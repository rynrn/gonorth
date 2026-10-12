# Issue #87 — category card and social image completion

Date: 2026-10-06
Issue: https://github.com/rynrn/gonorth/issues/87
Parent: https://github.com/rynrn/gonorth/issues/74

Risk level: L2. Canonical writer: WordPress media library for attachments; GoNorth child theme for card selection and archive OG. This is a scoped live change, with file snapshots and guarded rollback. No WordPress core or monthly backup changed.

## Source and license
- Restaurant photo: [Restaurant in Tiberias, Rakoon](https://commons.wikimedia.org/wiki/File:20171212-140959-restaurant-tiberias-israel.jpg), CC0 1.0. Source/license checked 2026-10-06. Thumbnail was resized to 1280×720; attachment #525. A real restaurant interior, shown as an illustrative category photograph rather than an endorsement of a named business.
- Tour photo: [Rafting on the Jordan River](https://commons.wikimedia.org/wiki/File:RAFTING_ON_THE_JORDAN_RIVER.jpg), photograph by Itamar Grinberg via Israel Tourism, [CC BY-SA 2.0](https://creativecommons.org/licenses/by-sa/2.0/). Source/license checked 2026-10-06. Thumbnail was resized to 1280×853; card and WordPress derivatives may crop. Visible credit with author, source, license and alteration note appears on the home category grid and tours archive. Attachment #526.
- Neither image was taken from Google Places, which does not establish republishing rights by itself. Both depict relevant northern Israel scenes. Attachment title, ALT and description record sources and rights.

## Change
The home cards for restaurants and tours now use #525 and #526 instead of lodging photographs #53 and #51. The category archive OG/Twitter image profile uses the new WordPress attachments and their real dimensions. Other category images retain their mapping. A small RTL credit paragraph was added. Stylesheet version increased from 2.9.11 to 2.9.12.

## Evidence
- Prechange files: `memory/work/issue87-category/front-page-before.php`, `functions-before.php`, `gonorth-before.css`; SHA-256 recorded in deploy script and copied privately to `/home/claude/.local/share/gonorth-issue87-category/`.
- Candidate files: `memory/work/issue87-category/*-candidate.*`. Diff inspected and limited to two card IDs, archive image mapping, credit and CSS.
- Executable guarded rollback: `memory/work/issue87-category/rollback.sh` and server copy in the same private directory. It checks all three live candidate hashes before restoring exact prechange files. `sh -n` passed; rollback was not run.
- Staged and live PHP lint passed. Deployment guards confirmed all three prechange hashes before copy; WP object cache flushed.
- Chrome on 390px and 1280px: home + restaurant/tours/coffee archives HTTP 200, correct canonical, OG image, OG dimensions and large X card, no horizontal overflow. Both new card photos loaded after mobile lazy-load scroll; ALT matched photographs. The coffee category remained correct. Card grid screenshots and QA scripts are in `memory/work/issue87-category/`.
- New full images and #431/#432/about/open-saturday all returned HTTP 200. Prior reports cover the two guides and the latter two pages.
- Social-platform crawler cache was not controlled; the public OG/X tags and image URLs were verified directly, rather than claiming a third-party preview cache refresh.

Related #87 reports: `memory/reports/2026-10-06-issue-87-agamon-licensed-photo.md`, `2026-10-06-issue-87-villas-licensed-photo.md`, and `2026-10-06-issue-87-about-saturday-social.md`.

```yaml
status: complete
scope: "Home category cards and category archive social images for restaurants and tours"
risk_level: L2
canonical_writer: "WordPress media CLI; GoNorth child theme"
findings:
  - "Restaurant and tours cards previously used lodging photos"
sources:
  - {url: "https://commons.wikimedia.org/wiki/File:20171212-140959-restaurant-tiberias-israel.jpg", checked_at: "2026-10-06"}
  - {url: "https://commons.wikimedia.org/wiki/File:RAFTING_ON_THE_JORDAN_RIVER.jpg", checked_at: "2026-10-06"}
changes_made:
  - "Uploaded licensed attachments #525 and #526"
  - "Updated card IDs, category social images, visible tour credit, stylesheet 2.9.12"
unchanged:
  - {item: "Other category cards and OG mappings", reason: "Outside the scoped mismatch"}
validation:
  - {check: "PHP lint, checksum deployment and rollback syntax", result: pass, evidence: "Server command output on 2026-10-06"}
  - {check: "Chrome public 390px and 1280px", result: pass, evidence: "memory/work/issue87-category/qa.js and qa-cards.js"}
  - {check: "Public image and affected page HEAD", result: pass, evidence: "6/6 HTTP 200"}
snapshot: "memory/work/issue87-category/*-before.*"
rollback: "sh /home/claude/.local/share/gonorth-issue87-category/rollback.sh, after confirming current candidate hashes"
blockers: []
remaining_risks:
  - "Social network preview caches may take time to refresh; they were not purged"
issue_recommendation: close
```
