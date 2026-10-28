// Browser response override only. No production write, CMP bypass, or deployment.
import fs from "node:fs";
import { createHash } from "node:crypto";
import http from "node:http";
import { chromium } from "playwright";

if (
  process.argv[2] !== "--browser-only-candidate" ||
  process.argv
    .slice(3)
    .some((value) => !["--mobile", "--keyboard"].includes(value))
)
  throw Error("Explicit browser-only mode required.");
const mobile = process.argv.includes("--mobile");
const keyboard = process.argv.includes("--keyboard");
if (mobile && keyboard) throw Error("Use a separate desktop keyboard run.");
process.umask(0o077);
const lab = fs
  .readFileSync("/private/tmp/oktv-selected-lab-current-path", "utf8")
  .trim();
if (
  !/^\/private\/tmp\/oktv-vidmov-integration\.[A-Za-z0-9]+$/.test(lab) ||
  fs.realpathSync(lab) !== lab ||
  fs.readFileSync(`${lab}/.oktv-lab`, "utf8").trim() !==
    "OKTV_DISPOSABLE_LOCAL_LAB"
)
  throw Error("Expected disposable lab.");
const fixture = await new Promise((resolve, reject) => {
  // Node fetch normalizes Host; use the fixed loopback HTTP client so the lab's
  // reserved-host guard stays intact. No configurable target or redirects.
  const request = http.get(
    {
      hostname: "127.0.0.1",
      port: 8793,
      path: "/video/local-test-companion/",
      headers: { Host: "www.offkilter.test" },
    },
    (response) => {
      let body = "";
      response.setEncoding("utf8");
      response.on("data", (chunk) => {
        body += chunk;
        if (body.length > 2_000_000)
          request.destroy(Error("Oversized fixture response."));
      });
      response.on("error", reject);
      response.on("end", () =>
        resolve({
          status: response.statusCode,
          headers: response.headers,
          body,
        }),
      );
    },
  );
  request.setTimeout(15000, () =>
    request.destroy(Error("Fixture request timed out.")),
  );
  request.on("error", reject);
});
if (
  fixture.status !== 200 ||
  !String(fixture.headers["x-robots-tag"] ?? "").includes("noindex")
)
  throw Error("Expected the running loopback fixture.");
const fixtureHtml = fixture.body;
const scripts = [
  ...fixtureHtml.matchAll(
    /<script[^>]*\bid=["']oktv-playback-help-js-before["'][^>]*>([^]*?)<\/script>/g,
  ),
];
if (scripts.length !== 1)
  throw Error("Expected one fixture helper configuration.");
const localized = scripts[0][1];
const match = localized.match(
  /^\s*window\.OKTVPlaybackHelpConfig\s*=\s*(\{[^]*\});\s*(?:\/\/# sourceURL=[^\n]+\s*)?$/,
);
if (!match) throw Error("Expected only localized playback JSON.");
const config = JSON.parse(match[1]);
if (config.video !== true || config.category !== "performance")
  throw Error("Unexpected fixture provider context.");
const origin = "https://www.offkilter.tv";
const video = `${origin}/video/why-i-want-youtube-to-be-my-main-platform-streaming-stream/`;
const base = "/wp-content/plugins/offkilter-arcana/assets";
const runtime = `${lab}/wordpress/wp-content/plugins/offkilter-arcana`;
const sources = {
  [`${base}/js/ok-playback-help.js`]: fs.readFileSync(
    `${runtime}/assets/js/ok-playback-help.js`,
    "utf8",
  ),
  [`${base}/css/ok-playback-help.css`]: fs.readFileSync(
    `${runtime}/assets/css/ok-playback-help.css`,
    "utf8",
  ),
};
const packageManifest = fs.existsSync(`${lab}/private/playback-release.json`)
  ? JSON.parse(fs.readFileSync(`${lab}/private/playback-release.json`, "utf8"))
  : null;
if (packageManifest) {
  for (const [urlPath, bytes] of Object.entries(sources)) {
    const relative = urlPath.slice(
      "/wp-content/plugins/offkilter-arcana/".length,
    );
    if (
      createHash("sha256").update(bytes).digest("hex") !==
      packageManifest.files[relative]
    ) {
      throw Error("Browser asset differs from the selected package.");
    }
  }
}
const output = fs.mkdtempSync("/private/tmp/oktv-browser-candidate.");
const result = {
  mode: "LOCAL_BROWSER_RESPONSE_OVERRIDE_NOT_DEPLOYED",
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
await page.route(`${origin}/**`, async (route) => {
  try {
    const request = route.request();
    const url = new URL(request.url());
    if (sources[url.pathname]) {
      await route.fulfill({
        status: 200,
        contentType: url.pathname.endsWith(".js")
          ? "application/javascript"
          : "text/css",
        headers: { "Cache-Control": "no-store" },
        body: sources[url.pathname],
      });
      return;
    }
    if (
      request.isNavigationRequest() &&
      request.frame() === page.mainFrame() &&
      url.href === video
    ) {
      const response = await route.fetch({ timeout: 30000 });
      const html = await response.text();
      if (response.status() !== 200 || html.includes("oktv-playback-help-js"))
        throw Error("Public baseline changed; inspect before overriding.");
      const helper =
        `<script id="oktv-playback-help-js-before">window.OKTVPlaybackHelpConfig = ${JSON.stringify(config).replace(/</g, "\\u003c")};</script>` +
        `<script id="oktv-playback-help-js" src="${origin}${base}/js/ok-playback-help.js?browser-only-candidate=1"></script>` +
        '<div style="position:fixed;bottom:0;left:0;z-index:2147483647;background:#fff;color:#000;padding:8px;font:14px sans-serif">LOCAL BROWSER-ONLY CANDIDATE. NOT DEPLOYED.</div>';
      await route.fulfill({
        response,
        body: html
          .replace(
            "</head>",
            `<link rel="stylesheet" href="${origin}${base}/css/ok-playback-help.css?browser-only-candidate=1"></head>`,
          )
          .replace("</body>", helper + "</body>"),
      });
      return;
    }
    await route.continue();
  } catch (error) {
    result.interceptionFailure = String(error).split("\n")[0];
    await route.abort("failed").catch(() => {});
  }
});
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
  check(true, "Candidate opens actual vendor preferences after denial");
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
  check(true, "Existing footer opens vendor preferences through candidate");
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
  await page.unrouteAll({ behavior: "wait" });
  await browser.close();
}
