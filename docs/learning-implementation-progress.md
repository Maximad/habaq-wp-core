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
- Regenerated and validated the 61-card review plan and 61-lesson curriculum. Replaced the same Library artifact identities with expected-version safeguards: review plan `libfile_0b83affecccc8191bfe9e24644ab0345` and curriculum `libfile_17af04fe58748191b961a4e5480eaed6`, both now at Library version 4. No duplicate curriculum or review file was created. Guarded commit, publication and live checks remain pending at this checkpoint and must not be claimed until recorded below.
- Domain-owner and learner acceptance are not claimed.

## Remaining approved work

1. Verify the interface on a real narrow viewport, keyboard and larger browser text; print/save a work card. This run has no documented browser viewport-emulation control, so do not claim real mobile acceptance from CSS or isolated tests alone.
2. Continue improving the remaining two-lesson paths in small batches: introductions, bounded first assignments, worked examples and reviewer guidance. Operations and coordination is complete in implementation. Actual first-task relevance chooses the next path when available; otherwise continue with a broadly useful path.
3. Review all 36 specialist courses against `docs/learning-design-and-review.md` in small batches, with current sources and explicit draft/domain review state.
4. Finish content-maintenance register, reviewer/support setup guidance and concise pilot checklist. Update existing Library artifact identities only when their content actually changes.

## Decisions and human acceptance still needed

- Confirm adopted policy/source versions; assign actual coordinator, reviewer, support and safe reporting/alternate contacts. No appointments or contacts have been invented.
- Choose the pilot members, their real first assignments and the next specialist priorities. A domain owner and learners must review actual use; no completed pilot reviews are claimed.
- Verify hosting protection for confidential existing uploads and actual data-backup/restore operations separately. Code rollback does not preserve database records.
- Agree retention and photo rights/credit decisions as needed before wider rollout. Existing media-library photography is used without inferring photographer or location.

Resume rule: read this log, AGENTS.md, relevant code and current source documents; re-read the actual branch head and recent commits. Preserve others’ changes, use an expected-head fast-forward, stay on this draft PR, and stop expanding when the approved implementable backlog is complete.
