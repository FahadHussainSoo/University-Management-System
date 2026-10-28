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
	if(isset($_REQUEST['vr'])){
		$stid = $_REQUEST['vr'];
		$name = $_REQUEST['vn'];
	} else {
		header('Location: st_result.php');
		exit();
	}
?>
<?php
$pageTitle = "Student CGPA";
include "php/headertop_admin.php";
?>
<div class="all_student fix">

	<div>
		<p style="text-align:center;color:#fff;background:purple;margin:0;padding:8px;">Name: <?php echo htmlspecialchars($name); ?><br>Student ID: <?php echo htmlspecialchars($stid); ?></p>
	</div>
	<p style='text-align:center;background:#ddd;color:#01C3AA;padding:5px;width:84%;margin:0 auto'>All Approved Courses & Grades</p>

	<?php
		$get_result = $user->get_student_results($stid);
		$cgpa = $user->get_student_cgpa($stid);
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
			</tr>
			<?php $i = 0; while($rows = $get_result->fetch_assoc()){ $i++; ?>
			<tr>
				<td><?php echo htmlspecialchars($rows['semester']); ?></td>
				<td><?php echo htmlspecialchars($rows['subject_name']); ?></td>
				<td><?php echo $rows['total_marks']; ?></td>
				<td><?php echo $rows['letter_grade']; ?></td>
				<td><?php echo $rows['grade_point']; ?></td>
				<td><?php echo $rows['credit_hours']; ?></td>
			</tr>
			<?php } ?>
			<tr>
				<td colspan="2">Total Course: <span style='color:#800080;padding:3px 6px;font-size:22px'><?php echo $i; ?></span></td>
				<td colspan="2">Total CGPA:
					<span style='color:green;padding:3px 6px;font-size:22px'><?php echo $cgpa !== null ? $cgpa : "-"; ?></span>
				</td>
				<td colspan="2">
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
		<p style="text-align:center"><a href="view_result.php?vr=<?php echo urlencode($stid); ?>&vn=<?php echo urlencode($name); ?>"><button class="editbtn">go to result page</button></a></p>
	</div>

</div>
<?php include "php/footerbottom.php";?>
<?php ob_end_flush() ; ?>
