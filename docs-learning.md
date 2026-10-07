# Habaq member learning and onboarding (0.9.0)

Operating model: [docs/learning-operating-model.md](docs/learning-operating-model.md). Expanded curriculum, roles and maintenance: [docs/learning-curriculum.md](docs/learning-curriculum.md).

Shortcode: `[habaq_learning]`. Arabic, RTL, text-first, works without JavaScript. Five shared modules plus two in one of ten unit/function paths. A separate library offers 36 optional specialist courses and 28 proposed role profiles. Checks are graded on the server. First assignments require a written admin review; learning completion never authorizes publication or employment. Source policies remain labeled as drafts.

## Source basis

The bundled catalog is an educational adaptation of current LIFE strategy notes (2026-10-06), draft ten principles, privacy and sensitive imagery policies, volunteering policy, the journalism training quality guide, and the historical People × Óros proposal. Canva strategy presentation was read as a secondary working document. The introductory Slides presentation is linked as a resource; its full contents were not used to derive claims. Targets conflict across strategy drafts, so numeric targets were intentionally excluded from mandatory learning.

Discovery used ChatGPT personal-context retrieval, Library search, Drive, Gmail and Canva. This does not establish exhaustive access to all ChatGPT projects. Gmail search results included private correspondence; private disputes and personal/financial details were excluded. No Figma file key was identified, and no design file was invented. Hostinger management tools were configured but absent from this session's callable tools. Mail API operations were discovered; no messages were sent or republished. The first public-site fetch timed out. The browser deployment subsequently verified the 0.3.0 live page and navigation. The 0.4.0 upgrade extends that page without replacing it.

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

PHP 8.3 lint passed for every plugin PHP file. Node syntax check passed for the training player. 500 isolated PHP behavioral assertions passed, covering catalog references and grading, anonymous/subscriber/member access, prerequisites, stale versions, acknowledgement, pending/revision/approval, user isolation, history, redirect validation, CSRF, privacy export/erasure, existing player progress authorization, optional specialist checks, role/search filters and all seven new functional practical-review flows. These use WordPress function stubs, not a real WordPress installation. Release-specific live smoke results are recorded in the PR; independent member identity and full review workflow remain a pilot acceptance step.

An existing unowned JavaScript fragment with an unmatched closing token prevented the repository training player from parsing; it was removed. Embedded player JSON is now escaped for HTML script contexts. Completion is shown as saved only after a successful server response for signed-in users.

## 0.4.0 operations and compatibility

Four flexible milestones, administrator-created per-member onboarding plans, optional printable job cards for all lessons, readable reference lessons regardless of completion order, one current learning-support request per member, and a reviewed application reflection. Contacts are configured through the learning admin screen and remain unset until the organization supplies approved contacts. Next incomplete lesson opens automatically. Existing lesson IDs and version `2026-10-07.1` are retained because required content, outcomes tested and answer keys have not changed. Optional learning aids and operational tools do not reset existing completion.

Only existing `manage_options` administrators can record plans and reviews. Naming a learning companion grants no permissions. Do not make supervisors administrators merely to review a task; the coordinator records their review in this first version. No mail or background notifications are sent. The coordinator checks the queue twice weekly and uses the team's approved contact for urgent issues.

Task approval requires four checked criteria, written feedback, a current content version and a matching submission revision; self-approval is blocked. Pending tasks cannot be silently replaced before review. Plans and support/reflection responses also check stale form revisions. These are stale-form guards, not atomic multi-writer transactions; the expected scale is one coordinator and small cohorts.

New metadata keys are `habaq_learning_plan`, `habaq_learning_support`, `habaq_learning_reflection` and `habaq_learning_reflection_history`. All participate in native privacy export/erase. A unit change archives the previous application reflection and retains shared/other lesson records. No database tables or schema migrations. Last support request replaces the prior request, as stated in the UI. The course is not a complaint or HR case-management system.

Before the 0.4.0 release preserve `rollback/learning-0.3.0` at `21e24ecdafdb65d7b455a1351639845d1310a48d`; use Deployer for Git to select that branch and deploy if rollback is needed. Records remain intact and can be exported. A code rollback branch is not a database backup. Obtain hosting backups before inviting a larger cohort.

## Visual release 0.6.0

The portal now has a full-width photo opening, compact headings, 16px Arabic body text, plain fusha guidance and direct actions to the current lesson/library filters. A scoped body class removes the duplicate visible theme title and opening whitespace only on the learning page; the H1 remains available to assistive technology. Lesson views use a shorter photo header. Existing lesson IDs, bodies, quizzes, versions and learner records are preserved.

The default photograph is existing Habaq media attachment 2564 (`ADN4981-scaled.jpg`). `habaq_learning_photo_id` can override the attachment ID. Native WordPress responsive images serve the appropriate size. A missing attachment leaves the dark hero usable. The source is the existing media collection; do not infer photographer, location or copyright metadata from the filename. Newly selected photographs need appropriate rights, privacy and crop review.

A native editable Cover pattern, **حبق: صورة واسعة ونص وأزرار**, is in the Habaq pattern category. It uses standard Cover, Heading, Paragraph and Buttons blocks. Change the image, short copy and button URL when inserting it in another page. Pattern CSS loads on content containing its class and in the block editor. This provides the photographic style for other page work without bulk altering unrelated pages.

See `docs/learning-design-and-review.md` for the 61 lesson review proposals, Arabic voice examples, experience backlog and manageable editorial workflow. These proposals are not yet published course rewrites or adopted policies. Prioritize five common lessons, the actually used path and one or two specialist courses needed for the first task.

Code rollback: `rollback/learning-0.5.0` at `cce01cd4b3dabece1e536ec57d1f8e3c7eaf73ee`. This does not replace a database backup.


## Reading/accessibility polish — 0.6.2

The portal includes a “قراءة دون صور” switch. `reading=text` prevents generation of the hero image markup and stays active through learning links, filters and form redirects. It is a URL preference only; no cookie or learner-record update. Default mode retains Habaq’s photography. This does not provide offline saving or disable unrelated theme resources. Existing print/PDF guidance remains available.

Lesson links land on focusable, named reading panels; progress has an accessible name. Answer/acknowledgement labels, lesson links and disclosure headings have 44px minimum target heights, and long article content wraps within the RTL grid. All course IDs, versions, quiz keys and stored completions are unchanged. Follow `docs/learning-implementation-progress.md` for batch status and remaining acceptance.

## Shared lessons: welcome and roles — 0.7.0

The first two common lessons now use warm, direct fusha and short sections. Each contains an ordinary fictional situation, a completed example, a small exercise and a printable work card. Welcome explains Habaq through a local need, a small contribution and a new voice or audience. Roles distinguishes preparing, reviewing and authorizing work, with clear support, safe refusal and handover steps.

Server-rendered formative feedback appears after an unsuccessful check and alongside a saved completion. It explains the decision without emitting raw answer keys. Both lesson IDs, answer keys and module versions remain unchanged because their tested learning results did not change. Existing completions therefore remain valid. The plugin release changes to 0.7.0 for cache-busting and release tracking; policy and domain-owner approval remain separate.

## Shared lessons: conduct and data — 0.8.0

The next two common lessons use the same short, practical structure. Conduct separates consent to attend, record and publish; gives a fictional local event case; and asks the learner to identify the exact permission, protection step, designated reporting role and independent alternative. Data works through three fictional files, data minimization, limited sharing, safe training copies, unapproved AI-tool exclusions and a brief incident response.

Neither lesson collects real reports or files. Actual reporting, safety and privacy contacts and approved storage locations remain administrator setup decisions. The source policies are drafts, and completing a lesson does not adopt them or grant access. Lesson IDs, answer keys, prerequisites and module versions remain unchanged because the tested decisions did not change; existing completions remain valid. Release 0.8.0 tracks the editorial batch.

## Shared lesson: workflow — 0.9.0

The final common lesson is now titled “من فكرة صغيرة إلى تسليم واضح”. It turns a broad idea into one reviewable output with a definition of done, one reviewer, a deadline, authority limits, a midpoint question and a clear handover. A fictional Suwayda reading-circle example is preparation-only: it permits no booking, public announcement or spending. The exercise uses fictional information, and the work card can be completed on paper or in a light text file when connectivity is weak.

The lesson now gives attempt-specific feedback and distinguishes recorded learning, human review of work, publication or spending authority, reach and evidence of impact. The tested outcome, answer key, lesson ID, prerequisites and module version remain unchanged, so prior completions remain current. Release 0.9.0 tracks this editorial batch; domain-owner review and learner testing are not claimed.
