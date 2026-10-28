# Result Workflow Rebuild — Changelog & Migration Guide

## Reality check first
This was not a small patch. The original app had **no faculty-side result entry
at all** — marks were entered by admin as a single flat number per subject,
with no components, no approval workflow, no CGPA table, and the entire data
layer used string-concatenated SQL. Everything below was built from that
starting point.

I could not run a PHP interpreter in the sandbox this was built in (the
Ubuntu package mirror was returning 404s on the php8.3 packages — not a
permissions issue, the repo itself was broken). Every file has been read
back and reasoned through carefully, but **none of it has been executed
against a live PHP + MySQL stack**. Treat this as a strong first pass that
needs a real test run on your machine before it touches production data.

---

## 1. Database migration

Run **after** your existing `uni.sql` is imported:

```
DATABASE FILE/migration_result_workflow.sql
```

What it does:
- Renames the old `result` table to `result_legacy` (your historical data
  is preserved, not deleted — it just isn't wired into any page anymore
  because its schema, one flat mark per subject, is incompatible with the
  new component-based one).
- Creates `subjects` (name + credit_hours), seeded with your original 9
  subjects and the same credit hours that were previously hard-coded in
  four different PHP files.
- Creates the new `result` table: attendance / assignments / quizzes /
  project / midterm / final_exam, computed internal_total / total_marks /
  percentage / letter_grade / grade_point, a `status` column
  (draft / submitted / returned / approved), `remarks` for return reasons,
  and a unique constraint on (student, subject, semester) so duplicates
  can't happen.

**Before you run this in production**: back up your database. `RENAME
TABLE` is safe but this is still a schema change on live data.

## 2. Grading policy actually implemented

Per what you confirmed:

| % range | Letter | GPA |
|---|---|---|
| 90–100 | A | 4.0 |
| 85–89.99 | A- | 3.7 |
| 80–84.99 | B+ | 3.3 |
| 75–79.99 | B | 3.0 |
| 70–74.99 | B- | 2.7 |
| 65–69.99 | C+ | 2.3 |
| 60–64.99 | C | 2.0 |
| 55–59.99 | C- | 1.7 |
| 50–54.99 | D | 1.0 |
| < 50 | F | 0.0 |

CGPA is credit-hour weighted: `sum(grade_point * credit_hours) / sum(credit_hours)`
across a student's **approved** results only. This lives in
`get_student_cgpa()` in `php/functions.php` and in `grade_from_percentage()`
right next to it — if the university's real policy differs from this
standard scale, that's the one function to edit; everything else reads
from it.

The same table is duplicated in JS in `fct_result_form.php` for the live
preview, with a comment pointing back to the PHP version so they don't
drift silently.

## 3. What changed, file by file

**New files:**
- `fct_results.php` — faculty dashboard: pick student/subject/semester,
  list of the teacher's own entries with status badges.
- `fct_result_form.php` — the actual entry form. Live-recalculates
  internal total / total / percentage / grade / GPA as you type. "Save as
  Draft" always works while editable; "Submit to Admin" is disabled until
  a draft exists (per your spec: draft first, then submit). Once submitted
  or approved, the form renders read-only — server-side, not just hidden
  in JS.
- `admin_results.php` — the review queue. Filter by status. Approve or
  Return (return requires a reason, stored in `remarks` and shown back to
  the teacher). No input field for marks exists on this page — admin
  physically cannot edit a mark from here.
- `DATABASE FILE/migration_result_workflow.sql`

**Rewritten:**
- `view_single_result.php`, `view_single_cgpa.php` (student-facing) — now
  read from the logged-in student's session directly instead of an
  admin-passthrough URL parameter that student pages never actually used
  correctly before. Show approved-only results with full component
  breakdown.
- `view_result.php`, `view_cgpa.php` (admin-facing) — read-only views of a
  student's results across all statuses, new schema. No edit controls.
- `st_result.php` — dropped the "Add Result" column/link; admin no longer
  has a path into marks entry.
- `header.php` — sidebar is now a real accordion (vanilla JS, no jQuery
  added). Clicking "Admin Login" / "Faculty Login" / "Student Login"
  expands that section's links directly beneath it and collapses the
  others, instead of a static list that always showed everything.
- `php/functions.php` — added the whole result-workflow backend
  (~230 new lines: subjects, grade calc, draft/submit/approve/return,
  CGPA). Parameterized `st_userlogin`, `fct_login`, and `admin_userlogin`
  with prepared statements (they gate every page in this feature, so they
  were in scope for the injection fix you approved). The old
  `add_marks` / `show_marks` / `update_result` / `view_cgpa` functions
  were repointed at `result_legacy` rather than deleted, so your historical
  data stays queryable if you ever need it, but nothing in the new UI
  calls them.

**Deleted:**
- `add_result.php` — this was admin's direct marks-entry page. Removed
  outright rather than "disabled," because admin isn't supposed to have
  this capability anymore per your spec.
- `st_result_update.php` — found late: an *unlinked* admin page that let
  admin bulk-edit marks directly by guessing the URL. Nothing pointed to
  it, but it existed and directly contradicted "admin cannot edit marks
  directly," so it's gone rather than left as a silent bypass.

**Bug fixed along the way (not asked for, but blocked the feature):**
Two faculty pages — `fct_single_profile.php` and `class_att_fc.php` — were
including the *student* header (`headertop.php`) instead of the faculty
one, and the faculty header itself was checking the student's session
variable. Net effect: faculty had no working nav menu at all before this.
Fixed both the includes and the session check, and added a "Results" link
to the faculty nav and a "Review Results" link to the admin dashboard.

## 4. Security note — what I fixed vs. what's still open

Fixed (scope you approved: files touched for this feature):
- `st_userlogin`, `fct_login`, `admin_userlogin` — now parameterized.
- Every new result-workflow query — parameterized from the start.

**Not fixed, still open:** `st_registration`, `search`, `delete_student`,
`updateprofile`, `updatePassword`, `fct_registration`, the whole attendance
subsystem, and others in `functions.php` still build SQL by string
concatenation. I didn't touch these because they're outside this feature,
but I'm not going to pretend they're fine — they're not. If this app is
going anywhere near a real network, that needs a dedicated pass, not a
side effect of a results feature. Also: passwords are hashed with MD5 on
the login forms (`index.php`, etc.), which is not a secure hashing
algorithm by modern standards (no salt, fast to brute-force). I left this
alone because migrating it would invalidate every existing account's
password and that's a decision for you to make deliberately, not something
I should do as a drive-by.

## 5. Testing checklist before you trust this

1. Import `uni.sql`, then run the migration SQL.
2. Log in as faculty (`robinson` / `123`), go to Results, pick a student +
   subject + semester, enter marks, confirm the live totals match your
   expectations, Save as Draft.
3. Confirm the Submit button was disabled before the draft existed and is
   enabled after.
4. Submit. Confirm the entry is no longer editable by the teacher.
5. Log in as admin (`admin` / `123`), go to Review Results, confirm it
   shows up under "Pending Review."
6. Try Return with a reason — confirm it shows up back on the teacher's
   list as "Returned" with your reason visible, and is editable again.
7. Approve a different one — confirm it disappears from admin's edit
   surface (there isn't one) and appears on the student's result page only
   after approval, not before.
8. Log in as the student it belongs to and confirm the CGPA page computes
   correctly against a manually-checked example.
