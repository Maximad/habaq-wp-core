# Habaq learning implementation progress

## Current checkpoint — 7 October 2026

- Working branch: `codex/member-learning`; existing draft PR: https://github.com/Maximad/habaq-wp-core/pull/21. Main is not merged or modified by this task.
- Starting branch head: `195ece86756919f3de10c2ae35cbc1e8df3c1e7c`; starting tree: `b27b58f5205e72e02e20f571318b6737dd1f32a2`. Fresh PR read confirms six commits through the deployed 0.6.1 release and no overlapping changes. The local staged baseline exactly matched that tree.
- Existing completed foundation: protected onboarding, five shared lessons and two lessons in one selected path, one practical assignment with human review, ten path choices, 36 optional specialist courses, 28 role maps, support/application review, source labels, photographic RTL portal and reusable native Cover pattern. Existing content is draft curriculum, not domain-owner or learner approval.
- This log was absent at the start and is the durable resume point for future batches.

## Batch 1 — reading, connectivity and navigation (0.6.2)

Implemented:

- A visible Arabic “قراءة دون صور” / “عرض الصور” control. `reading=text` omits the hero attachment in PHP, so the image is not requested by the learning markup. Native navigation, search and form return URLs retain the preference. No cookie, new user metadata or JavaScript is introduced. Toggle URLs include only bounded, known learning filters/IDs.
- Default mode retains the real Habaq full-width photograph and responsive cropping. Text mode shortens the header; it also explains that saving results still requires a connection. This is lower-bandwidth reading, not a promise of offline submission or elimination of every site/theme resource.
- Lesson links go directly to named, focusable reading panels. A second lesson jump in the path list helps narrow-screen users bypass seven navigation items. The progress bar has an Arabic accessible name.
- Quiz and acknowledgement labels, lesson links and disclosure headings have at least 44px target height. Existing 16px body type and RTL/mobile breakpoints remain. Long article content can wrap without forcing the grid wider.
- Search is retained in specialist course/back links. No module ID, quiz key, tested requirement, source label or content version changes; learner completions and histories remain intact.

Validation before commit:

- 514 isolated PHP behavioral checks passed (existing 500 plus 14 checks for omitted image generation, safe toggle parameters, mode persistence, accessible destinations, record immutability, key privacy and member gate).
- All 36 PHP files passed PHP 8.3 lint; `git diff --check` passed.
- Current strategic Drive source re-read: `1aBXRfbPGNhtCSo1CXigJiqIJtE2NQ64W8jHLnL3P7ZU`, modified 6 October 2026 at 18:04:46 UTC. No organizational course claims changed in this UI batch.
- Authenticated WordPress Deployer for Git was observed for this run, configured to the existing repository and branch. Publication and live checks completed below.

Commit/deployment — completed:

- Release commit: `492890bd079ef3e466b3828d16e790137e9cbd0f`, tree `2ba6e49c70799245ce418d93ae020369a209ad31`. Branch update used expected head `195ece86756919f3de10c2ae35cbc1e8df3c1e7c`, fast-forward only. The local staged tree matched the created remote tree exactly.
- Code rollback preserved as `rollback/learning-0.6.1` at the starting head before publication. No database backup or restore test is claimed.
- Published through existing Deployer for Git on 7 October 2026 around 08:33 UTC. UI reported “Package updated successfully.” Live CSS URL has `ver=0.6.2`, and new markup/control is visible at https://habaq.online/learning/.
- Default live view: responsive photograph restored successfully; hero width 1348px equals document content width, viewport 1363px including scrollbar, no horizontal overflow; body type remains 16px. Answer/acknowledgement labels measured 45.59px high.
- Text-only live view: zero `.habaq-learning__photo` elements. Keyboard Enter on “تابع درسك” moved focus to `habaq-lesson`, with the reading panel in view. Navigation to library and submitting its photographer filter retained `reading=text`; five matching courses displayed. Opening photo course landed/focused `habaq-courses` with text mode retained. “عرض الصور” restored the photo. No real learner completion or review was submitted during these checks.
- Screenshot: `habaq-learning-reading-0-6-2-1791362085671.jpg`, captured 7 October 2026 at 08:34:45 UTC and saved as proof. Library identity `libfile_4fc44916399881918969574f912f8988`.
- Active batch is complete. Follow-up documentation commit records deployment evidence; it does not change plugin code or require another deployment. Draft PR #21 remains the review entry point.

## Batch 2 — shared lessons: welcome and roles (0.7.0)

Implemented:

- Rewrote `welcome` as “حبق: من أين نبدأ؟” in short, direct fusha. It now explains the local starting point, three main work streams and Hub Sweida’s independence, then uses an explicitly fictional neighbourhood-library example, a completed three-sentence introduction, a short exercise and a stronger work card.
- Rewrote `roles` as “دورك: ما الذي تتولاه، ومن يساعدك؟”. It now separates preparing, reviewing and authorizing work, defines delegation in plain language, and uses a fictional bounded social-post task to show publishing, promise and spending limits. Its exercise and work card require one output, one reviewer, one approval boundary, support and handover.
- Added optional server-rendered lesson feedback. A wrong attempt shows a lesson-specific hint without naming an option or exposing the raw answer key. A completed lesson shows the decision principle beside the saved record. The generic supportive retry remains.
- Preserved both IDs, prerequisites, correct answer indexes and module version `2026-10-07.1`. Their tested outcomes did not materially change, so prior completions remain current. Plugin release `0.7.0` tracks the editorial batch and refreshes cached assets.
- Regenerated the complete curriculum and review plan from current repository content. Replaced the same Library artifact identities after validation: review plan `libfile_0b83affecccc8191bfe9e24644ab0345` and curriculum `libfile_17af04fe58748191b961a4e5480eaed6`, both now at Library version 1. No duplicate curriculum/review files were created.

Sources re-read on 7 October 2026:

- `التخطيط الاستراتيجي | تدريب حياة`, modified 6 October 2026 at 18:04:46 UTC: current working vision, mission, local grounding, openness and institutional-development context.
- `تجمّع حبق | عرض تعريفي لخمس دقائق`, modified 1 October 2026 at 15:18:51 UTC: current descriptions of Media, People and Production and their collaboration.
- `4- مسودة سياسة التطوع لتجمع حبق`, modified 23 December 2025 at 18:19:47 UTC: draft task, supervision, time, safe refusal, publication authority, expense and handover boundaries. It remains a draft and contains unfilled contact roles.

Validation before commit:

- 526 isolated behavioral checks passed. New checks cover preserved lesson versions/keys, explicit fictional examples, Hub independence, role authority/handover, attempt-specific feedback, saved completion feedback, unchanged record behavior and hidden raw keys.
- All 36 PHP files passed PHP 8.3 lint; `git diff --check` passed.
- Domain-owner and learner acceptance are not claimed. Live publication and checks are recorded below.

Commit/deployment — completed:

- Release commit: `946834786af1b346ab4d803d4a93fb32fbcefd19`, tree `3a97979797f985488856a2809a7b6c692f12ee80`. Branch update used expected head `3ba5caf7383a8c98d1236b8eba2e25fd432881ae`, fast-forward only; the local staged tree matched the created remote tree exactly.
- Code rollback preserved as `rollback/learning-0.6.2` at `3ba5caf7383a8c98d1236b8eba2e25fd432881ae` before publication. No database backup or restore test is claimed.
- Published through the existing WordPress Deployer for Git on 7 October 2026 around 10:29 UTC. The UI reported “Package updated successfully.” The live stylesheet loads with `ver=0.7.0`.
- Live `welcome` shows the new title, fictional Salma example, worked example and explicit Hub Sweida independence. Live `roles` shows the new title, fictional Noor example, worked role card and the specific retry hint when the bounded wrong-notice state is rendered. No raw `correct` key was present in either frontend response.
- The `roles` form remained absent because `welcome` was not completed for the inspecting account, confirming that the existing prerequisite gate still applies. No quiz, acknowledgement, completion or review submission was made during the live checks; learner state was not mutated.
- At 1363px viewport width, the live welcome view had 1348px document width and no horizontal overflow. This is desktop evidence only and does not replace the pending real narrow-screen and zoom checks.
- Screenshot: `habaq-shared-lessons-0-7-0-1791365402014.jpg`, captured 7 October 2026 and saved as live evidence. Library identity `libfile_f52db898eecc8191816c3336c05c0cab`.
- Active batch is complete. The documentation-only checkpoint that records this evidence does not change plugin code or require another deployment. Draft PR #21 remains the review entry point.

## Batch 3 — shared lessons: conduct and data (0.8.0)

Implemented:

- Rewrote `conduct` as “نعمل باحترام، ونطلب الموافقة” in short, direct fusha. It separates consent to attend, record and publish; uses an explicitly fictional small music-event example; adds a completed consent decision and deidentified exercise; and expands the work card to include use, risk, review, designated reporting role and independent alternative.
- Rewrote `data` as “أين نحفظ الملفات، ومع من نشاركها؟”. It uses three fictional files to practise purpose, classification, least access, deidentification, approved storage, AI-tool boundaries and first response to a lost device or exposed link. It explicitly tells learners not to upload a real file, report, testimony or password.
- Added lesson-specific retry and completion feedback through the existing server-rendered mechanism. Feedback explains the decision principle without exposing raw answer keys.
- Preserved both IDs, prerequisites, correct answer indexes and module version `2026-10-07.1`. Their tested outcomes did not materially change, so prior completions remain current. Plugin release `0.8.0` tracks this editorial batch.
- Regenerated the complete curriculum and review plan from current repository content. Replaced the same Library artifact identities after validation: review plan `libfile_0b83affecccc8191bfe9e24644ab0345` and curriculum `libfile_17af04fe58748191b961a4e5480eaed6`, both now at Library version 2. No duplicate curriculum or review file was created.

Sources re-read on 7 October 2026:

- `التخطيط الاستراتيجي | تدريب حياة`, modified 6 October 2026 at 18:04:46 UTC: current institutional context, including the need to adopt basic policies and distribute recurring responsibility.
- `1- مسودة المبادئ العشرة لتجمع حبق`, modified 26 September 2026 at 07:12:53 UTC: respect, non-discrimination, non-exploitation, privacy, consent, safety, AI boundaries and non-retaliation. Reporting fields remain placeholders.
- `15- مسودة سياسة حماية البيانات والخصوصية في حبق`, modified 13 December 2025 at 12:45:38 UTC: data minimization, four classifications, least access, protected storage, incident response and AI exclusions. It remains a draft and names roles that have not been appointed here.
- `12- مسودة ملحق D: سياسة الصور والمحتوى الحساس في حبق`, modified 5 September 2026 at 18:38:54 UTC: informed consent, no pressure, separate use decisions, protection beyond consent where risk remains, and sensitive-content access limits. It remains a draft.

Validation before commit:

- 537 isolated behavioral checks passed. New checks cover preserved lesson versions/keys, fictional examples, separate consent decisions, safe alternate role, three-file classification, data minimization, attempt-specific feedback, saved completion feedback, unchanged record behavior and hidden raw keys.
- All 36 PHP files passed PHP 8.3 lint; `git diff --check` passed.
- Domain-owner, privacy/safety and learner acceptance are not claimed. Live publication and checks are recorded below.

Commit/deployment — completed:

- Release commit: `47d8a813dc293b7575e96b016c72e4d8c3fd49b7`, tree `7b148e7ebc63522cfd8f236703747a888be766ff`. Branch update used expected head `e11c28e2e24383932f303fda6813a97f05b2302f`, fast-forward only.
- Code rollback preserved as `rollback/learning-0.7.0` at the starting head before publication. No database backup or restore test is claimed.
- Published through the existing WordPress Deployer for Git on 7 October 2026 around 10:26 UTC. The UI reported “Package updated successfully.” The live stylesheet loads with `ver=0.8.0`.
- Live `conduct` shows the new title, fictional Layan and Samer event example, separate consent decisions and the lesson-specific retry hint. Live `data` shows the new title, three fictional files, worked card, AI boundary, incident step and its lesson-specific retry hint. No raw `correct` key was present in either frontend response.
- Both forms remained absent because earlier shared prerequisites were incomplete for the inspecting account, confirming that the existing prerequisite gate still applies. No quiz, acknowledgement, completion, support or review submission was made; learner state was not mutated.
- At the 1363px desktop viewport, neither live lesson produced horizontal overflow. This does not replace the pending real narrow-screen and zoom checks.
- Screenshot: `habaq-conduct-data-0-8-0-1791368796176.jpg`, captured 7 October 2026 and saved as live evidence. Library identity `libfile_584a5fa329588191af3b2140a3d4275d`.
- Active batch is complete. The documentation-only checkpoint that records this evidence does not change plugin code or require another deployment. Draft PR #21 remains the review entry point.

## Batch 4 — shared lesson: workflow (0.9.0)

Scope and decisions:

- Rewrote `workflow` as “من فكرة صغيرة إلى تسليم واضح” in warm, direct fusha. It now has one observable result, a fictional Suwayda reading-circle example, a completed task card, a small safe exercise, low-connectivity guidance, a clearer handover and attempt-specific feedback.
- The example limits the learner to preparing a one-page plan. It does not authorize booking, announcement, spending or another external commitment. The lesson separates recorded learning, human review of work, publication/spending authority, reach and evidence of impact.
- Preserved the lesson ID, prerequisite, correct answer index and module version `2026-10-07.1`. The tested outcome—turning an idea into a small task with an output, reviewer, deadline and follow-up—did not materially change, so prior completions remain current. Plugin release `0.9.0` tracks this editorial batch.

Sources re-read on 7 October 2026:

- `التخطيط الاستراتيجي | تدريب حياة`, modified 6 October 2026 at 18:04:46 UTC: Habaq can experiment quickly, but needs clearer priorities, delegation, institutional roles and measurement boundaries.
- `حبق ميديا | دليل متابعة التدريب وجودة العمل الصحفي`, modified 28 September 2026 at 10:36:23 UTC: separate monitoring from evaluation and publication decisions; do not invent a pre-training score; use a current baseline with stated limitations; a four-to-six-week application conversation is a proposed follow-up practice.

Validation before commit:

- 545 isolated behavioral checks passed. New checks cover the preserved lesson version/key, fictional example, external-action limits, low-connectivity option, safe exercise, expanded handover card, attempt-specific feedback, unchanged completion behavior and hidden raw answer key.
- All 36 PHP files passed PHP 8.3 lint; the existing training JavaScript passed syntax validation; `git diff --check` passed.
- Regenerated and validated the 61-card review plan and the 61-lesson curriculum. Replaced the same Library artifact identities using expected version 2: review plan `libfile_0b83affecccc8191bfe9e24644ab0345` and curriculum `libfile_17af04fe58748191b961a4e5480eaed6`, both now at Library version 3. No duplicate curriculum or review file was created.
- Domain-owner and learner acceptance are not claimed.

Commit/deployment — completed:

- Release commit: `86143ced83c50cb405448f467e8c8d6f356d1a89`, tree `6e669692f72c190b6c218cadee0b31f3968f7f84`. The branch was rechecked as identical to expected head `03d0b4388c85580486960d7d40bfe1ef129d282c`, then updated fast-forward only with that expected-head safeguard.
- Code rollback preserved as `rollback/learning-0.8.0` at the starting head before publication. No database backup or restore test is claimed.
- Published through the existing WordPress Deployer for Git on 7 October 2026 around 11:35 UTC. The UI reported “Package updated successfully.” The live learning stylesheet loads with `ver=0.9.0`.
- Live `workflow` shows the new title, fictional Mira example, completed-card disclosure, low-connectivity option, exercise and lesson-specific retry hint. No raw `correct` key was present in the frontend response.
- The inspecting account had not completed earlier shared prerequisites, so the workflow form remained absent while the content was readable. This confirms the existing prerequisite gate remained active. No quiz, acknowledgement, completion, support or review submission was made; learner state was not mutated.
- At the 1348px desktop viewport, the lesson had no horizontal overflow. This does not replace the pending real narrow-screen, keyboard and larger-text acceptance checks.
- Screenshot: `habaq-workflow-0-9-0-1791372926170.jpg`, captured 7 October 2026 and saved as live evidence. Library identity `libfile_a267f6be0dc48191a80cc20642447521`.
- Active batch is complete. The documentation-only checkpoint that records this evidence does not change plugin code or require another deployment. Draft PR #21 remains the review entry point.

## Batch 5 — operations and coordination path (0.10.0)

Scope and decisions:

- Rewrote `operations-basics` as “بداية العمل في التنسيق”. It now separates the task owner, reviewer, decision owner and coordinator; uses a fictional Suwayda work week; compares an unclear task with a clear one; adds a safe exercise, stronger role card and formative feedback.
- Rewrote `operations-task` as “مهمتك الأولى: التنسيق”. It remains one weekly board of three fictional tasks, one decision, one blocker and a short handover. It now adds a completed example, honest unconfirmed-resource label, deidentified submission prompt, four reviewer questions and one-actionable-change guidance.
- Preserved both lesson IDs, prerequisites, practical assignment, correct answer indexes and module version `2026-10-07.2`. Their tested requirements did not materially change, so existing completions and review history remain current. Plugin release `0.10.0` tracks this editorial batch.
- The lessons do not appoint an operations coordinator, adopt draft procedures, approve a budget, change real assignments or grant access. Learning, human review and operational authority remain separate.

Sources re-read on 7 October 2026:

- `التخطيط الاستراتيجي | تدريب حياة`, modified 6 October 2026 at 18:04:46 UTC: current concentration of knowledge and decisions, limited specialist capacity, overload risk and the goal of delegating recurring operations.
- `منسق العمليات - التوصيف الوظيفي`, modified 6 January 2026 at 16:34:09 UTC: shared calendar, owners and deadlines, weekly due/blocked/decision summary, simple project boards, early risk signals, organized folders and decision records. It is a working job description, not an appointment.
- `برنامج حبق المتكامل — منحة حياة 2026–2027`, modified 18 September 2026 at 13:33:40 UTC: planning proposals are conditional on contract and team capacity; shared responsibility needs agreed time, tools and delegation; unconfirmed funds do not justify commitments. It remains a proposal for implementation and internal adoption.
- `10- مسودة دليل الإجراءات المالية لحبق`, modified 13 December 2025 at 13:19:43 UTC: project owners, finance and approvers have separate roles; placeholder approval limits remain unfilled; spending and commitments require the defined review path. It remains a draft.

Validation before commit:

- 557 isolated behavioral checks passed. New checks cover preserved module versions, assignments and keys; fictional local examples; owner/reviewer/decision separation; authority limits; the three-task board; reviewer guidance; safe summaries; unconfirmed resources; lesson feedback; and hidden raw answer keys.
- All 36 PHP files passed PHP 8.3 lint; the existing training JavaScript passed syntax validation; `git diff --check` passed.
- Regenerated and validated the 61-card review plan and 61-lesson curriculum. Replaced the same Library artifact identities with expected-version safeguards: review plan `libfile_0b83affecccc8191bfe9e24644ab0345` and curriculum `libfile_17af04fe58748191b961a4e5480eaed6`, both now at Library version 4. No duplicate curriculum or review file was created.
- Created rollback branch `rollback/learning-0.9.0` from the previous live head `763f267e755d10ff9b6b027363d6e177f1619897`. Saved release commit `1899ec4a4d3e2158b150f70f3b1fcba91663af84` to `codex/member-learning` with an expected-head safeguard; no force update or merge to `main` was used.
- Published release `0.10.0` through the existing authenticated WordPress Deployer for Git workflow on 7 October 2026 at approximately 14:31 UTC. The deployer reported “Package updated successfully.”
- Verified the live installed-plugin row reports Habaq Engine `0.10.0`, and the protected learning page loads `assets/learning.css?ver=0.10.0`. The inspecting account remains on its existing leadership path; the operations path was not selected during verification, so no learner track, answer, completion or review record was changed. Operations lesson content was verified by the isolated behavioral checks and generated curriculum rather than by mutating that account.
- Screenshot: `habaq-operations-0-10-0-1791383457475.jpg`, captured 7 October 2026 and saved as live version evidence. Library identity `libfile_6507c3bc54dc8191b63c578d370697b2`.
- Domain-owner and learner acceptance are not claimed.

## Batch 6 — Habaq Media path (0.11.0)

Scope and decisions:

- Rewrote `media-basics` as “بداية العمل الصحفي”. It now starts from one answerable question, uses a fictional Suwayda neighbourhood-library example, compares a weak pitch with a bounded one, maps claims to independent verification and adds a paper-friendly exercise and fuller reporting card.
- Rewrote `media-task` as “مهمتك الأولى: مقترح قصة”. The assignment remains a one-page fictional story pitch, now with a completed 500-word example, two-claim verification matrix, safe deidentified submission, low-connectivity option, four reviewer questions and one-actionable-change guidance.
- Preserved both lesson IDs, prerequisites, practical assignment, correct answer indexes and module version `2026-10-07.1`. The tested requirements did not materially change, so existing completions and review history remain current. Plugin release `0.11.0` tracks this editorial batch.
- Neither lesson authorizes contacting, recording or photographing a real source, publishing a story, or collecting a sensitive testimony. Learning completion, editorial review, safety review and publication authority remain separate; a professional review may conclude that the story should not proceed.

Sources re-read on 7 October 2026:

- `التخطيط الاستراتيجي | تدريب حياة`, modified 6 October 2026 at 18:04:46 UTC: local narrative and cultural/media production are strategic, while clear roles and a realistic workload remain necessary.
- `حبق ميديا | دليل متابعة التدريب وجودة العمل الصحفي`, modified 28 September 2026 at 10:36:23 UTC: learning, work review and publication decisions must remain separate; use safe deidentified records and human editorial review. It remains a consultation document.
- `17- مسودة سياسة التحرير والنشر والتصحيح وحق الرد في حبق`, modified 13 December 2025 at 12:48:25 UTC: accuracy before speed, independent verification, careful certainty language, identity protection, right of reply and editor approval. It remains a draft.
- `دليل اللغة والأسلوب لحبق ميديا`, modified 28 October 2025 at 19:41:07 UTC: precise, calm, human-centred Arabic; no exaggeration; identify uncertainty; use independent support for harmful claims. It remains a working guide.
- `أصوات آمنة`, modified 19 September 2026 at 14:14:44 UTC: proposed training, production, review and safe-publishing sequence; informed consent and non-publication where risk remains. It remains a proposal, not an adopted service or completed project.
- `12- مسودة ملحق D: سياسة الصور والمحتوى الحساس في حبق`, modified 5 September 2026 at 18:38:54 UTC: protect people before the story, consider indirect identification, and add review or withhold publication for sensitive material. It remains a draft.

Validation before commit:

- 571 isolated behavioral checks passed. New checks cover preserved module versions, assignments and keys; the fictional local example; weak/clear comparison; independent verification; contact and publication boundaries; completed pitch; reviewer guidance; safe low-connectivity submission; lesson feedback; and hidden raw answer keys.
- All 36 PHP files passed PHP 8.3 lint; the existing training JavaScript passed syntax validation; `git diff --check` passed.
- Regenerated and validated the 61-card review plan and 61-lesson curriculum. Replaced the same Library artifact identities with expected-version safeguards: review plan `libfile_0b83affecccc8191bfe9e24644ab0345` and curriculum `libfile_17af04fe58748191b961a4e5480eaed6`, both now at Library version 5. No duplicate curriculum or review file was created.
- Editorial/safety-owner and learner acceptance are not claimed.

Commit/deployment — prepared, publication blocked:

- Created code rollback branch `rollback/learning-0.10.0` from the previously verified live head `6edd27e6c33cc7515a8d52bb602a61f6ccf871a5` before publication. No database backup or restore test is claimed.
- Saved release commit `1e2338bd13fa6307bcfb5be4fb11816fcae24565`, tree `a3b397bd11aad26733989139f66f825717df343a`, to `codex/member-learning` with an expected-head fast-forward safeguard. No force update or merge to `main` was used.
- Live publication was not attempted: the existing WordPress deployer redirected to a reauthentication form in this non-interactive run, and no Hostinger WordPress deployment tool was available. Credentials were not requested, inspected or changed. The last verified live release remains `0.10.0`; release `0.11.0` is prepared on the draft PR but is not claimed as live.
- The public learning page still returns the protected member sign-in message. No learner track, answer, completion or review record was changed during the live read-only check.

## Batch 7 — Habaq People path (0.12.0)

Scope and decisions:

- Rewrote `people-basics` as “بداية العمل في حبق ناس”. It now starts from one cultural purpose and an observable participant action; uses a fictional Suwayda rhythm session; compares a broad idea with a bounded plan; and adds newcomer, rest, access-barrier, non-photographed participation and decision-limit practice.
- Rewrote `people-task` as “مهمتك الأولى: خطة جلسة”. It remains a one-page fictional 45–60 minute session plan, now with a completed 50-minute example, deidentified low-connectivity submission, four reviewer questions, one-actionable-change guidance and explicit booking, spending, partnership and announcement limits.
- Preserved both lesson IDs, prerequisites, practical assignment, correct answer indexes and module version `2026-10-07.1`. The tested requirements did not materially change, so existing completions and review history remain current. Plugin release `0.12.0` tracks this editorial batch.
- Neither lesson authorizes delivering or announcing an event, booking a space, spending money, engaging a real partner or collecting attendance details. Learning completion, human review, partner agreement, safety review, spending authority and announcement authority remain separate. Hub Sweida remains independent and is not presumed to host or approve the fictional session.

Sources re-read on 7 October 2026:

- `التخطيط الاستراتيجي | تدريب حياة`, modified 6 October 2026 at 18:04:46 UTC: participation and openness are strategic, while overload, unclear roles and unsupported impact claims remain risks. It remains a working draft.
- `خطة مشروع كاملة - حبق ناس × جوقة Óros`, modified 13 November 2025 at 13:04:01 UTC: short accessible roles, low-electricity options, privacy and participation design. It remains a historical proposal; proposed activities and measurement are not treated as completed or adopted.
- `برنامج حبق المتكامل — منحة حياة 2026–2027`, modified 18 September 2026 at 13:33:40 UTC: cultural programmes should enable participation and correction, use agreed time/resources and avoid treating reach alone as trust. It remains a proposal.
- `سياسة خدمات حبق ناس للفعاليات والشراكات`, modified 2 January 2026 at 20:15:45 UTC: event roles, partner/place responsibilities, content and safety expectations, and cancellation/change terms. It remains a draft or proposal.
- `1- مسودة المبادئ العشرة لتجمع حبق`, modified 26 September 2026 at 07:12:53 UTC: respect, non-discrimination, consent, privacy, safety and non-retaliation. Reporting roles remain unassigned and the document remains a draft.
- `12- مسودة ملحق D: سياسة الصور والمحتوى الحساس في حبق`, modified 5 September 2026 at 18:38:54 UTC: participation and photography require distinct decisions, and protection comes before documentation. It remains a draft.

Validation before commit:

- 586 isolated behavioral checks passed. New checks cover preserved module versions, assignments and keys; the fictional local example; broad/clear comparison; newcomer and rest options; participation without photography; Hub independence; access and authority cards; completed session plan; reviewer guidance; safe low-connectivity submission; impact limits; lesson feedback; and hidden raw answer keys.
- All 36 PHP files passed PHP 8.3 lint; the existing training JavaScript passed syntax validation; `git diff --check` passed.
- Regenerated and validated the 61-card review plan and 61-lesson curriculum. Replaced the same Library artifact identities with expected-version safeguards: review plan `libfile_0b83affecccc8191bfe9e24644ab0345` and curriculum `libfile_17af04fe58748191b961a4e5480eaed6`, both now at Library version 6. No duplicate curriculum or review file was created.
- Programme/safety-owner and learner acceptance are not claimed.

Commit/deployment — prepared, publication still blocked:

- Saved release commit `370c4e84b78873a5f91f8fb4f688ef15c03ea1d4`, tree `d9dc85f21dcda0b09964f639690fec869f2b5c60`, to `codex/member-learning` with an expected-head fast-forward safeguard. No force update or merge to `main` was used.
- Existing code rollback branch `rollback/learning-0.10.0` still preserves the last verified live code point. Because `0.11.0` was not published, no misleading live rollback branch was created for it. No database backup or restore test is claimed.
- Live publication was not retried: the preceding checkpoint established that the WordPress deployer requires reauthentication, and this non-interactive run has no Hostinger WordPress deployment tool. Credentials were not requested, inspected or changed. The last verified live release remains `0.10.0`; releases `0.11.0` and `0.12.0` are prepared on the draft PR but are not claimed as live.
- No live learner page or learner record was changed in this batch.

## Batch 8 — Habaq Production path (0.13.0)

Scope and decisions:

- Rewrote `production-basics` and `production-task` with a fictional Suwayda 40-second prototype, a weak/clear brief comparison, completed storyboard plan, rights register, low-connectivity alternatives, reviewer guidance and formative feedback.
- Preserved both IDs, prerequisites, tested outcomes, practical assignment, correct answer indexes and module version `2026-10-07.1`. Existing completions/history remain valid. Plugin release `0.13.0` records the editorial change.
- Production quality, rights/safety review, educational review, publishing, purchasing, contracts and equipment authority stay separate. No real people, external music, raw sensitive files or private learner material is requested.

Sources re-read on 7 October 2026:

- Strategic working draft: modified 6 October 2026 at 18:04:46 UTC.
- Sensitive-imagery policy draft: modified 5 September 2026 at 18:38:54 UTC.
- Historical People × Óros proposal: modified 13 November 2025 at 13:04:01 UTC.
- Integrated LIFE programme proposal: modified 18 September 2026 at 13:33:40 UTC.
- Assets/equipment draft: modified 13 December 2025 at 12:38:15 UTC. The verified ID is `1S3ythF_Faun4llO_C_Tx4Md3whPVCMWMW80Fgg37fVA`, matching the source register. No adoption or appointment is inferred.

Validation and delivery:

- 601 isolated behavioral checks passed, including preserved requirements, rights/consent limits, worked prototype, reviewer guidance, safe submission, feedback and hidden raw answer keys.
- All 36 PHP files passed PHP 8.3 lint; training JavaScript syntax and `git diff --check` passed. Both HTML documents parsed and their updated production content was verified. The existing 61-card review plan and 61-lesson curriculum were replaced in place with expected Library version 6, both now version 7. No duplicate artifact was created.
- Saved release commit `130cb28834ef8878ae0d73972bbfb332af623add`, tree `d7086ede03eed378490cc5ccdc8d6056b5e4a37b`, to the existing `codex/member-learning` branch using expected head `e2259ee585658e1ed275c1fcb0cc442361722d64`, fast-forward only. No force update or merge to main occurred.
- Publication was not retried against the already-established WordPress reauthentication blocker. Current callable Hostinger tools cover AI Builder sites, not this WordPress deployment. Releases 0.11.0–0.13.0 are prepared on draft PR #21; last verified live remains 0.10.0. No live learner records changed.
- Production/rights-owner review and learner acceptance remain pending. This batch is complete in implementation.
- The preceding execution was interrupted by a temporary usage-limit approval-review failure before final lint/commit. The local code and tests were preserved and this batch resumes them.
- Last verified live release remains `0.10.0`; the established deployment blocker is WordPress reauthentication with no callable Hostinger deployment method. Code rollback remains `rollback/learning-0.10.0`. Credentials and learner state remain untouched.

## Batch 9 — Radio path (0.14.0)

Scope and decisions:

- Rewrote `radio-basics` as “بداية العمل في الراديو” and `radio-task` as “مهمتك الأولى: مخطط حلقة”. They now use a fictional Suwayda five-minute segment, a broad/clear comparison, a completed three-work episode plan, explicit rights scopes, low-connectivity alternatives, reviewer guidance and formative feedback.
- Preserved both IDs, prerequisites, tested outcomes, practical assignment, correct answer indexes and module version `2026-10-07.2`. Existing completions and review history remain current. Plugin release `0.14.0` records the editorial batch.
- Learning completion, educational review, editorial/rights review, permission to contact or record, broadcast, AutoDJ, archive, download and signing authority remain separate. The exercise uses imaginary works and collects no music, recordings, signed permissions, contact details or private links.

Sources re-read on 7 October 2026:

- `التخطيط الاستراتيجي | تدريب حياة`, modified 6 October 2026 at 18:04:46 UTC: audio and platform distribution can widen access, while capacity and priorities remain limited. It remains a working draft.
- `إذن محدود لبث أعمال موسيقية عبر راديو حبق`, modified 8 September 2026 at 00:39:53 UTC: the proposed permission is limited, non-exclusive and terminable; linear broadcast, AutoDJ, promotion, archive/podcast, download, video and modification have distinct scopes. It remains a draft with unfilled fields.
- `برنامج حبق المتكامل — منحة حياة 2026–2027`, modified 18 September 2026 at 13:33:40 UTC: Radio is proposed as a six-item experiment within Media and expands only after evaluation. It remains a proposal.
- `17- مسودة سياسة التحرير والنشر والتصحيح وحق الرد في حبق`, modified 13 December 2025 at 12:48:25 UTC: production, fact review, sensitivity review, publication decision and archiving are distinct steps; music and effects require license, own production or written permission. It remains a draft.

Validation and delivery:

- 617 isolated behavioral checks passed. New checks cover preserved versions, assignment and answer keys; the fictional local example; distinct rights scopes; no invented radio role; completed three-work plan; safe low-connectivity submission; reviewer guidance; formative feedback; and hidden raw answer keys.
- All 36 PHP files passed PHP 8.3 lint; training JavaScript syntax and `git diff --check` passed before documentation generation.
- Both generated HTML documents parsed successfully and contain the 0.14.0 Radio lesson titles and content. The existing 61-card review plan and 61-lesson curriculum were replaced in place using the expected Library version 7; both are now version 8. No duplicate artifact was created.
- Saved release commit `40ca4e31a08f3eefd45e112dc48520d78b166077`, tree `25e4f9749a920a0669ffb6d5eb84e41f92680bdb`, to the existing `codex/member-learning` branch using expected head `4514e36c418407f21ee9776f9b49e65bf0290240`, fast-forward only. No force update or merge to main occurred.
- Publication was not retried against the established WordPress reauthentication blocker. Release `0.14.0` is prepared on draft PR #21; the last verified live release remains `0.10.0`. No learner state changed.
- Editorial/rights-owner review and learner acceptance remain pending. This batch is complete in implementation.
- Last verified live release remains `0.10.0`. The established WordPress deployer reauthentication blocker remains, and callable Hostinger tools do not manage this WordPress deployment. Code rollback remains `rollback/learning-0.10.0`; credentials and learner state are untouched.

## Batch 10 — Finance path (0.15.0)

Scope and decisions:

- Rewrote `finance-basics` as “بداية العمل في المالية” and `finance-task` as “مهمتك الأولى: ملف عملية مالية”. They now use a fictional Suwayda printing request, a weak/clear comparison, a completed five-movement operation file, explicit funding/liquidity states, duplicate and missing-document handling, a low-connectivity summary, reviewer guidance and formative feedback.
- Preserved both IDs, prerequisites, tested outcomes, practical assignment, correct answer indexes and module version `2026-10-07.2`. Existing completions and review history remain current. Plugin release `0.15.0` records the editorial batch.
- Preparing, reviewing, authorizing, accepting, paying and reconciling remain separate. The exercise uses fictional units and documents only. It collects no supplier identity, invoice, contract, bank statement, payment details or real amount, and it does not execute a payment.

Sources re-read on 7 October 2026:

- `التخطيط الاستراتيجي | تدريب حياة`, modified 6 October 2026 at 18:04:46 UTC: financial fragility, short-term funding, concentrated decisions and limited specialist capacity remain institutional risks. It remains a working draft.
- `10- مسودة دليل الإجراءات المالية لحبق`, modified 13 December 2025 at 13:19:43 UTC: project owner, financial review, authorization, acceptance and payment are distinct steps; a complete operation file precedes payment. Approval thresholds, deadlines and several role assignments remain unfilled draft fields.
- `8- مسودة سياسة المنح والأموال المقيدة والتقارير للممولين لتجمع حبق`, modified 26 December 2025 at 15:34:23 UTC: restricted funds need project/budget coding, eligible purpose, evidence, no double funding and documented change approval. It remains a draft with unfilled funder-specific rules.
- `13- مسودة ملحق E: سياسة الأصول والمعدات في حبق`, modified 13 December 2025 at 12:38:15 UTC: purchase, approval, receipt and asset registration are distinct, and equipment needs traceable custody. It remains a draft and does not appoint an asset owner here.

Validation and delivery:

- 633 isolated behavioral checks passed. The 16 new Finance checks cover preserved versions, assignment and answer keys; the fictional local example; funding/liquidity distinctions; separation of evidence and approval; completed five-movement file; duplicate/missing handling; safe low-connectivity submission; reviewer guidance; formative feedback; and hidden raw answer keys.
- All 36 PHP files passed PHP 8.3 lint; training JavaScript syntax and `git diff --check` passed. Both generated HTML documents parsed successfully and contain the 0.15.0 Finance lesson titles and content. The existing 61-card review plan and 61-lesson curriculum were replaced in place using the expected Library version 8; both are now version 9. No duplicate artifact was created.
- Saved release commit `d99fb57fb00560a3d3f2921a41f61cdd0af307ac`, tree `8c8d9c51e0e9c6070869d5584f4008335266d5a6`, to the existing `codex/member-learning` branch using expected head `76b3699b7d4edc2cafed05e496d6782cb8e12982`, fast-forward only. No force update or merge to main occurred.
- Publication was not retried against the established WordPress reauthentication blocker. Release `0.15.0` is prepared on draft PR #21; the last verified live release remains `0.10.0`. No learner state changed.
- Financial/authority-owner review and learner acceptance remain pending. This batch is complete in implementation.
- Last verified live release remains `0.10.0`. The established WordPress deployer reauthentication blocker remains, and callable Hostinger tools do not manage this WordPress deployment. Code rollback remains `rollback/learning-0.10.0`; credentials and learner state are untouched.

## Batch 11 — People Operations path (0.16.0)

Scope and decisions:

- Rewrote `people-ops-basics` as “بداية العمل في دعم الأشخاص” and `people-ops-task` as “مهمتك الأولى: خطة انضمام”. They now use a fictional Suwayda newcomer, an unclear/clear start comparison, a completed two-week onboarding plan, low-connectivity alternatives, orderly handover, reviewer guidance and formative feedback.
- Preserved both IDs, prerequisites, tested outcomes, practical assignment, correct answer indexes and module version `2026-10-07.2`. Existing completions and review history remain current. Plugin release `0.16.0` records the editorial batch.
- Learning support, urgent risk, complaints/testimony, relationship and pay decisions, performance/discipline, account access and publishing authority remain separate. The exercise uses fictional people and public-text tasks only. It collects no real person record, contract, complaint, testimony, health/financial detail, credential or contact.

Sources re-read on 7 October 2026:

- `التخطيط الاستراتيجي | تدريب حياة`, modified 6 October 2026 at 18:04:46 UTC: concentrated knowledge and decisions, incomplete structures, overload and limited specialist capacity support a small delegated start with explicit review. It remains a working draft.
- `9- مسودة لائحة الموارد البشرية في تجمع حبق`, modified 13 December 2025 at 12:26:30 UTC: relationship, agreement, file, time, performance, complaint, data/access and exit are distinct. It remains a draft; actual people, conduct and safety contacts are unfilled and no review cadence is treated as adopted.
- `4- مسودة سياسة التطوع لتجمع حبق`, modified 23 December 2025 at 18:19:47 UTC: expectations, support, capacity, safe refusal and orderly handover matter, while volunteering must remain distinct from paid work. It remains a draft with unfilled coordinator and reporting channels.
- `منسق العمليات - التوصيف الوظيفي`, modified 6 January 2026 at 16:34:09 UTC: basic onboarding covers the correct folder/channel, brief, simple norms, minimum access and risk escalation. It is a working role document, not evidence of an appointment.

Validation and delivery:

- 650 isolated behavioral checks passed. The 17 new People Operations checks cover preserved versions, assignment and answer keys; the fictional local example; unclear/clear comparison; support/complaint separation; no invented relationship or adopted policy; low-connectivity practice; worked bounded plan; midpoint and reviewer guidance; safe seven-line submission; orderly exit; formative feedback; and hidden raw answer keys.
- All 36 PHP files passed PHP 8.3 lint; training JavaScript syntax and `git diff --check` passed. Both generated HTML documents parsed successfully and contain the 0.16.0 People Operations lesson titles and content. The existing 61-card review plan and 61-lesson curriculum were replaced in place using expected Library version 9; both are now version 10. No duplicate artifact was created.
- Saved release commit `8d817ae63993d4146409a59c26e4bd1f1d813e35`, tree `fce9e06bc4ec181bf0ca51dbc8f2d243cfe602de`, to the existing `codex/member-learning` branch using expected head `bba6270f36321985c59749013987f24d1151f01f`, fast-forward only. The following documentation checkpoint records this delivery state. No force update or merge to main occurred.
- Publication was not retried against the established WordPress reauthentication blocker. Release `0.16.0` is prepared on draft PR #21; the last verified live release remains `0.10.0`. No learner state changed.
- People/policy-owner review and learner acceptance remain pending. This batch is complete in implementation.
- Last verified live release remains `0.10.0`. The established WordPress deployer reauthentication blocker remains, and callable Hostinger tools do not manage this WordPress deployment. Code rollback remains `rollback/learning-0.10.0`; credentials and learner state are untouched.

## Batch 12 — Research and Memory path (0.17.0)

Scope and decisions:

- Rewrote `research-basics` as “بداية العمل في البحث والذاكرة” and `research-task` as “مهمتك الأولى: مذكرة بحث وذاكرة”. They now use a fictional Suwayda researcher, a broad/clear question comparison, four statement types, a completed one-page memo, low-connectivity alternatives, a handover index, reviewer guidance and formative feedback.
- Preserved both IDs, prerequisites, tested outcomes, practical assignment, correct answer indexes and module version `2026-10-07.2`. Existing completions and review history remain current. Plugin release `0.17.0` records the editorial batch.
- Learning completion, data-collection approval, source contact, archive access, publication, institutional finding and impact claim remain separate. The exercise uses fictional or public/deidentified sources only. It collects no testimony, participant record, private link, sensitive location or raw archive item.

Sources re-read on 8 October 2026:

- `التخطيط الاستراتيجي | تدريب حياة`, modified 6 October 2026 at 18:04:46 UTC: knowledge and decisions remain too concentrated, while Sweida's changes create a need for meaning, narrative and documentation. It remains a working draft.
- `برنامج حبق المتكامل — منحة حياة 2026–2027`, modified 18 September 2026 at 13:33:40 UTC: Think is proposed as a shared research/learning function without a new team or budget; indicators need review and audience reach alone does not prove trust or impact. It remains a proposal for implementation and internal adoption.
- `دليل اللغة والأسلوب لحبق ميديا`, modified 28 October 2025 at 19:41:07 UTC: harmful claims require evidence, uncertainty should be labelled and corrections should explain what changed. It remains a working document whose adoption must be confirmed.
- `15- مسودة سياسة حماية البيانات والخصوصية في حبق`, modified 13 December 2025 at 12:45:38 UTC: purpose limitation, data minimization, least access, informed consent and protected storage apply to sensitive sources and testimony. It remains a draft with unfilled roles.
- `📂 هيكلية المجلدات – Drive Structure`, modified 4 November 2025 at 08:39:30 UTC: the working structure includes Think research, background documents, data/assessments and archives, with clear names and separation of working/latest and archived originals. It is a working document, not proof that every folder exists or is approved.

Validation and delivery:

- 667 isolated behavioral checks passed. The 17 new Research checks cover preserved versions, assignment and answer keys; the fictional local example; broad/clear question; four statement types; safe source cards; evidence and access limits; worked memo; labelled fictional result and limitations; handover index; low-connectivity submission; reviewer guidance; separation from collection/publication/impact claims; formative feedback; and hidden raw answer keys.
- All 36 PHP files passed PHP 8.3 lint; training JavaScript syntax and `git diff --check` passed. Both generated HTML documents parsed successfully and contain the 0.17.0 Research lesson titles and content. The existing 61-card review plan and 61-lesson curriculum were replaced in place using expected Library version 10; both are now version 11. No duplicate artifact was created.
- Saved release commit `beb2c3e0687c3bf4f1a1869bc98ce70c909ecfd9`, tree `016e487ee849787d57b1c3d757377bd65e8b862c`, to the existing `codex/member-learning` branch using expected head `800032741507c70155638ae3d372d5d0631c87cf`, fast-forward only. The following documentation checkpoint records this delivery state. No force update or merge to main occurred.
- Publication was not retried against the established WordPress reauthentication blocker. Release `0.17.0` is prepared on draft PR #21; the last verified live release remains `0.10.0`. No learner state changed.
- Research/privacy-owner review and learner acceptance remain pending. This batch is complete in implementation.
- Last verified live release remains `0.10.0`. The established WordPress deployer reauthentication blocker remains, and callable Hostinger tools do not manage this WordPress deployment. Code rollback remains `rollback/learning-0.10.0`; credentials and learner state are untouched.

## Batch 13 — Technology path (0.18.0)

Scope and decisions:

- Rewrote `technology-basics` as “بداية العمل في التقنية” and `technology-task` as “مهمتك الأولى: بطاقة تغيير تقني”. They now use a fictional Suwayda contributor, a vague/clear request comparison, an approved test environment, a completed small-change card, five meaningful tests, minimum access, low-connectivity alternatives, rollback impact, reviewer guidance and formative feedback.
- Preserved both IDs, prerequisites, tested outcomes, practical assignment, correct answer indexes and module version `2026-10-07.2`. Existing completions and review history remain current. Plugin release `0.18.0` records the editorial batch.
- Learning completion, technical review, security or incident review, account access, code merge, purchasing, release approval and production deployment remain separate. The exercise uses fictional or deidentified test data only. It collects no password, verification code, key, private link, database copy, production data or user record.

Sources re-read on 8 October 2026:

- `التخطيط الاستراتيجي | تدريب حياة`, modified 6 October 2026 at 18:04:46 UTC: technology can reduce production and distribution costs, while broad scope, concentrated knowledge and limited specialist capacity require clear priorities. It remains a working draft.
- `برنامج حبق المتكامل — منحة حياة 2026–2027`, modified 18 September 2026 at 13:33:40 UTC: Geeks is proposed as a shared support function that should operate and document the existing stack, keep sensitive data in restricted spaces, preserve portable records, separate backups and test recovery. It remains a proposal for implementation and internal adoption.
- `18- مسودة سياسة الأمن الرقمي والسلامة الميدانية في حبق`, modified 13 December 2025 at 12:49:31 UTC: individual accounts, strong unique passwords, two-factor authentication, protected storage, no secrets in unapproved AI tools and immediate containment are proposed minimum practices. It remains a draft with unfilled responsible roles.
- `15- مسودة سياسة حماية البيانات والخصوصية في حبق`, modified 13 December 2025 at 12:45:38 UTC: purpose limitation, data minimization, least access, protected storage and incident containment apply to personal and sensitive data. It remains a draft with unfilled roles.
- `منسق العمليات - التوصيف الوظيفي`, modified 6 January 2026 at 16:34:09 UTC: operations and Geeks should keep the basic stack organized, document new tools, control access and backups, and distinguish who may view from who may publish. It is a working role document, not evidence of an appointment.

Validation and delivery:

- 686 isolated behavioral checks passed. The 19 new Technology checks cover preserved versions, assignment and answer keys, the fictional local example, test-environment scope, least access, credential and AI-tool boundaries, safe test data, cache isolation, backup limits, low-connectivity practice, rollback, a worked card, five tests, concurrent-work protection, safe seven-line submission, reviewer guidance, incident separation, formative feedback and hidden raw answer keys.
- All 36 PHP files passed PHP 8.3 lint; training JavaScript syntax, generated-document parsing and `git diff --check` passed. The existing 61-card review plan and 61-lesson curriculum were replaced in place using expected Library version 11, both now version 12. No duplicate artifact was created.
- Saved release commit `87c9df8ec862a6b76355387905140206c17300da`, tree `d6a3fe47a47f407656a9d560cfca065ff48093ae`, to the existing `codex/member-learning` branch using expected head `569366cbec28ce21859134412baac15b5d7899a5`, fast-forward only. The following documentation checkpoint records this delivery state. No force update or merge to main occurred.
- Publication was not retried against the established WordPress reauthentication blocker. Release `0.18.0` is prepared on draft PR #21; the last verified live release remains `0.10.0`. No learner state changed.
- Technical/security-owner review and learner acceptance remain pending. This batch is complete in implementation.
- Last verified live release remains `0.10.0`. The established WordPress deployer reauthentication blocker remains, and callable Hostinger tools do not manage this WordPress deployment. Code rollback remains `rollback/learning-0.10.0`; credentials and learner state are untouched.

## Batch 14 — Leadership, governance and partnerships path (0.19.0)

Scope and decisions:

- Rewrote `leadership-basics` as “بداية العمل في القيادة” and `leadership-task` as “مهمتك الأولى: مذكرة قرار”. They now use a fictional Suwayda contributor, a quick/clear decision comparison, separate preparation, review, decision and delegation, a completed small-pilot memo, conflict disclosure and recusal, low-connectivity alternatives, an expansion gate, reviewer guidance and formative feedback.
- Preserved both IDs, prerequisites, tested outcomes, practical assignment, correct answer indexes and module version `2026-10-07.2`. Existing completions and review history remain current. Plugin release `0.19.0` records the editorial batch.
- Learning completion, domain review, due diligence, policy or legal adoption, representation, partnership approval, signature, spending and publication remain separate. The exercise uses fictional partners, people and resources only. It collects no real offer, partner data, amount, signature, private minutes or personal conflict detail.

Sources re-read on 8 October 2026:

- `التخطيط الاستراتيجي | تدريب حياة`, modified 6 October 2026 at 18:04:46 UTC: concentrated decisions, incomplete structures, limited specialist capacity and broad scope require priorities, delegation and transparent partnerships that preserve identity. It remains a working draft.
- `برنامج حبق المتكامل — منحة حياة 2026–2027`, modified 18 September 2026 at 13:33:40 UTC: new activities start as small tests with an owner, covered time and cost, evidence and a pre-agreed continue, change or stop rule. Decisions within budgets and editorial authority remain distinct. It remains a proposal for implementation and internal adoption.
- `مسودة النظام الداخلي لتجمع حبق`, modified 7 December 2025 at 10:00:43 UTC: the file contains alternative association and company structures, proposed bodies and authority routes, and says effectiveness depends on approval and competent registration. It remains a draft and is not proof of a current board, legal form or signing authority.
- `14- مسودة ملحق F سياسة الشراكات`, modified 28 December 2025 at 13:07:20 UTC: partnership screening should cover value, risk, due diligence, editorial independence, conflict disclosure, a partnership owner, written terms and termination conditions. It remains a draft and does not appoint an approver.
- `9- مسودة لائحة الموارد البشرية في تجمع حبق`, modified 13 December 2025 at 12:26:30 UTC: potential interests require disclosure, and editorial or financial roles may require recusal. Its people, conduct and safety roles remain unfilled draft fields.

Validation and delivery:

- 708 isolated behavioral checks passed. The 22 new Leadership checks cover preserved versions, assignment and answer keys, the fictional local example, bounded commitments, four decision steps, conflict disclosure and recusal, no invented governance, low-connectivity practice, a worked memo, honest resources, independence and authority limits, a conditional expansion gate, explicit decision options, safe eight-line submission, reviewer guidance, due-diligence and impact limits, formative feedback and hidden raw answer keys.
- All 36 PHP files passed PHP 8.3 lint; training JavaScript syntax, generated-document parsing and `git diff --check` passed. The existing 61-card review plan and 61-lesson curriculum were replaced in place using expected Library version 12, both now version 13. No duplicate artifact was created.
- Saved release commit `4d612f6b5b5aa4d6c9252745ab62d8ade129f058`, tree `dc287569b147332048149716e4dab5877124e486`, to the existing `codex/member-learning` branch using expected head `09871d66632c21a82f85d3dae53e699afea33af0`, fast-forward only. The following documentation checkpoint records this delivery state. No force update or merge to main occurred.
- Publication was not retried against the established WordPress reauthentication blocker. Release `0.19.0` is prepared on draft PR #21; the last verified live release remains `0.10.0`. No learner state changed.
- Governance/partnership-owner review and learner acceptance remain pending. This batch is complete in implementation.
- Last verified live release remains `0.10.0`. The established WordPress deployer reauthentication blocker remains, and callable Hostinger tools do not manage this WordPress deployment. Code rollback remains `rollback/learning-0.10.0`; credentials and learner state are untouched.

## Remaining approved work

1. Verify the interface on a real narrow viewport, keyboard and larger browser text; print/save a work card. This run has no documented browser viewport-emulation control, so do not claim real mobile acceptance from CSS or isolated tests alone.
2. All ten two-lesson onboarding paths are complete in implementation. Domain-owner and learner review remain human acceptance steps, not completed claims.
3. Review all 36 specialist courses against `docs/learning-design-and-review.md` in small batches, with current sources and explicit draft/domain review state. Actual first-task relevance chooses the next batch; otherwise WordPress use and digital security are next because they support the site and account boundaries used across early tasks.
4. Finish content-maintenance register, reviewer/support setup guidance and concise pilot checklist. Update existing Library artifact identities only when their content actually changes.

## Decisions and human acceptance still needed

- Confirm adopted policy/source versions; assign actual coordinator, reviewer, support and safe reporting/alternate contacts. No appointments or contacts have been invented.
- Choose the pilot members, their real first assignments and the next specialist priorities. A domain owner and learners must review actual use; no completed pilot reviews are claimed.
- Verify hosting protection for confidential existing uploads and actual data-backup/restore operations separately. Code rollback does not preserve database records.
- Agree retention and photo rights/credit decisions as needed before wider rollout. Existing media-library photography is used without inferring photographer or location.

Resume rule: read this log, AGENTS.md, relevant code and current source documents; re-read the actual branch head and recent commits. Preserve others’ changes, use an expected-head fast-forward, stay on this draft PR, and stop expanding when the approved implementable backlog is complete.
