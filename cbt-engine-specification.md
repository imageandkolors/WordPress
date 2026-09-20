# Edutech v1.0 — CBT Engine Specification

## Executive requirement

Edutech v1.0 should support two distinct computer-based testing modes:

1. **Official school CBT examinations**, created and controlled by an authorized school administrator or examination officer.
2. **Practice CBT examinations**, available for student preparation and revision.

Official and practice attempts must be stored separately. An official attempt must become part of the student’s formal examination record and, after publication, contribute to the configured subject and overall grade. A practice attempt must not change the student’s official academic result unless an authorized user explicitly converts or imports it through a controlled workflow.

The system must generate a different question set for students sitting the same examination at the same time. It must also support question-level time limits and subject-level time limits. All timing and answer submission decisions must be enforced on the server, not trusted from the browser.

## Existing-plugin compatibility finding

The current plugin already has examination, exam-paper, admit-card, result, grading, and student-result structures. Its existing model is designed mainly for manually entered marks. The current exam result table stores marks and grades per student admit card and exam paper, but it does not provide a question bank, exam sessions, generated attempts, answer events, per-question timers, subject timers, or CBT result snapshots.

The CBT engine should therefore be implemented as a new bounded subsystem that links to the existing exam and student records. It should not overload the existing manual `exam_results` table with answer-level events.

## Operating modes

### Official school CBT

An authorized school user creates an official examination and configures its subjects, question pools, schedule, timing, scoring, eligibility, attempts, and publication rules. Eligible students receive or are assigned a secure examination code. The code alone must not be enough to impersonate another student; the system should also require the authenticated student account and, where appropriate, a student-specific access token or invigilator-issued PIN.

An official exam should have states:

```text
Draft → Scheduled → Open → In progress → Submitted/Auto-submitted → Marked → Moderated → Published → Archived
```

The exam must not be editable after the first official attempt starts, except through an audited correction or cancellation workflow.

### Practice CBT

Practice exams may be created by a school, teacher, or curriculum administrator, depending on permissions. A practice exam can use the same question bank but should have separate rules for attempts, review, hints, explanations, answer visibility, and retakes.

Practice mode should support:

- Immediate or delayed feedback.
- Review of answers and explanations.
- Unlimited or limited attempts.
- Random question selection.
- Random option order.
- Topic and difficulty filters.
- Time-limited and untimed practice.
- Progress history and mastery analytics.

Practice results should be clearly labelled **Practice** and must not appear as official school results.

## Question bank design

The question bank should support multiple institution types, subjects, curricula, examination bodies, and countries. Questions must be versioned so that an official exam remains reproducible even if the source question is later edited.

### Question bank entities

Recommended entities include:

| Entity | Purpose |
|---|---|
| Question bank | Container scoped to a school, institution, curriculum, or shared library |
| Question | Stable logical question record |
| Question version | Immutable wording, media, options, answer key, explanation, and scoring at a point in time |
| Question option | Answer choices with stable ordering and correctness metadata |
| Question tag | Topic, subtopic, curriculum outcome, examination body, difficulty, skill, and language |
| Subject pool | Question eligibility for a subject and syllabus version |
| Blueprint | Required counts by topic, difficulty, type, and marks |
| Exam snapshot | Immutable copy of selected question versions used in one official exam |
| Translation | Localized question and option text where supported |

A question should support at least:

- Single-choice multiple choice.
- Multiple-response choice.
- True/false.
- Short answer.
- Numeric answer.
- Matching.
- Ordering.
- Image-based questions.
- Audio or video questions where required.
- Essay or teacher-marked questions for future extension.

The first production release should prioritize objective question types that can be marked reliably. Subjective questions should be stored as pending manual marking rather than pretending they are automatically graded.

## Official exam configuration

Each official CBT exam should support:

- School and campus.
- Academic session, term, semester, or level.
- Education type: primary, secondary, tertiary, or custom.
- Examination body or internal school exam.
- Class, section, programme, or course eligibility.
- One or more subjects.
- Question count per subject.
- Marks per question.
- Negative marking rules.
- Difficulty distribution.
- Topic and syllabus blueprint.
- Subject duration.
- Per-question duration.
- Total examination duration.
- Start and end window.
- Number of attempts.
- Resume policy after disconnect.
- Navigation policy: free navigation or sequential only.
- Backtracking policy.
- Option randomization.
- Question randomization.
- Whether students can review answers.
- Whether results are immediate, delayed, or manually published.
- Invigilator and examination officer permissions.
- Official access code policy.
- Candidate eligibility and attendance tracking.
- Result weighting into the existing grading system.

## Unique question delivery

The requirement that students must not all receive the same questions requires a controlled pool and allocation algorithm. Merely shuffling the same questions is not enough if every student receives the same set.

### Recommended algorithm

For each official subject:

1. Define a question blueprint, such as 40 questions containing 10 mathematics, 10 English, 10 science, and 10 social studies questions, or a subject-specific blueprint.
2. Filter the eligible question versions by school, curriculum, subject, topic, difficulty, language, and status.
3. Confirm that the pool contains enough questions for the required number of candidates and the configured overlap policy.
4. Generate a cryptographically strong random seed for each candidate attempt using server-side randomness.
5. Select questions from each pool according to the blueprint.
6. Enforce a uniqueness policy across simultaneous candidates.
7. Randomize question order and option order using a deterministic derivation from the attempt seed.
8. Store the exact generated exam snapshot for that attempt.
9. Never regenerate the question set from a new random seed after the attempt has started.

### Uniqueness policy

The school should be able to select a policy:

- **No identical full paper:** no two candidates receive the exact same question set and order.
- **Limited overlap:** candidates may share a configured maximum percentage of questions.
- **Disjoint allocation:** each question is used only once until the available pool is exhausted.
- **Wave allocation:** candidates are divided into secure randomization waves when the pool is too small for fully disjoint papers.

The default official policy should be **no identical full paper with a configurable maximum overlap**. The system must block exam publication if the question pool is too small to satisfy the selected policy.

If the school has 100 candidates and wants 50 questions per candidate with no reuse, it needs at least 5,000 eligible question versions after blueprint filtering. If that quantity is unavailable, the system must report the shortage before the exam starts rather than silently giving students identical papers.

### What “unique” should mean

Question uniqueness should be defined at three levels:

1. **Question-set uniqueness:** students do not receive the same selected question IDs.
2. **Presentation uniqueness:** question order and answer-option order differ.
3. **Content uniqueness:** equivalent or near-duplicate questions are not repeatedly assigned to the same sitting.

The system cannot guarantee that students will never see related concepts unless the question bank includes enough tagged variants. Therefore, the blueprint and question-pool validation must identify duplicate or equivalent variants where possible.

## Timing model

### Server-authoritative time

The browser may display a countdown, but the server must calculate the true deadline using server timestamps. The client must never be allowed to extend an attempt by changing the system clock, browser JavaScript, or request payload.

Each attempt should store:

```text
started_at_server
scheduled_deadline_at
subject_started_at
subject_deadline_at
current_question_started_at
current_question_deadline_at
last_heartbeat_at
submitted_at
submission_reason
```

### Subject timer

A subject can have its own duration. When a subject deadline is reached:

- The server closes that subject.
- Unsaved answers already acknowledged by the server are retained.
- The student cannot return to that subject unless the exam policy explicitly allows review.
- The next subject becomes available if the exam uses sequential subject delivery.

### Per-question timer

A question can have a configured time limit. When the question deadline is reached:

- The server marks the question as timed out.
- The current answer is accepted only if it was received before the deadline or within a narrowly defined network grace period.
- The system advances to the next question according to the navigation policy.
- The timer cannot be reset by refreshing the page.

The recommended default is to record a small server-defined submission grace period for network latency, not a client-defined extension. The grace period must be visible in the audit log.

### Total exam timer

The total exam deadline is the earliest applicable deadline among:

- Official exam closing time.
- Candidate attempt deadline.
- Subject deadline.
- Question deadline.
- Maximum total duration.

The server must auto-submit when the effective deadline is reached. Auto-submission must be idempotent so that repeated browser requests cannot create duplicate results.

## Official access code design

The official access code should be a secure exam-entry mechanism, not a shared password that grants unrestricted access.

Recommended model:

- A school administrator creates or rotates an exam code.
- The code is displayed only to authorized invigilators or distributed through a controlled process.
- The student must already be authenticated in the frontend.
- The student enters the code and the server checks eligibility.
- The server creates a candidate-specific attempt token.
- The code is rate-limited and expires according to the exam window.
- The code is stored hashed, not as plaintext.
- The code can be single-use per candidate or reusable only within a controlled session policy.
- The system logs failed code attempts, successful entry, device/session metadata, and invigilator overrides.

For higher-stakes exams, add a second factor such as a candidate PIN, invigilator approval, seat number, or one-time candidate token. A shared exam code by itself cannot reliably prove candidate identity.

## Student answer and result records

Every official CBT attempt must be linked directly to the student’s existing student record, school, academic session, exam, subject, and generated paper snapshot.

Recommended records include:

### `cbt_exams`

Stores official or practice exam configuration and lifecycle state.

### `cbt_exam_subjects`

Stores subject-specific blueprint, order, duration, question count, marking, and grade-weight configuration.

### `cbt_question_banks`

Stores ownership, scope, curriculum, language, and status.

### `cbt_questions`

Stores logical questions and metadata.

### `cbt_question_versions`

Stores immutable question content and answer keys.

### `cbt_question_options`

Stores immutable options and correctness metadata.

### `cbt_exam_snapshots`

Stores the exact question versions and configuration frozen for an exam or candidate attempt.

### `cbt_attempts`

Stores student, exam, mode, code entry, status, timestamps, seed hash, score status, and submission reason.

### `cbt_attempt_subjects`

Stores each subject attempt, its timer, status, score, marks, and grading result.

### `cbt_attempt_questions`

Stores the exact question sequence, question version, order, marks, timing, and state for one attempt.

### `cbt_answers`

Stores the student’s answer, answer hash or normalized answer, saved time, final time, correctness, marks, and marking status.

### `cbt_events`

Stores append-only events such as login, code entry, question delivery, answer save, focus loss, reconnect, timeout, submission, and invigilator action.

### `cbt_official_results`

Stores the finalized subject and overall result snapshot that links into the existing exam result and grading system.

The existing `wlsm_exam_results` table should remain the compatibility layer for published subject-level results. A CBT finalization service should calculate the result and write the final official marks and grade to that existing structure only after the attempt is valid, marked, and approved.

## Grading and academic record integration

The CBT engine should not calculate only a raw percentage. It should pass the result through the configurable grading engine described in the frontend-first assessment.

For each subject, store:

- Raw marks.
- Maximum marks.
- Percentage.
- Grade.
- Grade point.
- Pass/fail status.
- Negative marks if applicable.
- Manual moderation adjustment if any.
- Assessment component weight.
- Curriculum and grading-scale version.

For official exams, the final result must retain the exact grading-scale version used at the time. If the school later changes its grading rules, historical CBT results must not change.

## Practice mode data policy

Practice results should belong to the student’s account and can be used for analytics, but they must be visibly separate from official results.

A practice attempt should include:

- Practice exam ID.
- Student record ID.
- Topic and curriculum tags.
- Attempt number.
- Questions seen.
- Answers and explanations.
- Score and mastery indicators.
- Time spent.
- Recommended revision areas.

Practice mode should not create a published `wlsm_exam_results` record unless a deliberate, authorized conversion process is used.

## Integrity and anti-cheating controls

No web application can guarantee that students cannot communicate or use another device. The system can, however, make collusion harder and produce auditable evidence.

Recommended controls include:

- Candidate-specific randomized question sets.
- Randomized option order.
- Server-authoritative timers.
- Single active attempt per candidate.
- Session and device binding with a controlled reconnect policy.
- Full-screen exam mode as a user-experience aid, not a security boundary.
- Focus and visibility-change events.
- Copy, paste, print, and context-menu deterrence where appropriate, without relying on them for security.
- Watermarking with candidate name or ID.
- Rate-limited API requests.
- No answer keys sent to the browser.
- No future questions sent before they are needed if the exam has a high-security requirement.
- Secure finalization and append-only event log.
- Invigilator dashboard showing active candidates, last heartbeat, disconnects, timeouts, and suspicious events.
- Optional browser lockdown or approved secure testing application for high-stakes examinations.
- Post-exam analytics for identical answer patterns, timing anomalies, and unusual similarity.

The question bank’s correct answers and explanations must never be included in the initial page payload. They should remain server-side and be used only for marking.

## Frontend CBT screens

### Administrator or examination officer

- Question-bank dashboard.
- Question authoring and bulk import.
- Question review and approval.
- Curriculum and topic tagging.
- Exam blueprint builder.
- Pool-capacity and uniqueness validator.
- Official exam setup.
- Candidate eligibility and code management.
- Live invigilation dashboard.
- Attempt monitoring.
- Result moderation and publication.
- Audit and integrity reports.

### Teacher

- Create practice exams if permitted.
- Contribute or review questions if permitted.
- Assign practice exams to classes.
- View practice analytics.
- Mark approved subjective questions.
- View official results only according to permission.

### Student

- Enter official exam code.
- View exam instructions and policy.
- Complete identity and readiness checks.
- Take subject and question timers.
- Save answers automatically.
- Navigate according to exam rules.
- Submit or be auto-submitted.
- View practice feedback.
- View official result only after publication.

### Parent

- View published official results for linked children.
- View practice summaries only if the school enables parent visibility.
- Never start or modify a child’s attempt.

## Failure and recovery requirements

The exam must remain usable when a student experiences a short network interruption.

The recommended behavior is:

- Save each answer immediately or at short intervals.
- Store the last server acknowledgement.
- Show connection status clearly.
- Allow reconnect only within the configured resume window.
- Continue the server timer during disconnection unless the exam policy explicitly supports a paused state.
- Never give extra time merely because the browser was offline.
- Submit safely if the deadline passes during disconnection.
- Prevent duplicate answer events and duplicate result finalization.
- Provide an invigilator override with an audited reason for exceptional cases.

## Required validations before an official exam starts

The system must block publication or opening when:

- The question pool is too small for the requested uniqueness policy.
- A subject has fewer valid questions than its blueprint requires.
- Questions have missing answer keys or invalid options.
- The grading configuration is incomplete.
- The exam has no eligible candidates.
- Subject times exceed the total exam window.
- The schedule conflicts with another locked official exam if conflict prevention is enabled.
- The selected curriculum or syllabus version is inactive.
- The code policy is incomplete.
- The official result mapping is not configured.

## Implementation phases

### Phase 1 — CBT foundation

Create the CBT tables, immutable question versions, question bank permissions, official/practice mode flag, exam lifecycle, and server-side attempt service. Do not begin with a browser-only quiz page.

### Phase 2 — question bank and practice mode

Build question authoring, tagging, import, review, practice exams, randomized delivery, answer saving, immediate feedback, and student analytics.

### Phase 3 — official exam delivery

Add candidate eligibility, official code entry, candidate-specific snapshots, subject and question timers, autosave, reconnect, auto-submit, and audit events.

### Phase 4 — marking and academic integration

Add automatic marking, manual marking placeholders for subjective items, moderation, grading-scale snapshots, and final publication into student examination records and the existing result display.

### Phase 5 — invigilation and integrity

Add live monitoring, event review, suspicious-pattern analytics, exports, invigilator controls, and optional secure-browser integrations.

## Acceptance criteria

The CBT release is ready only when all of the following are true:

1. Official and practice attempts are stored separately.
2. Every official attempt is linked to the correct student record, school, session, exam, subject, and paper snapshot.
3. Students cannot start an official exam without eligibility and a valid code or authorized token.
4. The code is hashed, rate-limited, expiry-controlled, and audited.
5. No two candidates receive the same full question set when the configured pool capacity supports the uniqueness rule.
6. The system blocks an exam whose pool is too small for its uniqueness policy.
7. Question order and option order are independently randomized.
8. The browser never supplies the authoritative deadline or correct answer.
9. Subject and per-question time limits are enforced by the server.
10. Refreshing, reopening, or changing the browser clock cannot reset a timer.
11. Answers are saved and recoverable after short network interruptions.
12. Auto-submit is safe and idempotent.
13. Results are calculated with the correct grading-scale and curriculum versions.
14. Official results flow into the student’s formal result record only after finalization and publication.
15. Practice results never silently modify official results.
16. Parents can see only published results belonging to their linked children.
17. Teachers and examination officers can view only permitted question banks, exams, candidates, and results.
18. Invigilators can monitor active attempts and review audit events.
19. All sensitive operations are logged.
20. The system has automated tests for uniqueness, timing, authorization, scoring, resumption, and result finalization.

## Final recommendation

Yes, this CBT requirement fits the frontend-first education platform. It should be treated as a dedicated examination subsystem with its own question bank, attempt engine, timer service, audit log, and result finalization workflow.

The most important implementation rule is this:

> **The browser displays the exam, but the server owns the exam state.**

Question allocation, candidate eligibility, timers, answer acceptance, scoring, official result creation, and publication must all be decided server-side. This is the difference between a reliable school examination system and a client-side quiz that can be manipulated.

## References

[1]: file:///home/ubuntu/work/school-management-analysis/project/school-management-pro/admin/inc/WLSM_Database.php "Current exam paper, admit card, and exam result schema"
[2]: file:///home/ubuntu/work/school-management-analysis/project/school-management-pro/admin/inc/school/staff/examination/WLSM_Staff_Examination.php "Current examination management and result logic"
[3]: file:///home/ubuntu/work/school-management-analysis/project/school-management-pro/admin/inc/school/staff/examination/exams/save.php "Current examination configuration form and paper fields"
[4]: file:///home/ubuntu/work/school-management-analysis/project/school-management-pro/admin/inc/school/staff/examination/results/save_results.php "Current manual examination result entry workflow"
[5]: file:///home/ubuntu/work/school-management-analysis/project/school-management-pro/public/api/WLSM_Api.php "Current student examination result API handlers"
