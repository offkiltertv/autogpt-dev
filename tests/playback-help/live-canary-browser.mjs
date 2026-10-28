// Live canary verification only: no route interception, response replacement or consent bypass.
import fs from "node:fs";
import { createHash } from "node:crypto";
import { parseHTML } from "linkedom";
import { chromium } from "playwright";

const [mode, release, reviewedId, ...flags] = process.argv.slice(2);
if (mode !== "--live-canary" || reviewedId !== "oktv-playback-3.3.6-db31a5a937a7d1af"
    || !/^\/private\/tmp\/oktv-playback-release\.[a-f0-9]+$/.test(release ?? "")
    || fs.realpathSync(release) !== release
    || flags.some(flag => !["--mobile", "--keyboard"].includes(flag))) throw Error("Pass explicit live-canary mode, private package and reviewed ID.");
const mobile=flags.includes("--mobile"), keyboard=flags.includes("--keyboard");
if (mobile && keyboard) throw Error("Separate desktop keyboard and mobile runs.");
process.umask(0o077);
const packageManifest=JSON.parse(fs.readFileSync(release+"/release.json","utf8"));
if (packageManifest.release_id !== reviewedId) throw Error("Unexpected release.");
const origin="https://www.offkilter.tv";
const video=origin+"/video/why-i-want-youtube-to-be-my-main-platform-streaming-stream/";
const response=await fetch(video, {signal:AbortSignal.timeout(30000),redirect:"error"});
if (response.status !== 200 || response.headers.get("x-oktv-playback-release") !== reviewedId
    || !response.headers.get("cache-control")?.includes("no-store")) throw Error("Canary scope/header not active through Cloudflare.");
const {document}=parseHTML(await response.text());
const sources={};
for (const [id,attr,path] of [["oktv-playback-help-js","src","assets/js/ok-playback-help.js"],
    ["oktv-playback-help-css","href","assets/css/ok-playback-help.css"]]) {
  if(document.querySelectorAll("#"+id).length!==1) throw Error("Expected one live asset: "+id);
  const url=new URL(document.getElementById(id).getAttribute(attr),origin);
  if(url.origin!==origin || url.pathname!=="/wp-content/plugins/offkilter-arcana/"+path) throw Error("Unexpected asset origin/path.");
  const asset=await fetch(url,{signal:AbortSignal.timeout(30000),redirect:"error"});
  if(asset.status!==200)throw Error("Live asset response failed.");
  const bytes=Buffer.from(await asset.arrayBuffer());
  if(createHash("sha256").update(bytes).digest("hex")!==packageManifest.files[path])throw Error("Live asset hash differs: "+path);
  sources[url.pathname]=bytes;
}
const output = fs.mkdtempSync("/private/tmp/oktv-browser-live-canary.");
const result = {
  mode: "DEPLOYED_SINGLE_PUBLIC_VIDEO_CANARY_NO_RESPONSE_OVERRIDE",
  candidateReleaseId: packageManifest?.release_id ?? null,
  keyboardActivation: keyboard,
  at: new Date().toISOString(),
  browserProfile: mobile
    ? "Chrome mobile emulation 390x844, not a physical device"
    : "Chrome desktop 1440x1000",
  url: video,
  sourceHashes: Object.fromEntries(
    Object.entries(sources).map(([key, value]) => [
      key,
      createHash("sha256").update(value).digest("hex"),
    ]),
  ),
  assertions: [],
  errors: [],
};
const browser = await chromium.launch({ channel: "chrome", headless: true });
const context = await browser.newContext(
  mobile
    ? { viewport: { width: 390, height: 844 }, isMobile: true, hasTouch: true }
    : { viewport: { width: 1440, height: 1000 } },
);
const page = await context.newPage();
page.on("pageerror", (error) => result.errors.push(error.message));
function check(value, name) {
  result.assertions.push({ name, pass: Boolean(value) });
  if (!value) throw Error(name);
}
const state = () =>
  page.evaluate(() => ({
    consent: window.Termly?.getConsentState?.() ?? null,
    providerFrames: Array.from(
      document.querySelectorAll('iframe[src*="youtube.com/embed/"]'),
    ).length,
    helper: document.querySelector(".oktv-playback-help")?.innerText ?? null,
  }));
async function declineAndWait() {
  // This site's current vendor UI reloads after saving denial. Observe it rather
  // than reading the old document between modal dismissal and navigation.
  const navigation = page.waitForEvent("framenavigated", {
    predicate: (frame) => frame === page.mainFrame(),
    timeout: 20000,
  });
  await page.getByRole("button", { name: "Decline All", exact: true }).click();
  await navigation;
  await page.waitForLoadState("domcontentloaded");
  await page.waitForFunction(
    () => window.Termly?.getConsentState?.()?.performance === false,
    null,
    { timeout: 20000 },
  );
  await page.locator(".oktv-playback-help").waitFor();
}
try {
  check(
    (
      await page.goto(video, { waitUntil: "domcontentloaded", timeout: 60000 })
    ).status() === 200,
    "Fresh anonymous page returns 200",
  );
  await page
    .getByRole("button", { name: "Preferences", exact: true })
    .first()
    .click({ timeout: 25000 });
  await declineAndWait();
  await page.locator(".oktv-playback-help").waitFor({ timeout: 20000 });
  result.denied = await state();
  result.layout = await page
    .locator(".oktv-playback-help")
    .evaluate((element) => {
      const box = element.getBoundingClientRect();
      return {
        viewport: window.innerWidth,
        helperLeft: box.left,
        helperRight: box.right,
        documentWidth: document.documentElement.scrollWidth,
      };
    });
  check(
    result.denied.consent?.performance === false &&
      result.denied.providerFrames === 0,
    "Declining optional consent keeps provider blocked",
  );
  if (keyboard) {
    await page.locator(".oktv-playback-help > summary").focus();
    await page.keyboard.press("Tab");
    check(
      await page
        .getByRole("button", { name: "Privacy preferences", exact: true })
        .evaluate((element) => document.activeElement === element),
      "Tab reaches the helper preference action",
    );
    await page.keyboard.press("Enter");
  } else {
    await page
      .getByRole("button", { name: "Privacy preferences", exact: true })
      .click();
  }
  await page.getByRole("dialog").waitFor({ timeout: 15000 });
  check(true, "Deployed canary opens actual vendor preferences after denial");
  const boxes = page.getByRole("dialog").locator('label[role="checkbox"]');
  const names = [
    "Do not sell",
    "Essential Cookies",
    "Performance and Functionality",
    "Analytics and Customization",
    "Advertising Cookies",
    "Social networking",
    "Unclassified",
  ];
  check(
    (await boxes.count()) === names.length,
    "Known vendor categories present",
  );
  for (let i = 0; i < names.length; i++) {
    check(
      (
        await boxes
          .nth(i)
          .evaluate((element) => element.parentElement.innerText)
      ).includes(names[i]),
      `Category identity: ${names[i]}`,
    );
    if (
      i > 1 &&
      ((await boxes.nth(i).getAttribute("aria-checked")) === "true") !==
        (i === 2)
    )
      await boxes.nth(i).click();
  }
  await page
    .getByRole("button", { name: "Save and Finish", exact: true })
    .click();
  await page.getByRole("dialog").waitFor({ state: "hidden" });
  const media = page
    .frameLocator('iframe[src^="https://www.youtube.com/embed/"]')
    .locator("video");
  await media.first().waitFor({ state: "attached", timeout: 25000 });
  await page.waitForTimeout(5000);
  const sample = () =>
    media.evaluateAll((elements) =>
      elements.map((element) => ({
        paused: element.paused,
        time: element.currentTime,
        ready: element.readyState,
        error: element.error?.code ?? null,
      })),
    );
  result.selective = await state();
  check(
    result.selective.consent.performance === true &&
      ["analytics", "advertising", "social_networking", "unclassified"].every(
        (key) => result.selective.consent[key] === false,
      ),
    "Only requested functionality consent enabled",
  );
  result.before = await sample();
  await page.waitForTimeout(4000);
  result.after = await sample();
  check(
    result.before.some(
      (entry, i) =>
        result.after[i]?.time > entry.time + 1 &&
        !result.after[i].paused &&
        !result.after[i].error,
    ),
    "Real anonymous provider clock advances",
  );
  await page.screenshot({ path: `${output}/playing.png` });
  // The closed ready-state disclosure remains available to the visitor.
  if (
    !(await page
      .locator(".oktv-playback-help")
      .evaluate((element) => element.hasAttribute("open")))
  ) {
    await page.getByText("Playback help", { exact: true }).click();
  }
  await page
    .getByRole("button", { name: "Privacy preferences", exact: true })
    .click();
  await page.getByRole("dialog").waitFor({ timeout: 15000 });
  await declineAndWait();
  result.withdrawn = await state();
  check(
    result.withdrawn.consent.performance === false,
    "Withdrawal is made by the vendor UI",
  );
  await page.getByRole("button", { name: "Reload page", exact: true }).click();
  await page.waitForLoadState("domcontentloaded");
  await page.locator(".oktv-playback-help").waitFor();
  await page.waitForTimeout(3000);
  result.reloaded = await state();
  check(
    result.reloaded.consent.performance === false &&
      result.reloaded.providerFrames === 0,
    "Explicit reload honors withdrawn consent",
  );
  const footer = page.getByRole("link", {
    name: "Consent Preferences",
    exact: true,
  });
  if (keyboard) {
    await footer.focus();
    await page.keyboard.press("Enter");
  } else {
    await footer.click();
  }
  await page.getByRole("dialog").waitFor({ timeout: 15000 });
  check(true, "Existing footer opens vendor preferences on deployed canary");
  await page.screenshot({ path: `${output}/preferences.png` });
} catch (error) {
  result.failure = String(error).split("\n")[0];
  result.failureState = await state().catch(() => ({
    unavailable: "Navigation in progress",
  }));
  await page.screenshot({ path: `${output}/failure.png` }).catch(() => {});
  process.exitCode = 1;
} finally {
  fs.writeFileSync(`${output}/result.json`, JSON.stringify(result, null, 2));
  console.log(JSON.stringify({ output, ...result }, null, 2));
  await browser.close();
}
