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
$pageTitle = "Student Result";
include "php/headertop_admin.php";
?>
<div class="all_student fix">

	<div>
		<p style="text-align:center;color:#fff;background:purple;margin:0;padding:8px;">Name: <?php echo htmlspecialchars($name); ?><br>Student ID: <?php echo htmlspecialchars($stid); ?></p>
	</div>
	<div>
		<p style="float:left;margin:0 0 5px 0;width:100%;text-align:center;"><a href="view_cgpa.php?vr=<?php echo urlencode($stid); ?>&vn=<?php echo urlencode($name); ?>"><button class="editbtn">View CGPA & completed course</button></a></p>
	</div>

	<p style="text-align:center;color:#888;">Admin can view results but cannot edit marks. Use <a href="admin_results.php">Review Results</a> to approve or return submissions.</p>

	<table class="tab_two" style="text-align:center;width:90%;margin:0 auto">
		<tr>
			<th>Semester</th>
			<th>Subject</th>
			<th>Faculty</th>
			<th>Internal (60)</th>
			<th>Final (40)</th>
			<th>Total (100)</th>
			<th>Grade</th>
			<th>Status</th>
		</tr>
		<?php
			$results = $user->get_admin_results_for_student($stid);
			$i = 0;
			while($r = $results->fetch_assoc()){
				$i++;
		?>
		<tr>
			<td><?php echo htmlspecialchars($r['semester']); ?></td>
			<td><?php echo htmlspecialchars($r['subject_name']); ?></td>
			<td><?php echo htmlspecialchars($r['faculty_name']); ?></td>
			<td><?php echo $r['internal_total'] !== null ? $r['internal_total'] : '-'; ?></td>
			<td><?php echo $r['final_exam'] !== null ? $r['final_exam'] : '-'; ?></td>
			<td><?php echo $r['total_marks'] !== null ? $r['total_marks'] : '-'; ?></td>
			<td><?php echo htmlspecialchars($r['letter_grade']); ?></td>
			<td><?php echo ucfirst($r['status']); ?></td>
		</tr>
		<?php } ?>
		<?php if($i == 0){ ?>
		<tr><td colspan="8">No results found for this student.</td></tr>
		<?php } ?>
	</table>

	<div class="back fix">
		<p style="text-align:center"><a href="st_result.php"><button class="editbtn">Back to list</button></a></p>
	</div>
</div>
<?php include "php/footerbottom.php";?>
<?php ob_end_flush() ; ?>
