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
	$semester = isset($_POST['seme']) ? $_POST['seme'] : null;
?>
<?php
$pageTitle = "My Result";
include "php/headertop.php";
?>
<div class="all_student fix">

	<div>
		<p style="text-align:center;color:#fff;background:purple;margin:0;padding:8px;">Name: <?php echo htmlspecialchars($sname); ?><br>Student ID: <?php echo htmlspecialchars($sid); ?></p>
	</div>

	<form action="" method="post" style="width:25%;margin:0 auto;padding-bottom:5px;">
		<select name="seme">
			<option value="1st" <?php echo $semester=='1st'?'selected':''; ?>>1st Semester</option>
			<option value="2nd" <?php echo $semester=='2nd'?'selected':''; ?>>2nd Semester</option>
			<option value="3rd" <?php echo $semester=='3rd'?'selected':''; ?>>3rd Semester</option>
		</select>
		<input type="submit" value="View Result" />
	</form>
	<div class="fix">
		<p style="float:left;margin:0 0 3px 0;width:100%;text-align:center;"><a href="view_single_cgpa.php"><button class="editbtn">View CGPA & Complete Course</button></a></p>
	</div>

	<?php if($semester){ ?>
		<?php $get_result = $user->get_student_results($sid, $semester); ?>
		<?php if($get_result && $get_result->num_rows > 0){ ?>
			<p style="text-align:center;background:#ddd;color:#01C3AA;padding:5px;width:84%;margin:0 auto"><?php echo htmlspecialchars($semester); ?> Semester Result</p>
			<table class="tab_two" style="text-align:center;width:90%;margin:0 auto">
				<tr>
					<th>Subject</th>
					<th>Attendance</th>
					<th>Assignments</th>
					<th>Quizzes</th>
					<th>Project</th>
					<th>Mid-Term</th>
					<th>Internal (60)</th>
					<th>Final (40)</th>
					<th>Total (100)</th>
					<th>%</th>
					<th>Grade</th>
					<th>GPA</th>
				</tr>
				<?php
					$total_credits = 0; $total_points = 0;
					while($rows = $get_result->fetch_assoc()){
						$total_credits += $rows['credit_hours'];
						$total_points += $rows['credit_hours'] * $rows['grade_point'];
				?>
				<tr>
					<td><?php echo htmlspecialchars($rows['subject_name']); ?></td>
					<td><?php echo $rows['attendance']; ?></td>
					<td><?php echo $rows['assignments']; ?></td>
					<td><?php echo $rows['quizzes']; ?></td>
					<td><?php echo $rows['project']; ?></td>
					<td><?php echo $rows['midterm']; ?></td>
					<td><?php echo $rows['internal_total']; ?></td>
					<td><?php echo $rows['final_exam']; ?></td>
					<td><?php echo $rows['total_marks']; ?></td>
					<td><?php echo $rows['percentage']; ?>%</td>
					<td><?php echo $rows['letter_grade']; ?></td>
					<td><?php echo $rows['grade_point']; ?></td>
				</tr>
				<?php } ?>
				<tr>
					<td colspan="10">SGPA (this semester):</td>
					<td colspan="2">
						<span style="color:green;padding:3px 6px;font-size:20px"><?php echo $total_credits>0 ? round($total_points/$total_credits,2) : "-"; ?></span>
					</td>
				</tr>
			</table>
		<?php } else { ?>
			<p style="color:red;text-align:center">No approved results for this semester yet.</p>
		<?php } ?>
	<?php } ?>

	<p style="float:right;text-align:left;margin:20px 0;width:58%"><a href="st_profile.php"><button class="editbtn">Back to profile</button></a></p>

</div>
<?php include "php/footerbottom.php";?>
<?php ob_end_flush() ; ?>
