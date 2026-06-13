# Creator Integration Plan

Date: 2026-06-13

## Objective
Ensure Arcana creators are part of the same creator ecosystem as Gaming, Commentary, Music, and other OffKilter creators.

## Verified Current State
- Creator/channel pages are already active on site (channel-id + @handle patterns).
- Creator list/subscription surfaces already exist (`Most Subscriptions`, member list, subscribe actions).
- Arcana imports currently attach creator-related category terms (example: `@lipps`) and display as standard VidMov creator content.

## Integration Principle
Do not create Arcana-only creator logic.
Use existing VidMov creator architecture and ensure Arcana creators are discoverable in existing creator surfaces.

## Required Integration Actions

### 1) Creator Attribution Consistency
For Arcana campaigns, keep the same creator assignment pattern as the Golden Campaign:
- target post type: `vidmov_video`
- creator/category terms preserved on import
- no special Arcana-only author override

### 2) Featured Creators Blending
In homepage creator rail/widget settings:
- include mixed creators (do not filter out Arcana creators)
- sort by existing engagement/subscription metric
- keep Arcana and non-Arcana creators in one ranked list

### 3) Arcana-to-Creator Cross-Linking
From Arcana discovery surfaces:
- keep creator avatar/name visible on every Arcana card
- ensure clicking creator name/avatar routes to creator/channel archive

### 4) Creator Discovery Balance
Use one ecosystem model:
- Arcana creators appear in:
  - creator archives
  - member list (where applicable)
  - subscription surfaces
- Existing creators continue to appear in Arcana-adjacent homepage areas (Featured Creators rail)

## Suggested Homepage Placement
- Keep `Featured Creators` section directly below `🔮 The Arcana`.
- Do not split into separate “Arcana Creators” and “Other Creators” sections at alpha stage.

## Validation Checklist
- Arcana video cards show creator avatar/name.
- Creator click-through opens existing creator/channel page.
- Arcana creators appear in creator ranking surfaces when they have content/activity.
- Subscriptions function identically across Arcana and non-Arcana creators.

## Risk Notes
- Low risk if only widget/query configuration changes are used.
- Medium risk only if manual menu links point to legacy/invalid creator URLs.
