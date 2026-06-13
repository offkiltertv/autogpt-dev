# YouTube Source Catalog (Approved OffKilter Sources)

Date: 2026-06-13
Source: authenticated YouTube session (`youtube.com/feed/playlists`), read-only verification.

## Arcana Source Policy
- Arcana is a focused content silo.
- Approved Arcana Sources: `Prem`, `Outcome2026`.
- Playlist inclusion is the editorial approval gate.
- No additional per-item approval required for these curated playlists.
- No forum requirement for Arcana ingestion/publishing.

## Verified Sources

| Source Group | Playlist Name | Playlist URL | Playlist ID | Video Count | Visibility | Status |
|---|---|---|---|---:|---|---|
| Prem/Outcome | Prem | `https://www.youtube.com/playlist?list=PLmQAJYyQkZlA5wYrdJWdjPMnznXJ8tFOE` | `PLmQAJYyQkZlA5wYrdJWdjPMnznXJ8tFOE` | 1,168 | Private | Verified |
| Prem/Outcome | Outcome2026 | `https://www.youtube.com/playlist?list=PLmQAJYyQkZlBEO7AKf4ihEZ_SZfoUHPh4` | `PLmQAJYyQkZlBEO7AKf4ihEZ_SZfoUHPh4` | 179 | Private | Verified |
| Arcana (requested) | Poem | Not found in authenticated account | Not found | Not found | Not found | Blocked |

## Name/Existence Checks

- `Poem` exact name: not found (`Find in page` result `0/0` on playlist index).
- `Prem and Outcome 2026` exact name: not found (`Find in page` result `0/0`).
- Operational reality: source is currently split across two playlists: `Prem` and `Outcome2026`.

## Non-Arcana Reservoir Rule
- `Liked videos` is not automatically Arcana content.
- `Liked videos` should be treated as a broader reservoir for non-Arcana silos (Gaming, Commentary, Reality TV, Music, Technology, Investigations) after classification/routing.

## Import Order Recommendation

Import first: `Outcome2026`

Reasoning:
1. Content quality control: smaller set (179) is easier to review and tune category output quickly.
2. Risk: lower chance of flooding VidMov with misconfigured imports than starting with 1,168-item `Prem`.
3. Ease of validation: run `draft` first, verify one item, then publish cadence with clear rollback scope.
4. Volume management: `Prem` can be phased in after validation using the same proven campaign template.

## Suggested Rollout Sequence

1. Create `Outcome2026 Playlist -> VidMov` campaign first (`draft`, 1 item/run).
2. Validate post type/category/publish behavior.
3. Switch Outcome2026 to desired drip rate.
4. Clone config for `Prem Playlist -> VidMov` and start `draft` validation.
5. Keep `Poem` blocked until playlist URL/ID exists in account.
