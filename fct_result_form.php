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
	$existing = null;
	$result_id = isset($_GET['id']) ? (int)$_GET['id'] : (isset($_POST['result_id']) ? (int)$_POST['result_id'] : 0);

	if($result_id){
		$existing = $user->get_result_by_id($result_id);
		if(!$existing || $existing['faculty_id'] != $fid){
			header('Location: fct_results.php');
			exit();
		}
		$st_id = $existing['st_id'];
		$subject_id = $existing['subject_id'];
		$semester = $existing['semester'];
		$student_name = $existing['student_name'];
		$subject_name = $existing['subject_name'];
	} else {
		if(!isset($_GET['st']) || !isset($_GET['sub']) || !isset($_GET['sem'])){
			header('Location: fct_results.php');
			exit();
		}
		$st_id = $_GET['st'];
		$subject_id = (int)$_GET['sub'];
		$semester = $_GET['sem'];
		$studentRow = $user->getuserbyid($st_id)->fetch_assoc();
		$subjectRow = $user->get_subject_by_id($subject_id);
		if(!$studentRow || !$subjectRow){
			header('Location: fct_results.php');
			exit();
		}
		$student_name = $studentRow['name'];
		$subject_name = $subjectRow['name'];
		// If a result already exists for this student+subject+semester, redirect to it
		$maybeExisting = $user->find_result($st_id, $subject_id, $semester);
		if($maybeExisting){
			header('Location: fct_result_form.php?id='.$maybeExisting['id']);
			exit();
		}
	}

	$is_locked = $existing && $existing['status'] !== 'draft' && $existing['status'] !== 'returned';
	$view_only = isset($_GET['view']) || $is_locked;

	if($_SERVER['REQUEST_METHOD'] == 'POST' && !$view_only){
		$attendance  = isset($_POST['attendance']) ? $_POST['attendance'] : 0;
		$assignments = isset($_POST['assignments']) ? $_POST['assignments'] : 0;
		$quizzes     = isset($_POST['quizzes']) ? $_POST['quizzes'] : 0;
		$project     = isset($_POST['project']) ? $_POST['project'] : 0;
		$midterm     = isset($_POST['midterm']) ? $_POST['midterm'] : 0;
		$final_exam  = isset($_POST['final_exam']) ? $_POST['final_exam'] : 0;
		$action      = isset($_POST['action']) ? $_POST['action'] : 'draft';

		$saved_id = $user->save_result_draft($result_id ?: null, $st_id, $subject_id, $semester, $fid,
			$attendance,$assignments,$quizzes,$project,$midterm,$final_exam);

		if($saved_id){
			if($action == 'submit'){
				if($user->submit_result($saved_id, $fid)){
					$notice = "Marks saved and submitted to admin for review.";
				} else {
					$errors[] = "Could not submit -- please save as draft first.";
				}
			} else {
				$notice = "Saved as draft.";
			}
			// reload fresh state
			$result_id = $saved_id;
			$existing = $user->get_result_by_id($result_id);
			$is_locked = $existing['status'] !== 'draft' && $existing['status'] !== 'returned';
			$view_only = $is_locked;
		} else {
			$errors[] = "Could not save. This entry may have already been submitted.";
		}
	}

	// values to pre-fill the form
	$vals = array('attendance'=>'','assignments'=>'','quizzes'=>'','project'=>'','midterm'=>'','final_exam'=>'');
	if($existing){
		foreach(array('attendance','assignments','quizzes','project','midterm') as $f){
			$vals[$f] = $existing[$f];
		}
		$vals['final_exam'] = $existing['final_exam'];
	}
?>
<?php
$pageTitle = "Enter Result";
include "php/headertop_faculty.php";
?>
<div class="all_student fix">
	<h3 style="text-align:center;color:#fff;margin:0;padding:5px;background:#1abc9c">
		<?php echo $view_only ? "View Result" : ($result_id ? "Edit Result" : "Enter Result"); ?>
	</h3>
	<div>
		<p style="text-align:center;color:#fff;background:purple;margin:0;padding:8px;">
			Student: <?php echo $student_name !== '' ? htmlspecialchars($student_name) : '<em>(no name on file)</em>'; ?> (<?php echo htmlspecialchars($st_id); ?>)<br>
			Subject: <?php echo $subject_name !== '' ? htmlspecialchars($subject_name) : '<em>(no name on file)</em>'; ?> &nbsp;|&nbsp; Semester: <?php echo htmlspecialchars($semester); ?>
		</p>
	</div>

	<?php if($notice){ ?><h3 style="color:green;text-align:center;"><?php echo $notice; ?></h3><?php } ?>
	<?php foreach($errors as $e){ ?><p style="color:red;text-align:center;"><?php echo htmlspecialchars($e); ?></p><?php } ?>

	<?php if($existing && $existing['status'] == 'returned' && !empty($existing['remarks'])){ ?>
		<p style="text-align:center;background:#fdecea;color:#c0392b;padding:8px;width:60%;margin:10px auto;border:1px solid #e74c3c;">
			<strong>Returned by admin:</strong> <?php echo htmlspecialchars($existing['remarks']); ?>
		</p>
	<?php } ?>

	<?php if($view_only && $existing){ ?>
		<p style="text-align:center;">Status:
			<strong><?php echo ucfirst($existing['status']); ?></strong>
			-- this entry can no longer be edited<?php echo $existing['status']=='submitted' ? ' while it is awaiting admin review.' : '.'; ?>
		</p>
	<?php } ?>

	<div style="width:50%;margin:20px auto;">
		<form method="post" id="resultForm">
			<input type="hidden" name="result_id" value="<?php echo (int)$result_id; ?>" />
			<table class="tab_one" style="width:100%;text-align:center;">
				<tr><td>Attendance (out of 10)</td><td><input type="number" step="0.1" min="0" max="10" name="attendance" id="attendance" value="<?php echo htmlspecialchars($vals['attendance']); ?>" <?php echo $view_only?'readonly':'required'; ?> /></td></tr>
				<tr><td>Assignments (out of 10)</td><td><input type="number" step="0.1" min="0" max="10" name="assignments" id="assignments" value="<?php echo htmlspecialchars($vals['assignments']); ?>" <?php echo $view_only?'readonly':'required'; ?> /></td></tr>
				<tr><td>Quizzes (out of 10)</td><td><input type="number" step="0.1" min="0" max="10" name="quizzes" id="quizzes" value="<?php echo htmlspecialchars($vals['quizzes']); ?>" <?php echo $view_only?'readonly':'required'; ?> /></td></tr>
				<tr><td>Project (out of 10)</td><td><input type="number" step="0.1" min="0" max="10" name="project" id="project" value="<?php echo htmlspecialchars($vals['project']); ?>" <?php echo $view_only?'readonly':'required'; ?> /></td></tr>
				<tr><td>Mid-Term Exam (out of 20)</td><td><input type="number" step="0.1" min="0" max="20" name="midterm" id="midterm" value="<?php echo htmlspecialchars($vals['midterm']); ?>" <?php echo $view_only?'readonly':'required'; ?> /></td></tr>
				<tr style="background:#eee;"><td><strong>Internal Assessment Total</strong> (/60)</td><td><strong id="internalTotalDisplay">0</strong></td></tr>
				<tr><td>Final Exam (out of 40)</td><td><input type="number" step="0.1" min="0" max="40" name="final_exam" id="final_exam" value="<?php echo htmlspecialchars($vals['final_exam']); ?>" <?php echo $view_only?'readonly':'required'; ?> /></td></tr>
				<tr style="background:#eee;"><td><strong>Total Marks</strong> (/100)</td><td><strong id="totalDisplay">0</strong></td></tr>
				<tr><td>Percentage</td><td><span id="percentDisplay">0%</span></td></tr>
				<tr><td>Letter Grade</td><td><span id="gradeDisplay">-</span></td></tr>
				<tr><td>GPA</td><td><span id="gpaDisplay">0.00</span></td></tr>
			</table>

			<?php if(!$view_only){ ?>
			<div style="text-align:center;margin-top:15px;">
				<button type="submit" name="action" value="draft" style="background:#95a5a6;border:none;color:#fff;padding:10px 20px;">Save as Draft</button>
				<button type="submit" name="action" value="submit" style="background:#1abc9c;border:none;color:#fff;padding:10px 20px;" <?php echo $result_id ? '' : 'disabled title="Save as draft first"'; ?>>Submit to Admin</button>
			</div>
			<?php if(!$result_id){ ?>
				<p style="text-align:center;color:#888;">Save as draft first -- the Submit button unlocks once a draft exists.</p>
			<?php } ?>
			<?php } ?>
		</form>
	</div>
	<div class="back fix">
		<p style="text-align:center"><a href="fct_results.php"><button class="editbtn">Back to list</button></a></p>
	</div>
</div>

<script>
// Grading table mirrors php/functions.php grade_from_percentage() -- keep both in sync.
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
function recalc(){
	var attendance = clamp(document.getElementById('attendance').value, 10);
	var assignments = clamp(document.getElementById('assignments').value, 10);
	var quizzes = clamp(document.getElementById('quizzes').value, 10);
	var project = clamp(document.getElementById('project').value, 10);
	var midterm = clamp(document.getElementById('midterm').value, 20);
	var final_exam = clamp(document.getElementById('final_exam').value, 40);

	var internalTotal = attendance + assignments + quizzes + project + midterm;
	var total = internalTotal + final_exam;
	var percent = total; // total is already out of 100

	var g = gradeFromPercentage(percent);

	document.getElementById('internalTotalDisplay').innerText = internalTotal.toFixed(1) + " / 60";
	document.getElementById('totalDisplay').innerText = total.toFixed(1) + " / 100";
	document.getElementById('percentDisplay').innerText = percent.toFixed(2) + "%";
	document.getElementById('gradeDisplay').innerText = g[0];
	document.getElementById('gpaDisplay').innerText = g[1].toFixed(2);
}
['attendance','assignments','quizzes','project','midterm','final_exam'].forEach(function(id){
	var el = document.getElementById(id);
	if(el){ el.addEventListener('input', recalc); }
});
recalc();
</script>

<?php include "php/footerbottom.php";?>
<?php ob_end_flush() ; ?>
