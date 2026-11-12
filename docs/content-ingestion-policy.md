# OFFKILTER content ingestion policy

Updated November 12, 2026 for the approved catalog and creator studio plan. This
replaces the June launch-sprint rule that required placeholder member accounts
for imported channels. Existing accounts and editorial holds are preserved.

## Curated sources and actual creators

A YouTube source is credited by its immutable provider channel ID and plain-text
channel name. A source collection on OKTV is separate from an OKTV member profile.
Importing a video never establishes a creator claim, verification badge, follower
count, platform membership or permission to broadcast on that channel.

Actual creators use their own authenticated OKTV accounts. Account linking and
creator claiming require ownership proof. The selected first studio pilot is
**itsjustrobby**, with SidebarChat as the continuing creator conversation.

## Approved playlist ingestion

The owner-approved Outcome and Prem playlist campaigns, 8089 and 8090, are the
current catalog scope. Source playlist identifiers and provider credentials stay
on the server, outside this public repository. Structural categories describe
subjects; they do not assert that all videos in a playlist share one creator.
Every published curated video receives the source channel returned for that
specific video.

Native wp-automatic campaigns check for new items every 15 minutes and stage
imports as drafts. Automatic member creation and provider comment copying are
disabled for those campaigns. The verified publishing operator uses WordPress's
standard APIs; it does not run vendor processing templates. Arcana continues as
an enhancement and discovery layer, not a second import engine.

The hourly verified job inventories both playlists, checks current availability
and embedding permission, and publishes at most 20 eligible items per source in
a run. It refreshes each video's provider metadata before publication, requires
valid source credit and a thumbnail, checks duplicates across the library, and
starts new local engagement counters at zero. Only the thumbnail is copied;
video playback remains the original YouTube embed.

## Holds and incomplete provider inventories

Older editorial drafts, revised drafts, pending posts and unrelated campaign
content are not adopted. The three available Prem drafts held since July remain
on hold until the owner resolves that existing editorial decision. A title
becoming unavailable at the provider does not justify deleting its OKTV page
or discussion.

Coverage counts distinguish published, draft, missing, unavailable,
embedding-restricted and unknown states. A provider pagination gap stays
unresolved. The explicit observation mode can retain a small terminal count
discrepancy while importing identifiable eligible items; it never invents a
video identifier or labels that inventory complete.

## Source presentation and discovery

Verified imports display “YouTube source · curated on OKTV”, a local source
collection and the original YouTube channel. They do not inherit a member's
verification, subscriptions or following controls. Ordinary member posts retain
their existing presentation. Source verification can be reconciled for existing
imports from the approved campaigns without reassigning authors, changing local
engagement, creating accounts or claiming creators.

Deleted/private placeholder titles and provider-confirmed unavailable or
embedding-restricted videos are excluded from discovery. Existing direct pages,
slugs and conversations remain available. Homepage language describes curation
accurately. Featuring a source or member remains a deliberate editorial choice;
backlog completion does not automatically feature every imported video.

## Existing restrictions retained

The deprecated campaigns stay retired: @Lipps / Casey campaign 4206 and term
2055, @LC campaign 4708, and @DaniElleLuminati campaign 4168. Do not apply term
2055 as a catch-all creator label or re-enable those sources through this job.
Test campaigns remain isolated from public discovery. Blank or unidentifiable
source rows are not importable.

New source approval requires owner/editorial authorization, an identifiable
provider source, an initial draft canary and a source-credit check. A new member
profile requires the creator's actual account and ownership evidence; importing
a source is not identity provisioning.

## Operations and rollback

**Tools → OKTV Catalog Health** reports aggregate coverage, unresolved provider
items, held drafts and verified import times. Counts older than four hours are
flagged as stale. A failed source request never overwrites a successful inventory
with zero coverage.

The hourly job stops on failed site health, ongoing recovery, low disk reserve,
changed release hashes, provider failures, ambiguous records or overlapping
execution. It keeps root-private before-state and publication receipts, with
verified compression for new large receipts and a bounded archive reserve.
No raw inventory, credentials or production records belong in Git.

Disable the dedicated timer to pause verified publication; native campaigns
continue staging drafts. Presentation markers provide a reversible display
switch. Publication rollback must identify the exact records from a private
receipt and return those records to draft. Restore metadata only from its named
before-state receipt after checking for later editorial changes. Preserve the
existing uptime protections throughout catalog or studio work.
