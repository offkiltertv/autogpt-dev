// Offline DOM boundary only, using real ARMember-rendered synthetic snapshots.
// Does not claim native player, Termly, billing, or authenticated-browser coverage.
import assert from "node:assert/strict";
import fs from "node:fs";
import { createHash } from "node:crypto";
import { chromium } from "playwright";

const [lab, reviewedId, ...extra] = process.argv.slice(2);
if (
  extra.length ||
  !/^\/private\/tmp\/oktv-membership-integration\.[A-Za-z0-9]+$/.test(
    lab ?? "",
  ) ||
  fs.realpathSync(lab) !== lab ||
  fs.readFileSync(`${lab}/.oktv-membership-lab`, "utf8").trim() !==
    "OKTV_MEMBERSHIP_SYNTHETIC_ONLY"
)
  throw Error("Pass the private membership clone and reviewed release ID.");
process.umask(0o077);
const manifest = JSON.parse(
  fs.readFileSync(`${lab}/private/playback-release.json`, "utf8"),
);
assert.equal(manifest.release_id, reviewedId);
const suitePath = `${lab}/private/membership-results.json`;
const suiteBytes = fs.readFileSync(suitePath, "utf8");
const suite = JSON.parse(suiteBytes);
assert.equal(suite.status, "passed");
assert.match(suite.runId, /^[a-f0-9]{32}$/);
assert.equal(suite.release, reviewedId);
assert.equal(suite.mode, "PRIVATE_SYNTHETIC_ARMember_NOT_PRODUCTION");
const expected = [
  "public-anonymous",
  "direct-anonymous",
  "direct-active",
  "direct-no_plan",
  "direct-wrong_plan",
  "direct-suspended",
  "direct-editor",
  "direct-admin",
  "category-anonymous",
  "category-active",
  "tag-anonymous",
  "tag-active",
  "trailer-anonymous",
  "global-active",
];
assert.deepEqual(
  suite.captures.map((item) => item.name).sort(),
  expected.sort(),
);
const runtime = `${lab}/wordpress/wp-content/plugins/offkilter-arcana`;
const script = fs.readFileSync(
  `${runtime}/assets/js/ok-playback-help.js`,
  "utf8",
);
const digest = (value) => createHash("sha256").update(value).digest("hex");
assert.equal(digest(script), manifest.files["assets/js/ok-playback-help.js"]);
const result = {
  status: "running",
  mode: "OFFLINE_MEMBERSHIP_DOM_SNAPSHOTS_NOT_PLAYBACK_OR_DEPLOYMENT",
  at: new Date().toISOString(),
  release: reviewedId,
  sourceRunId: suite.runId,
  sourceReportHash: digest(suiteBytes),
  sourceHash: digest(script),
  cases: [],
};
const reportPath = `${lab}/private/membership-browser.json`;
const saveReport = () =>
  fs.writeFileSync(reportPath, JSON.stringify(result, null, 2) + "\n", {
    mode: 0o600,
  });
saveReport();
let browser;
try {
  browser = await chromium.launch({ channel: "chrome", headless: true });
  for (const width of [1440, 390]) {
    const context = await browser.newContext({
      viewport: { width, height: 900 },
    });
    await context.route("**/*", (route) => route.abort("blockedbyclient"));
    try {
      for (const item of suite.captures) {
        assert.equal(item.html, `membership-${item.name}.html`);
        const html = fs.readFileSync(`${lab}/private/${item.html}`, "utf8");
        assert.equal(digest(html), item.sha256);
        const page = await context.newPage();
        try {
          await page.clock.install();
          await page.setContent(
            "<!doctype html><html><head><meta http-equiv=\"Content-Security-Policy\" content=\"default-src 'none'; script-src 'unsafe-inline'; style-src 'unsafe-inline'\"><title>Offline membership DOM check</title></head><body></body></html>",
          );
          const before = await page.evaluate((source) => {
            const parsed = new DOMParser().parseFromString(source, "text/html");
            const inline = parsed.querySelector(
              "#oktv-playback-help-js-before",
            );
            const match = inline?.textContent.match(
              /window\.OKTVPlaybackHelpConfig\s*=\s*(\{[^]*\});/,
            );
            if (!match) throw Error("Missing actual PHP helper configuration.");
            window.OKTVPlaybackHelpConfig = JSON.parse(match[1]);
            const player = parsed.querySelector(".is-single-post-main-player");
            if (!player) throw Error("Missing native player/access container.");
            // Keep actual DOM structure but execute only our own helper, not vendor/embed scripts.
            for (const node of player.querySelectorAll("script,link,iframe")) {
              if (node.tagName !== "IFRAME") node.remove();
            }
            for (const element of [player, ...player.querySelectorAll("*")])
              for (const attribute of [...element.attributes])
                if (/^on/i.test(attribute.name))
                  element.removeAttribute(attribute.name);
            document.body.append(document.importNode(player, true));
            return {
              config: JSON.stringify(window.OKTVPlaybackHelpConfig),
              controls: document.querySelectorAll(".beeteam368-player-control")
                .length,
              frames: [...document.querySelectorAll("iframe")].map((node) =>
                node.getAttribute("src"),
              ),
              protected: document.querySelectorAll(".beeteam368-player-protect")
                .length,
            };
          }, html);
          assert.equal(
            before.controls === 1,
            item.allowed,
            `${item.name} native eligibility`,
          );
          await page.addScriptTag({ content: script });
          await page.clock.runFor(16000);
          const after = await page.evaluate(() => ({
            helpers: document.querySelectorAll(".oktv-playback-help").length,
            config: JSON.stringify(window.OKTVPlaybackHelpConfig),
            controls: document.querySelectorAll(".beeteam368-player-control")
              .length,
            frames: [...document.querySelectorAll("iframe")].map((node) =>
              node.getAttribute("src"),
            ),
            protected: document.querySelectorAll(".beeteam368-player-protect")
              .length,
          }));
          assert.equal(
            after.helpers,
            item.allowed ? 1 : 0,
            `${width}px ${item.name} helper mounting`,
          );
          assert.equal(after.controls, before.controls);
          assert.equal(after.config, before.config);
          assert.equal(after.protected, before.protected);
          assert.deepEqual(after.frames, before.frames);
          result.cases.push({
            name: item.name,
            width,
            allowed: item.allowed,
            pass: true,
          });
        } finally {
          await page.close();
        }
      }
    } finally {
      await context.close();
    }
  }
  assert.equal(
    digest(fs.readFileSync(suitePath, "utf8")),
    result.sourceReportHash,
    "PHP fixture run changed during browser validation",
  );
  result.status = "passed";
  saveReport();
  console.log(
    `${result.cases.length} offline membership DOM scenarios passed; not playback or deployment.`,
  );
} catch (error) {
  result.status = "failed";
  saveReport();
  throw error;
} finally {
  await browser?.close();
}
