<?php
session_start();
	require "php/config.php";
	require_once "php/functions.php";
	$user = new login_registration_class();
	$fid = $_SESSION['f_id'];
	$funame = $_SESSION['f_uname'];
	$fname = $_SESSION['f_name'];
	if(!$user->get_faculty_session()){
		header('Location: facultylogin.php');
		exit();
	}

	$errors = array();
	$notice = "";

	$subject_id = isset($_GET['sub']) ? (int)$_GET['sub'] : (isset($_POST['subject_id']) ? (int)$_POST['subject_id'] : 0);
	$semester   = isset($_GET['sem']) ? $_GET['sem'] : (isset($_POST['semester']) ? $_POST['semester'] : '');

	$step = ($subject_id && $semester) ? 'grid' : 'pick';

	$subjectRow = null;
	if($step == 'grid'){
		$subjectRow = $user->get_subject_by_id($subject_id);
		if(!$subjectRow){
			$step = 'pick';
			$errors[] = "That subject could not be found.";
		}
	}

	$saved_count = 0;
	$submitted_count = 0;
	$skipped_count = 0;

	if($step == 'grid' && $_SERVER['REQUEST_METHOD'] == 'POST'){
		$action = isset($_POST['action']) ? $_POST['action'] : 'draft';
		$rows = isset($_POST['rows']) ? $_POST['rows'] : array();

		foreach($rows as $st_id => $r){
			$st_id = (int)$st_id;

			// Skip rows the teacher left completely untouched -- we don't
			// want to create a "0 across the board" draft for every student
			// just because they were listed on the grid.
			$touched = false;
			foreach(array('attendance','assignments','quizzes','project','midterm','final_exam') as $f){
				if(isset($r[$f]) && $r[$f] !== ''){ $touched = true; break; }
			}
			if(!$touched){ continue; }

			$existing = $user->find_result($st_id, $subject_id, $semester);
			$result_id = $existing ? $existing['id'] : null;

			// A row already submitted/approved is locked -- don't silently
			// eat the teacher's edits, tell them it didn't apply.
			if($existing && $existing['status'] !== 'draft' && $existing['status'] !== 'returned'){
				$skipped_count++;
				continue;
			}

			$get = function($f) use ($r){ return isset($r[$f]) && $r[$f] !== '' ? $r[$f] : 0; };

			$saved_id = $user->save_result_draft(
				$result_id, $st_id, $subject_id, $semester, $fid,
				$get('attendance'), $get('assignments'), $get('quizzes'),
				$get('project'), $get('midterm'), $get('final_exam')
			);

			if($saved_id){
				$saved_count++;
				if($action == 'submit_all'){
					if($user->submit_result($saved_id, $fid)){
						$submitted_count++;
					}
				}
			} else {
				$skipped_count++;
			}
		}

		if($saved_count > 0){
			$notice = "Saved {$saved_count} student result(s).";
			if($action == 'submit_all'){
				$notice .= " Submitted {$submitted_count} to admin.";
			}
		}
		if($skipped_count > 0){
			$errors[] = "{$skipped_count} row(s) were skipped -- either already submitted/approved, or could not be saved.";
		}
	}

	// Fresh state for rendering (post-save or first load).
	$students = array();
	$resultsMap = array();
	if($step == 'grid'){
		$res = $user->get_all_student();
		while($s = $res->fetch_assoc()){ $students[] = $s; }
		$resultsMap = $user->get_results_map($subject_id, $semester);
	}
?>
<?php
$pageTitle = "Bulk Enter Results";
include "php/headertop_faculty.php";
?>
<div class="all_student fix">
	<h3 style="text-align:center;color:#fff;margin:0;padding:5px;background:#1abc9c">Bulk Enter Results</h3>

	<?php if($step == 'pick'){ ?>
	<div style="background:#ddd;padding:20px;" class="fix">
		<p style="text-align:center;margin:0 0 10px 0;">Pick a subject and semester to grade the whole class at once.</p>
		<form action="fct_result_bulk.php" method="get" style="text-align:center;">
			<select name="sub" required>
				<option value="">-- Select Subject --</option>
				<?php
					$subjects = $user->get_subjects();
					while($sub = $subjects->fetch_assoc()){
						echo "<option value='".$sub['id']."'>".htmlspecialchars($sub['name'])."</option>";
					}
				?>
			</select>
			<select name="sem" required>
				<option value="">-- Select Semester --</option>
				<option value="1st">1st semester</option>
				<option value="2nd">2nd semester</option>
				<option value="3rd">3rd semester</option>
			</select>
			<input type="submit" value="Open Grid" style="background:#1abc9c;border:none;color:#fff;padding:8px 16px;" />
		</form>
	</div>
	<?php } else { ?>

	<div>
		<p style="text-align:center;color:#fff;background:purple;margin:0;padding:8px;">
			Subject: <?php echo htmlspecialchars($subjectRow['name']); ?> &nbsp;|&nbsp; Semester: <?php echo htmlspecialchars($semester); ?>
			&nbsp;|&nbsp; <a style="color:#fff;" href="fct_result_bulk.php">Change</a>
		</p>
	</div>

	<?php if($notice){ ?><h3 style="color:green;text-align:center;"><?php echo htmlspecialchars($notice); ?></h3><?php } ?>
	<?php foreach($errors as $e){ ?><p style="color:red;text-align:center;"><?php echo htmlspecialchars($e); ?></p><?php } ?>

	<form method="post" id="bulkForm">
		<input type="hidden" name="subject_id" value="<?php echo (int)$subject_id; ?>" />
		<input type="hidden" name="semester" value="<?php echo htmlspecialchars($semester); ?>" />
		<div style="overflow-x:auto;">
		<table class="tab_one" style="width:100%;text-align:center;border-collapse:collapse;">
			<tr>
				<th>Std Name</th>
				<th>Attendance<br><small>/10</small></th>
				<th>Assignments<br><small>/10</small></th>
				<th>Quizzes<br><small>/10</small></th>
				<th>Project<br><small>/10</small></th>
				<th>Mid Exam<br><small>/20</small></th>
				<th>Sessional<br><small>/60</small></th>
				<th>Final Exam<br><small>/40</small></th>
				<th>Total Marks<br><small>/100</small></th>
				<th>Grade</th>
				<th>Status</th>
			</tr>
			<?php foreach($students as $s){
				$st_id = $s['st_id'];
				$existing = isset($resultsMap[$st_id]) ? $resultsMap[$st_id] : null;
				$status = $existing ? $existing['status'] : null;
				$locked = $existing && $status !== 'draft' && $status !== 'returned';
				$ro = $locked ? 'readonly disabled' : '';
			?>
			<tr class="<?php echo $locked ? 'row-locked' : 'row-editable'; ?>" style="<?php echo $locked ? 'background:#eee;' : ''; ?>">
				<td style="text-align:left;padding-left:8px;"><?php echo $s['name'] !== '' ? htmlspecialchars($s['name']) : '<em style="color:#c0392b;">(no name on file)</em>'; ?><br><small style="color:#888;"><?php echo htmlspecialchars($st_id); ?></small></td>
				<td><input type="number" step="0.1" min="0" max="10" class="cell att" name="rows[<?php echo $st_id; ?>][attendance]" value="<?php echo $existing ? htmlspecialchars($existing['attendance']) : ''; ?>" <?php echo $ro; ?> /></td>
				<td><input type="number" step="0.1" min="0" max="10" class="cell asg" name="rows[<?php echo $st_id; ?>][assignments]" value="<?php echo $existing ? htmlspecialchars($existing['assignments']) : ''; ?>" <?php echo $ro; ?> /></td>
				<td><input type="number" step="0.1" min="0" max="10" class="cell quz" name="rows[<?php echo $st_id; ?>][quizzes]" value="<?php echo $existing ? htmlspecialchars($existing['quizzes']) : ''; ?>" <?php echo $ro; ?> /></td>
				<td><input type="number" step="0.1" min="0" max="10" class="cell prj" name="rows[<?php echo $st_id; ?>][project]" value="<?php echo $existing ? htmlspecialchars($existing['project']) : ''; ?>" <?php echo $ro; ?> /></td>
				<td><input type="number" step="0.1" min="0" max="20" class="cell mid" name="rows[<?php echo $st_id; ?>][midterm]" value="<?php echo $existing ? htmlspecialchars($existing['midterm']) : ''; ?>" <?php echo $ro; ?> /></td>
				<td><strong class="sess">0</strong></td>
				<td><input type="number" step="0.1" min="0" max="40" class="cell fin" name="rows[<?php echo $st_id; ?>][final_exam]" value="<?php echo $existing ? htmlspecialchars($existing['final_exam']) : ''; ?>" <?php echo $ro; ?> /></td>
				<td><strong class="tot">0</strong></td>
				<td><span class="grd">-</span></td>
				<td>
					<?php
						if($status == 'draft') echo "<span style='background:#95a5a6;color:#fff;padding:2px 6px;'>Draft</span>";
						elseif($status == 'submitted') echo "<span style='background:#f39c12;color:#fff;padding:2px 6px;'>Submitted</span>";
						elseif($status == 'returned') echo "<span style='background:#e74c3c;color:#fff;padding:2px 6px;'>Returned</span>";
						elseif($status == 'approved') echo "<span style='background:#27ae60;color:#fff;padding:2px 6px;'>Approved</span>";
						else echo "<span style='color:#aaa;'>-</span>";
					?>
				</td>
			</tr>
			<?php } ?>
			<?php if(count($students) == 0){ ?>
			<tr><td colspan="11">No students found.</td></tr>
			<?php } ?>
		</table>
		</div>

		<div style="text-align:center;margin-top:15px;">
			<button type="submit" name="action" value="draft" style="background:#95a5a6;border:none;color:#fff;padding:10px 20px;">Save All as Draft</button>
			<button type="submit" name="action" value="submit_all" style="background:#1abc9c;border:none;color:#fff;padding:10px 20px;">Save All &amp; Submit to Admin</button>
		</div>
		<p style="text-align:center;color:#888;">Rows already Submitted or Approved are locked here -- edit those individually from the results list instead. Blank rows are left untouched.</p>
	</form>
	<?php } ?>

	<div class="back fix">
		<p style="text-align:center"><a href="fct_results.php"><button class="editbtn">Back to list</button></a></p>
	</div>
</div>

<script>
function gradeFromPercentage(p){
	if(p >= 90) return ["A", 4.0];
	if(p >= 85) return ["A-", 3.7];
	if(p >= 80) return ["B+", 3.3];
	if(p >= 75) return ["B", 3.0];
	if(p >= 70) return ["B-", 2.7];
	if(p >= 65) return ["C+", 2.3];
	if(p >= 60) return ["C", 2.0];
	if(p >= 55) return ["C-", 1.7];
	if(p >= 50) return ["D", 1.0];
	return ["F", 0.0];
}
function clamp(v, max){
	v = parseFloat(v);
	if(isNaN(v)) return 0;
	if(v < 0) return 0;
	if(v > max) return max;
	return v;
}
function recalcRow(tr){
	var att = clamp(tr.querySelector('.att') ? tr.querySelector('.att').value : 0, 10);
	var asg = clamp(tr.querySelector('.asg') ? tr.querySelector('.asg').value : 0, 10);
	var quz = clamp(tr.querySelector('.quz') ? tr.querySelector('.quz').value : 0, 10);
	var prj = clamp(tr.querySelector('.prj') ? tr.querySelector('.prj').value : 0, 10);
	var mid = clamp(tr.querySelector('.mid') ? tr.querySelector('.mid').value : 0, 20);
	var fin = clamp(tr.querySelector('.fin') ? tr.querySelector('.fin').value : 0, 40);

	var sess = att + asg + quz + prj + mid;
	var total = sess + fin;
	var g = gradeFromPercentage(total);

	tr.querySelector('.sess').innerText = sess.toFixed(1);
	tr.querySelector('.tot').innerText = total.toFixed(1);
	tr.querySelector('.grd').innerText = g[0];
}
document.querySelectorAll('#bulkForm tr.row-editable').forEach(function(tr){
	recalcRow(tr);
	tr.querySelectorAll('.cell').forEach(function(el){
		el.addEventListener('input', function(){ recalcRow(tr); });
	});
});
document.querySelectorAll('#bulkForm tr.row-locked').forEach(function(tr){
	recalcRow(tr);
});
</script>

<?php include "php/footerbottom.php";?>
<?php ob_end_flush() ; ?>
