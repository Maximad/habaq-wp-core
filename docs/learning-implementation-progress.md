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
- Domain-owner and learner acceptance are not claimed. Live publication and checks are pending the guarded release commit.

Commit/deployment: pending. Preserve 0.6.2 as a rollback branch before publication. Update this section with commit, deployment and live evidence.

## Remaining approved work

1. Verify the interface on a real narrow viewport, keyboard and larger browser text; print/save a work card. This run has no documented browser viewport-emulation control, so do not claim real mobile acceptance from CSS or isolated tests alone.
2. Continue the shared editorial work with conduct and data, followed by workflow, using fresh relevant Drive sources, ordinary fictional local examples, short exercises, explanatory feedback and work cards. Keep IDs/versions when tested meaning is unchanged; deliberately version material requirements.
3. Improve the two lessons in each existing path in small batches: introductions, bounded first assignments, worked examples and reviewer guidance. Actual first-task relevance chooses specialist priorities when available; otherwise start with broadly useful first-task courses.
4. Review all 36 specialist courses against `docs/learning-design-and-review.md` in small batches, with current sources and explicit draft/domain review state.
5. Finish content-maintenance register, reviewer/support setup guidance and concise pilot checklist. Update existing Library artifact identities only when their content actually changes.

## Decisions and human acceptance still needed

- Confirm adopted policy/source versions; assign actual coordinator, reviewer, support and safe reporting/alternate contacts. No appointments or contacts have been invented.
- Choose the pilot members, their real first assignments and the next specialist priorities. A domain owner and learners must review actual use; no completed pilot reviews are claimed.
- Verify hosting protection for confidential existing uploads and actual data-backup/restore operations separately. Code rollback does not preserve database records.
- Agree retention and photo rights/credit decisions as needed before wider rollout. Existing media-library photography is used without inferring photographer or location.

Resume rule: read this log, AGENTS.md, relevant code and current source documents; re-read the actual branch head and recent commits. Preserve others’ changes, use an expected-head fast-forward, stay on this draft PR, and stop expanding when the approved implementable backlog is complete.
