<?php
session_start();
	require "php/config.php";
	require_once "php/functions.php";
	$user = new login_registration_class();
	$admin_id = $_SESSION['admin_id'];
	$admin_name = $_SESSION['admin_name'];
	if(!$user->get_admin_session()){
		header('Location: index.php');
		exit();
	}

	$notice = "";
	$error = "";

	if($_SERVER['REQUEST_METHOD'] == 'POST'){
		$result_id = (int)$_POST['result_id'];
		if($_POST['action'] == 'approve'){
			if($user->approve_result($result_id)){
				$notice = "Result approved. The student can now see it.";
			} else {
				$error = "Could not approve -- it may not be in submitted status.";
			}
		} elseif($_POST['action'] == 'return'){
			$remarks = trim($_POST['remarks']);
			if($remarks == ''){
				$error = "Please provide a reason before returning to the teacher.";
			} elseif($user->return_result($result_id, $remarks)){
				$notice = "Sent back to the teacher for correction.";
			} else {
				$error = "Could not return -- it may not be in submitted status.";
			}
		}
	}

	$status_filter = isset($_GET['status']) ? $_GET['status'] : 'submitted';
	$valid_statuses = array('submitted','draft','returned','approved','all');
	if(!in_array($status_filter, $valid_statuses)){ $status_filter = 'submitted'; }
?>
<?php
$pageTitle = "Review Results";
include "php/headertop_admin.php";
?>
<div class="all_student fix">
	<h3 style="text-align:center;color:#fff;margin:0;padding:5px;background:#1abc9c">Review Results</h3>

	<?php if($notice){ ?><h3 style="color:green;text-align:center;"><?php echo htmlspecialchars($notice); ?></h3><?php } ?>
	<?php if($error){ ?><p style="color:red;text-align:center;"><?php echo htmlspecialchars($error); ?></p><?php } ?>

	<div style="text-align:center;margin:15px 0;">
		<a href="admin_results.php?status=submitted">Pending Review</a> |
		<a href="admin_results.php?status=draft">Drafts</a> |
		<a href="admin_results.php?status=returned">Returned</a> |
		<a href="admin_results.php?status=approved">Approved</a> |
		<a href="admin_results.php?status=all">All</a>
	</div>

	<table class="tab_one" style="text-align:center;width:95%;margin:0 auto;">
		<tr>
			<th>SL</th>
			<th>Student</th>
			<th>Subject</th>
			<th>Semester</th>
			<th>Faculty</th>
			<th>Internal</th>
			<th>Final</th>
			<th>Total</th>
			<th>Grade</th>
			<th>Status</th>
			<th>Action</th>
		</tr>
		<?php
			$i = 0;
			$results = $status_filter == 'all' ? $user->get_admin_results() : $user->get_admin_results($status_filter);
			while($r = $results->fetch_assoc()){
				$i++;
		?>
		<tr>
			<td><?php echo $i; ?></td>
			<td><?php echo htmlspecialchars($r['student_name']); ?> (<?php echo htmlspecialchars($r['st_id']); ?>)</td>
			<td><?php echo htmlspecialchars($r['subject_name']); ?></td>
			<td><?php echo htmlspecialchars($r['semester']); ?></td>
			<td><?php echo htmlspecialchars($r['faculty_name']); ?></td>
			<td><?php echo $r['internal_total'] !== null ? $r['internal_total']."/60" : "-"; ?></td>
			<td><?php echo $r['final_exam'] !== null ? $r['final_exam']."/40" : "-"; ?></td>
			<td><?php echo $r['total_marks'] !== null ? $r['total_marks']."/100" : "-"; ?></td>
			<td><?php echo htmlspecialchars($r['letter_grade']); ?></td>
			<td><?php echo ucfirst($r['status']); ?></td>
			<td>
				<?php if($r['status'] == 'submitted'){ ?>
					<form method="post" style="display:inline;">
						<input type="hidden" name="result_id" value="<?php echo $r['id']; ?>" />
						<input type="hidden" name="action" value="approve" />
						<button type="submit" style="background:#27ae60;border:none;color:#fff;padding:5px 10px;">Approve</button>
					</form>
					<button type="button" onclick="document.getElementById('return-form-<?php echo $r['id']; ?>').style.display='block'" style="background:#e74c3c;border:none;color:#fff;padding:5px 10px;">Return</button>
					<div id="return-form-<?php echo $r['id']; ?>" style="display:none;margin-top:5px;">
						<form method="post">
							<input type="hidden" name="result_id" value="<?php echo $r['id']; ?>" />
							<input type="hidden" name="action" value="return" />
							<textarea name="remarks" placeholder="Reason for returning to teacher" required style="width:150px;"></textarea><br>
							<button type="submit" style="background:#e74c3c;border:none;color:#fff;padding:5px 10px;margin-top:3px;">Confirm Return</button>
						</form>
					</div>
				<?php } else { ?>
					-
				<?php } ?>
			</td>
		</tr>
		<?php } ?>
		<?php if($i == 0){ ?>
		<tr><td colspan="11">Nothing here.</td></tr>
		<?php } ?>
	</table>
</div>
<?php include "php/footerbottom.php";?>
<?php ob_end_flush() ; ?>
