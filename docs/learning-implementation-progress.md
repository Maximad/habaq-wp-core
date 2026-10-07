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
- Authenticated WordPress Deployer for Git was observed for this run, configured to the existing repository and branch. Deployment is not yet claimed; publish and live evidence will be appended after verification.

Commit/deployment: pending release commit and live checks. Before publishing, preserve 0.6.1 as a code rollback branch; this is not a database backup.

## Remaining approved work

1. Verify the interface on a real narrow viewport, keyboard and larger browser text; print/save a work card. This run has no documented browser viewport-emulation control, so do not claim real mobile acceptance from CSS or isolated tests alone.
2. Next editorial batch: welcome and roles, followed by conduct, data and workflow, using fresh relevant Drive sources, ordinary fictional local examples, short exercises, explanatory feedback and work cards. Keep IDs/versions when tested meaning is unchanged; deliberately version material requirements.
3. Improve the two lessons in each existing path in small batches: introductions, bounded first assignments, worked examples and reviewer guidance. Actual first-task relevance chooses specialist priorities when available; otherwise start with broadly useful first-task courses.
4. Review all 36 specialist courses against `docs/learning-design-and-review.md` in small batches, with current sources and explicit draft/domain review state.
5. Finish content-maintenance register, reviewer/support setup guidance and concise pilot checklist. Update existing Library artifact identities only when their content actually changes.

## Decisions and human acceptance still needed

- Confirm adopted policy/source versions; assign actual coordinator, reviewer, support and safe reporting/alternate contacts. No appointments or contacts have been invented.
- Choose the pilot members, their real first assignments and the next specialist priorities. A domain owner and learners must review actual use; no completed pilot reviews are claimed.
- Verify hosting protection for confidential existing uploads and actual data-backup/restore operations separately. Code rollback does not preserve database records.
- Agree retention and photo rights/credit decisions as needed before wider rollout. Existing media-library photography is used without inferring photographer or location.

Resume rule: read this log, AGENTS.md, relevant code and current source documents; re-read the actual branch head and recent commits. Preserve others’ changes, use an expected-head fast-forward, stay on this draft PR, and stop expanding when the approved implementable backlog is complete.
