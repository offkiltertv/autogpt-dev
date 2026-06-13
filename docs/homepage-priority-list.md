# Homepage Priority List

Date: 2026-06-13

## Objective
Rank the top 5 homepage changes by visual impact, implementation effort, and user discovery gain.

## Top 5 Changes

### 1) Add `🔮 The Arcana` Rail Above Global Feed
- Visual impact: Very High
- Effort: Low
- Discovery improvement: Very High
- Why: Instantly turns Arcana from hidden archive into homepage destination.

### 2) Add Main Menu Arcana Tree
- Visual impact: High
- Effort: Low
- Discovery improvement: Very High
- Why: One-click discoverability from any page.

### 3) Remove/Replace Legacy `34.105.65.179` Menu Links
- Visual impact: Medium
- Effort: Low
- Discovery improvement: High
- Why: Eliminates dead/legacy navigation and reduces trust friction.

### 4) Keep Featured Creators Directly Under Arcana Rail
- Visual impact: Medium-High
- Effort: Very Low
- Discovery improvement: High
- Why: Connects Arcana viewers to broader creator ecosystem immediately.

### 5) Add Side Menu Arcana Quick Links
- Visual impact: Medium
- Effort: Low
- Discovery improvement: Medium-High
- Why: Improves deep-page and repeat-user navigation without layout risk.

## Fastest Execution Order
1. Main menu Arcana tree + legacy menu cleanup.
2. Homepage Arcana rail insertion (clone `d826690`, filter Arcana).
3. Featured Creators placement verification below Arcana rail.
4. Side menu Arcana quick links.
5. Final desktop/mobile QA pass.

## No-Code Admin Actions With Biggest Gains
If only WordPress admin changes are allowed, do these in order:
1. Add Arcana in main menu with children.
2. Insert Arcana homepage rail above existing global feed.
3. Remove old IP links from navigation.
4. Keep mixed Featured Creators adjacent to Arcana.
5. Add side-menu Arcana shortcuts.

## Go/No-Go Criteria
Go when:
- Arcana appears above the fold.
- Arcana is reachable from main menu in one click.
- Creator click-through works from Arcana cards.
- No primary nav links reference `34.105.65.179`.
