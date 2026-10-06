# Habaq member learning pilot (0.3.0)

New shortcode: `[habaq_learning]`. Arabic, RTL, text-first, works without JavaScript. Five shared modules plus two per Media, People or Production path. Checks are graded on the server. First assignments require a written admin review; learning completion never authorizes publication or employment. Source policies remain labeled as drafts.

## Source basis

The bundled catalog is an educational adaptation of current LIFE strategy notes (2026-10-06), draft ten principles, privacy and sensitive imagery policies, volunteering policy, the journalism training quality guide, and the historical People × Óros proposal. Canva strategy presentation was read as a secondary working document. The introductory Slides presentation is linked as a resource; its full contents were not used to derive claims. Targets conflict across strategy drafts, so numeric targets were intentionally excluded from mandatory learning.

Discovery used ChatGPT personal-context retrieval, Library search, Drive, Gmail and Canva. This does not establish exhaustive access to all ChatGPT projects. Gmail search results included private correspondence; private disputes and personal/financial details were excluded. No Figma file key was identified, and no design file was invented. Hostinger management tools were configured but absent from this session's callable tools. Mail API operations were discovered; no messages were sent or republished. The public-site fetch timed out. Production state is unverified.

## Deployment

1. Back up the current plugin directory and database, note installed plugin version and active theme. Confirm it matches this repository before replacing it.
2. Install/update the packaged `habaq-wp-core` plugin on staging. Preserve `wp-content/uploads/habaq-training/` and the existing training registry. No activation migration or existing page replacement occurs.
3. Open Trainings > مسارات التعلم and create `/learning`. The setup button does not overwrite an existing page. If a different page occupies that slug, put the shortcode on a reviewed page manually.
4. Assign `habaq_insider` (or an existing Habaq role with insider capability) to verified members. Ordinary WordPress subscribers have no access. Only users with `manage_options` can review submissions in this pilot.
5. Nominate the actual reviewer for each unit and a safe reporting contact with an alternative if the named person is involved in a complaint. The source documents contain blanks. Confirm policy adoption separately; no invented reporting address is included.
6. Exclude the page from LiteSpeed/Hostinger/CDN caches and verify that authenticated member HTML is never shared. The plugin sends no-cache headers and `DONOTCACHEPAGE`, which an upstream cache may not honor.
7. Test anonymous and ordinary subscriber denial, member paths, correct and wrong answers, stale versions, prerequisite gates, practical submission/revision/approval, narrow-screen RTL layout, and WordPress privacy export/erasure.
8. Verify all existing jobs shortcodes, one registered multimedia training, progress saving and current-version completion. Progress AJAX now rejects unknown/unregistered training slugs and stale versions. For legacy audio-only shortcode trainings, register canonical metadata first. Explicit shortcode version/access overrides should match registry/JSON metadata for tracking.
9. Link the verified page in the site navigation, run a small member pilot, and verify live page content after publication.

Rollback: restore the previous plugin files; learner metadata remains in the database and can be exported. Do not delete training uploads or learner records to roll back code.

## Data and limitations

Member metadata stores chosen track, correct knowledge-check response, lesson confirmation time, deidentified assignment summary, review decision and feedback. Previous lesson versions are archived when a new version is submitted. WordPress personal-data exporters/erasers include current and historical learning records. A learner can request correction/export/deletion through site administration. The module IDs are stable; removing modules requires an explicit retention review.

The pilot has one formative check per lesson and manual administrator review. No scheduled mail, certificates, SSO, external learning analytics, fully editable curriculum UI, private file uploads or automatic role elevation are included. These are follow-up choices after observing the pilot. Source permissions remain unchanged. The old training player's media URLs under public uploads require separate hosting protections if the media itself is confidential; shortcode gating alone does not protect a known direct file URL.

## Validation

PHP 8.3 lint passed for every plugin PHP file. Node syntax check passed for the training player. 71 isolated PHP behavioral assertions passed, covering catalog references and grading, anonymous/subscriber/member access, prerequisites, stale versions, acknowledgement, pending/revision/approval, user isolation, history, redirect validation, CSRF, privacy export/erasure and existing player progress authorization. These use WordPress function stubs, not a real WordPress installation. Live staging, theme, cache and deployment verification remain pending.

An existing unowned JavaScript fragment with an unmatched closing token prevented the repository training player from parsing; it was removed. Embedded player JSON is now escaped for HTML script contexts. Completion is shown as saved only after a successful server response for signed-in users.
