# Habaq member learning and onboarding (0.24.0)

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

PHP 8.3 lint passed for every plugin PHP file. Node syntax check passed for the training player. The isolated PHP behavioral checks cover catalog references and grading, anonymous/subscriber/member access, prerequisites, stale versions, acknowledgement, pending/revision/approval, user isolation, history, redirect validation, CSRF, privacy export/erasure, existing player progress authorization, optional specialist checks, role/search filters, all seven new functional practical-review flows and the reviewed shared and ten path lesson batches. These use WordPress function stubs, not a real WordPress installation. The current assertion count is recorded after each validated release in `docs/learning-implementation-progress.md`. Release-specific live smoke results are recorded in the PR; independent member identity and full review workflow remain a pilot acceptance step.

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

## Habaq Media path — 0.11.0

The media introduction is now titled “بداية العمل الصحفي”. It starts with one answerable question rather than a conclusion, uses a fictional Suwayda neighbourhood-library example, compares a weak pitch with a clear one and asks the learner to connect each claim to a possible source and independent verification. It explicitly prohibits contacting a real source during the exercise and explains that stopping or not publishing can be the professional decision.

The first assignment is now titled “مهمتك الأولى: مقترح قصة”. It remains a one-page story proposal with a question, audience, public interest, small verification matrix, source/voice plan, risks, protections, timing and reviewer. A completed fictional example, low-connectivity option, deidentified submission prompt and four-question reviewer guide make the handoff practical. The learning form accepts no names, contact details, testimony, recordings or private links.

The journalism training-quality guide, strategic note, draft editorial policy, working language/style guide, Safe Voices proposal and draft sensitive-imagery policy were re-read on 7 October 2026. Their adoption states remain visible. Lesson completion and practical review do not authorize source contact, recording or publication. Both lesson IDs, practical assignment, prerequisites, answer keys and module version `2026-10-07.1` remain unchanged because the tested requirements did not change. Existing completions and review history therefore remain current. Release 0.11.0 tracks the editorial batch; editorial/safety-owner and learner acceptance remain pending.

## Habaq People path — 0.12.0

The People introduction is now titled “بداية العمل في حبق ناس”. A fictional Suwayda rhythm session shows how to start from one cultural purpose and an observable participant action, welcome a first-time visitor, provide participation and rest options, reduce one access barrier and keep attendance separate from photography. A weak-versus-clear comparison makes booking, spending, partnership and announcement limits explicit. Hub Sweida remains an independent space and is not presumed to host or approve the session.

The first assignment is now “مهمتك الأولى: خطة جلسة”. It remains a one-page plan for a fictional 45–60 minute session, not a real event. A completed 50-minute example covers the objective, audience, sequence, newcomer entry, roles, resources, barriers, non-photographed participation, low-electricity alternative, stopping conditions, one useful feedback question and decisions that require authorization. The learner submits only a deidentified summary and can work on paper or in a light text file.

Reviewer guidance checks purpose, inclusion, honest resources/authority and separation of attendance from consent and safety. One feedback question or an attendance count may improve the next session but does not prove long-term social impact. The strategic note, historical People × Óros proposal, integrated LIFE programme proposal, draft event-services policy, draft principles and draft sensitive-imagery policy were re-read on 7 October 2026. Both lesson IDs, practical assignment, prerequisites, answer keys and module version `2026-10-07.1` remain unchanged because the tested requirements did not change. Existing completions and review history remain current. Release 0.12.0 tracks this editorial batch; programme/safety-owner and learner acceptance remain pending.

## Operations and coordination path — 0.10.0

The operations introduction is now titled “بداية العمل في التنسيق”. It explains the difference between a task owner, reviewer, decision owner and coordinator, using a fictional Suwayda work week and a weak-versus-clear task comparison. The role card records one output, definition of done, deadline, blocker, support, decision boundary and handover without assigning real staff or granting authority.

The first assignment is now titled “مهمتك الأولى: التنسيق”. It remains one fictional three-task weekly board with one decision, one blocker and a short handover. A completed example marks unconfirmed resources honestly, prohibits booking, publishing, spending and access changes, and asks the learner for a deidentified summary only. Reviewer guidance uses four practical questions and asks for one actionable revision plus available support.

Both lesson IDs, assignments, prerequisites, answer keys and module version `2026-10-07.2` remain unchanged because the tested requirements did not change. Existing completions and review history therefore remain current. Release 0.10.0 tracks the editorial batch; domain-owner and learner acceptance are still pending.

## Habaq Production path — 0.13.0

`production-basics` is now “بداية العمل في الإنتاج”. A fictional Suwayda 40-second prototype compares an unclear request with a bounded brief, records rights and missing permissions, separates attendance/recording/publication consent and labels uncertain resources. A paper or light-text alternative keeps the exercise feasible. Quality review, publication, purchase, contracting and equipment authority remain separate.

`production-task` is now “مهمتك الأولى: نسخة تجريبية”. The assignment remains a one-page brief and a 30–60 second prototype or storyboard using learner-owned materials. The completed six-shot example uses original text and shapes, with no real people, outside music or archive. The learner submits only a deidentified summary; a human reviewer checks purpose, rights, feasibility and handover, then gives one actionable change. No raw sensitive files or private links are collected.

The strategic working draft, sensitive-imagery draft, historical Óros proposal, integrated LIFE programme proposal and assets/equipment draft were re-read on 7 October 2026. IDs, tested outcomes, assignment, keys and module versions `2026-10-07.1` remain unchanged. Existing completions and review history remain valid. Production/rights-owner and learner acceptance remain pending.

## Radio path — 0.14.0

`radio-basics` now uses a fictional five-minute Suwayda segment to distinguish a clear episode plan from an open-ended idea. It records linear broadcast, AutoDJ, promotional excerpt, archive/podcast, download and video as separate uses. A paper or light-text exercise captures the rights holder, version, scope, duration, ending method, credit, fallback, reviewer and decision limit.

`radio-task` remains a fictional short episode or playlist with three imaginary works. Its completed example includes one linear-only work, one requiring separate archive permission and one expired work with a substitute. Learners submit a safe seven-line summary, while a human reviewer checks purpose, rights, feasibility and handover and requests one actionable change. Completion does not authorize contact, recording, broadcasting, archiving, downloading or signing.

The current radio-permission document, programme proposal, editorial-policy draft and strategic working draft were re-read on 7 October 2026. IDs, tested outcomes, assignment, answer keys and module versions `2026-10-07.2` remain unchanged. Editorial/rights-owner and learner acceptance remain pending.

## Finance path — 0.15.0

`finance-basics` is now “بداية العمل في المالية”. A fictional Suwayda printing request separates preparation, review, authorization, acceptance and payment. A weak-versus-clear comparison distinguishes expected funding, confirmed funding, received cash and liquidity. The learner uses five questions to check project code/budget line, funding/liquidity, restricted-fund eligibility, evidence/acceptance and decision authority, then completes a safe paper or light-text role card.

`finance-task` is now “مهمتك الأولى: ملف عملية مالية”. The assignment remains one fictional operation file and reconciliation with five movements and a small budget. Its completed example includes restricted cash received, a file ready for payment review but not paid, a duplicate invoice, a documented fee needing coding and a claim missing approval/evidence. The learner submits a deidentified seven-line summary; no real invoice, contract, statement, supplier, payment data or transaction is used.

Reviewer guidance checks the budget/output link, separation of expected/confirmed/received/liquid funds, evidence and status for each movement, duplicate/missing-item handling and authority boundaries. Educational approval does not approve a payment, grant eligibility, signature, account access or a real reconciliation. The strategic working draft, draft financial procedure, restricted-funds/grants draft and assets/equipment draft were re-read on 7 October 2026. IDs, tested outcomes, assignment, answer keys and module versions `2026-10-07.2` remain unchanged. Finance/authority-owner and learner acceptance remain pending.

## People Operations path — 0.16.0

`people-ops-basics` is now “بداية العمل في دعم الأشخاص”. A fictional Suwayda newcomer and an unclear-versus-clear comparison show how to agree on the real relationship without assuming it, available time, one small output, a learning companion and task reviewer by responsibility, minimum access, support and a low-connectivity alternative. A seven-line paper or light-text exercise separates ordinary learning support from urgent risk, complaints, testimony, contract, pay, health and disciplinary records.

`people-ops-task` is now “مهمتك الأولى: خطة انضمام”. The assignment remains one fictional onboarding plan covering role, time, companion, small task, criteria, support, review and orderly exit. Its completed example uses a two-week, two-hours-per-week trial with a 45–60 minute fictional indexing task, a midpoint check, observable criteria, minimum access, an offline fallback and handover. The learner submits only a deidentified seven-line summary.

Reviewer guidance checks the relationship and authority boundaries, a small observable task, realistic support/fallback, and separation of learning from complaints, contracts, performance and disciplinary decisions. The strategic working draft, draft HR policy, draft volunteering policy and operations-role working document were re-read on 7 October 2026. Their unfilled roles and channels remain unfilled and no draft is treated as adopted policy. IDs, tested outcomes, assignment, answer keys and module versions `2026-10-07.2` remain unchanged. People/policy-owner and learner acceptance remain pending.

## Research and Memory path — 0.17.0

`research-basics` is now “بداية العمل في البحث والذاكرة”. A fictional Suwayda researcher begins with one decision and a bounded question before collecting data. A broad-versus-clear comparison and four statement types separate a documented fact, attributed account, interpretation and recommendation. The learner creates three safe source cards recording date, location, evidence, limits, rights and access, on paper or in a light text file.

`research-task` is now “مهمتك الأولى: مذكرة بحث وذاكرة”. The assignment remains a fictional one-page memo with a question, three sources, method, two limitations, labelled fictional finding, recommendation and handover index. Its completed example compares three fictional public or deidentified sources, does not treat likes as demand, marks the result as non-representative, proposes one small test, and hands over the memo, source table, readme and rights/access note. The learner submits only a deidentified seven-line summary.

Reviewer guidance checks the decision/question link, what each source can and cannot establish, separation of method/finding/limits/recommendation, and safe rights/access/handover. Educational completion does not authorize interviews, surveys, sensitive-archive access, publication or an impact claim. The strategic working draft, integrated-program proposal, language/style working document, privacy draft and folder-structure working document were re-read on 8 October 2026. No proposed structure, metric, folder or role is treated as adopted. IDs, tested outcomes, assignment, answer keys and module versions `2026-10-07.2` remain unchanged. Research/privacy-owner and learner acceptance remain pending.

## Technology path — 0.18.0

`technology-basics` is now “بداية العمل في التقنية”. A fictional contributor from Suwayda starts from one observable problem in an approved test environment, compares a vague request with a bounded one and records the user, current behavior, expected behavior, scope and reviewer. The exercise uses a personal account with minimum access, fictional or deidentified test data and no passwords, verification codes, private links, database copies or user records. It adds authorized and denied access, invalid input, narrow-screen and keyboard use, weak connectivity, image failure, cache isolation and rollback thinking.

`technology-task` is now “مهمتك الأولى: بطاقة تغيير تقني”. The practical assignment remains one small fictional change card with acceptance criteria, five tests, a rollback plan and a maintenance owner by responsibility. A completed example separates preparation, code review, release approval and production deployment. The learner submits a safe seven-line summary on paper or in a light text file, and a human reviewer checks scope, tests, minimum access, test data, rollback impact and maintainability before one actionable revision or educational completion.

The strategic working draft, integrated-program proposal, draft digital-security policy, draft privacy policy and operations-role working document were re-read on 8 October 2026. They support operating and documenting the existing stack, least access, individual accounts, protected data, incident containment and tested recovery. Their roles and incident contacts are not treated as appointments, and no draft is treated as adopted policy. IDs, tested outcomes, assignment, answer keys and module versions `2026-10-07.2` remain unchanged. Technical/security-owner and learner acceptance remain pending.

## Leadership, governance and partnerships path — 0.19.0

`leadership-basics` is now “بداية العمل في القيادة”. A fictional contributor from Suwayda prepares one bounded decision and compares it with an informal approval. The lesson separates preparation, review, decision and delegation, records capacity and alternatives, requires disclosure and recusal for a potential interest, and protects editorial independence. It does not treat a draft board, company, title or signing authority as fact.

`leadership-task` is now “مهمتك الأولى: مذكرة قرار”. The practical assignment remains one fictional memo with need, scope, resources, fallback, potential interest, delegation, review and a condition for expansion. Its completed example uses one sixty-minute fictional reading session, an unconfirmed in-kind venue, a separate independent reviewer and an explicit stop or revision gate. The learner submits only a safe eight-line summary, and a human reviewer checks capacity, risks, interest, independence and the actual decision path before one actionable revision or educational completion.

The strategic working draft, integrated-program proposal, draft internal statute, draft partnership policy and draft HR regulation were re-read on 8 October 2026. Their alternative legal forms, proposed bodies, approval routes and unfilled roles remain proposals or drafts. Learning completion does not adopt them or authorize representation, signing, spending, partnership approval or publication. IDs, tested outcomes, assignment, answer keys and module versions `2026-10-07.2` remain unchanged. Governance/partnership-owner and learner acceptance remain pending.

## Specialist batch: WordPress and digital security — 0.20.0

`wordpress` is now “جهّز مسودة على الموقع”. A fictional Suwayda contributor prepares one safe draft and distinguishes a preparation file, draft, pending review and published content. The worked example covers purpose, a short text, an authorized training image, alt text, a non-live link, mobile crop, reviewer and publication decision. It explains that media-library file URLs may remain reachable, and that hiding or removing an image from a page does not prove deletion of the file or its copies. A seven-line paper or text-only content pack supports weak connections.

`digital-security` is now “احمِ حسابك، وانتبه للرسائل المشبوهة”. An inert synthetic phishing message demonstrates urgency, a request for a verification code, an unfamiliar login route, bypassed support and excessive access. The learner stops interaction, verifies through an independent known channel and reports only necessary facts. A six-line offline exercise and incident card record whether any interaction occurred, without collecting credentials, source identity, private links or real account settings.

Optional specialist lessons now render their own retry and completion feedback in the library, without exposing answer keys. The strategic working draft, integrated-program proposal, draft digital-security policy, draft privacy policy and draft editorial policy were re-read on 8 October 2026. Their mandatory language, publishing workflow, proposed roles and incident contacts are not treated as adopted procedure or appointments. IDs, outcomes, answer keys and module versions `2026-10-07.2` remain unchanged, preserving prior completions. Technical/editorial/security/privacy-owner review and trials with new learners remain pending.

## Specialist batch: verification and interviewing/consent — 0.21.0

`verification` is now “كيف نتحقق قبل أن نشارك؟”. A fictional Suwayda example splits one image caption into date, place, cause and scope, then traces an older copy without treating it as proof of every claim. The learner records the origin, an independent check, contradiction, knowns, unknowns and a proposed publish/wait/do-not-use decision. A seven-line paper or light-text exercise, worked evidence log and expanded work card make the next action explicit while excluding real images, private links and source identities.

`interview` is now “مقابلة مريحة، وأسئلة واضحة”. A fictional craft interview separates agreement to speak, record, quote, use a name, use an image and publish. The worked opening gives refusal and stopping choices before five open questions. The lesson checks indirect identification, limits raw-recording access, stops when distress appears and sends high-risk interviews to a specialist process. It does not ask learners to contact a real person or collect testimony.

Both lessons include actionable reviewer guidance and specialist retry/completion feedback without exposing answer keys. The current strategy, draft editorial, security, safeguarding, imagery and privacy policies, language/style working document and Safe Voices proposal were re-read on 8 October 2026. They remain working, draft or proposal sources; no reporting contact, reviewer or protection role is invented. IDs, outcomes, answer keys and module versions `2026-10-07.2` remain unchanged, preserving prior completions. Editorial/visual-verification/safeguarding/privacy-owner review and learner trials remain pending.

## Specialist batch: reporting and editing — 0.22.0

`reporting` is now “ابدأ بسؤال محلي واضح”. A fictional Suwayda contributor turns a broad claim about a fictional cultural centre into one answerable question, four claim parts and an evidence map. The worked assignment records its audience and size, evidence and limits for two claims, a safe right-of-reply path, unknowns, risk and publication boundaries. An eight-line paper or light-text exercise excludes real names, contact details, documents and private links.

`editing` is now “راجع المادة، وصحّح بوضوح”. A fictional editor identifies a wrong time span, unsupported generalizations, an unverified cause and a missing right of reply. The worked edit narrows the language to the available fictional record and preserves unknowns. A separate correction example identifies the original error, the corrected figure, the date and the dependent text, graphic and social post that need updating instead of silently replacing the number.

Both courses include expanded work cards, reviewer guidance and specialist retry/completion feedback without exposing answer keys. The current strategy, journalism-training consultation document, draft editorial policy, language/style working document and draft sensitive-imagery policy were re-read on 8 October 2026. They remain working, consultation or draft sources; no editor, reporting channel, fixed deadline or publication authority is invented. IDs, outcomes, answer keys and module versions `2026-10-07.2` remain unchanged, preserving prior completions. Editorial/rights/safety-owner review and learner trials remain pending.

## Specialist batch: social publishing and translation — 0.23.0

`social` is now “من مادة معتمدة إلى منشور واضح”. A fictional Suwayda contributor converts one approved fictional story into a small platform package, preserves its number and uncertainty, records link, image rights, alt text and crop, and separates a question, legitimate criticism, correction request and privacy threat. A seven-line paper or light-text exercise and expanded card keep account access, moderation and publication outside educational completion.

`translation` is now “ترجمة دقيقة وعربية سهلة”. A fictional Suwayda translator restores the number, attribution and uncertainty lost from a short English sentence. The worked example, three-term glossary, open editorial question, eight-line low-connectivity exercise and two-stage comparison preserve meaning while producing readable fusha.

Both courses include reviewer guidance and specialist retry/completion feedback without exposing answer keys. The current strategy, draft editorial policy, language/style working document, draft sensitive-imagery policy and draft privacy policy were re-read on 8 October 2026. They remain working or draft sources; no account access, moderator, translator, correction channel or publication authority is invented. IDs, outcomes, answer keys and module versions `2026-10-07.2` remain unchanged, preserving prior completions. Editorial/audience/rights/privacy/language-owner review and learner trials remain pending.

## Specialist batch: photography and video — 0.24.0

`photo` is now “صور تحكي، وتسليم يحفظ السياق”. A fictional Suwayda photographer prepares a purpose-led four-shot list for an invented pottery scene, checks direct and indirect identification, replaces a wide image whose crop would change the meaning and delivers a safe caption, rights scope, restricted-original location and review-copy metadata. An eight-line paper or light-text exercise collects no real image or private link.

`video` is now “ابنِ تسلسلاً بصرياً واضحاً”. A fictional Suwayda contributor prepares a 35-second, six-shot paper-poster sequence using owned materials, preserves chronology, removes an unauthorized background song, adds readable screen text and a short transcript and hands over a named review version with one clear question. The exercise uses a storyboard only and asks for no recording or upload.

Both courses include crop and deidentification limits, accessible alternatives, expanded work cards, reviewer guidance and specialist retry/completion feedback without exposing answer keys. The current strategy and draft sensitive-imagery, privacy and editorial policies were re-read on 8 October 2026. They remain working or draft sources; no archive access, rights approval, reviewer or publication authority is invented. IDs, outcomes, answer keys and module versions `2026-10-07.2` remain unchanged, preserving prior completions. Production/editorial/rights/privacy/accessibility-owner review and learner trials remain pending.
