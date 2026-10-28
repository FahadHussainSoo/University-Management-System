<?php
session_start();
	require "php/config.php";
	require_once "php/functions.php";
	$user = new login_registration_class();
	$sid = $_SESSION['sid'];
	$sname = $_SESSION['sname'];
	if(!$user->getsession()){
		header('Location: st_login.php');
		exit();
	}
?>
<?php
$pageTitle = "My CGPA";
include "php/headertop.php";
?>
<div class="all_student fix">

	<div>
		<p style="text-align:center;color:#fff;background:purple;margin:0;padding:8px;">Name: <?php echo htmlspecialchars($sname); ?><br>Student ID: <?php echo htmlspecialchars($sid); ?></p>
	</div>
	<p style='text-align:center;background:#ddd;color:#01C3AA;padding:5px;width:84%;margin:0 auto'>All Approved Courses & Grades</p>

	<?php
		$get_result = $user->get_student_results($sid);
		$cgpa = $user->get_student_cgpa($sid);
	?>
	<?php if($get_result && $get_result->num_rows > 0){ ?>
		<table class="tab_two" style="text-align:center;width:85%;margin:0 auto">
			<tr>
				<th>Semester</th>
				<th>Subject</th>
				<th>Total (100)</th>
				<th>Grade</th>
				<th>GPA</th>
				<th>Credit hr.</th>
				<th>Status</th>
			</tr>
			<?php $i = 0; while($rows = $get_result->fetch_assoc()){ $i++; ?>
			<tr>
				<td><?php echo htmlspecialchars($rows['semester']); ?></td>
				<td><?php echo htmlspecialchars($rows['subject_name']); ?></td>
				<td><?php echo $rows['total_marks']; ?></td>
				<td><?php echo $rows['letter_grade']; ?></td>
				<td><?php echo $rows['grade_point']; ?></td>
				<td><?php echo $rows['credit_hours']; ?></td>
				<td>
					<?php if($rows['letter_grade'] == 'F'){ ?>
						<span style='background:red;padding:3px 11px;color:#fff;'>Fail</span>
					<?php } elseif($rows['grade_point'] < 2.0){ ?>
						<span style='background:#e67e22;padding:3px 6px;color:#fff;'>Retake</span>
					<?php } else { ?>
						<span style='background:green;padding:3px 6px;color:#fff;'>Pass</span>
					<?php } ?>
				</td>
			</tr>
			<?php } ?>
			<tr>
				<td colspan="2">Total Course: <span style='color:#800080;padding:3px 6px;font-size:22px'><?php echo $i; ?></span></td>
				<td colspan="2">Total CGPA:
					<span style='color:green;padding:3px 6px;font-size:22px'><?php echo $cgpa !== null ? $cgpa : "-"; ?></span>
				</td>
				<td colspan="3">
					<?php
						if($cgpa !== null){
							if($cgpa >= 3.5) echo "<span style='background:purple;padding:3px 6px;color:#fff;'>Excellent</span>";
							elseif($cgpa >= 3.0) echo "<span style='background:green;padding:3px 6px;color:#fff;'>Good</span>";
							elseif($cgpa >= 2.5) echo "<span style='background:gray;padding:3px 6px;color:#fff;'>Average</span>";
							else echo "<span style='background:red;padding:3px 6px;color:#fff;'>Probation</span>";
						}
					?>
				</td>
			</tr>
		</table>
	<?php } else { ?>
		<p style='color:red;text-align:center'>No approved results yet.</p>
	<?php } ?>

	<div class="back fix">
		<p style="text-align:center"><a href="view_single_result.php"><button class="editbtn">go to result page</button></a></p>
	</div>

</div>
<?php include "php/footerbottom.php";?>
<?php ob_end_flush() ; ?>
