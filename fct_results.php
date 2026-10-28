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
?>
<?php
$pageTitle = "Student Results";
include "php/headertop_faculty.php";
?>
<div class="all_student fix">
	<h3 style="text-align:center;color:#fff;margin:0;padding:5px;background:#1abc9c">Student Results</h3>

	<p style="text-align:center;margin:15px 0;">
		<a href="fct_result_bulk.php"><button style="background:#1abc9c;border:none;color:#fff;padding:10px 20px;">Enter Marks for Whole Class</button></a>
	</p>

	<div style="background:#ddd;padding:20px;" class="fix">
		<p style="text-align:center;margin:0 0 10px 0;">Or enter one student at a time / continue editing a draft below.</p>
		<form action="fct_result_form.php" method="get" style="text-align:center;">
			<select name="st" required>
				<option value="">-- Select Student --</option>
				<?php
					$students = $user->get_all_student();
					while($s = $students->fetch_assoc()){
						echo "<option value='".htmlspecialchars($s['st_id'])."'>".htmlspecialchars($s['name'])." (".htmlspecialchars($s['st_id']).")</option>";
					}
				?>
			</select>
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
				<option value="1st">1st semester</option>
				<option value="2nd">2nd semester</option>
				<option value="3rd">3rd semester</option>
			</select>
			<input type="submit" value="Enter Marks" style="background:#1abc9c;border:none;color:#fff;padding:8px 16px;" />
		</form>
	</div>

	<p style="text-align:center;background:#ddd;color:#01C3AA;padding:5px;margin:20px 0 0 0;">My Submitted / Draft Results</p>
	<table class="tab_one" style="text-align:center;width:95%;margin:0 auto;">
		<tr>
			<th>SL</th>
			<th>Student</th>
			<th>Subject</th>
			<th>Semester</th>
			<th>Total</th>
			<th>Grade</th>
			<th>Status</th>
			<th>Action</th>
		</tr>
		<?php
			$i = 0;
			$results = $user->get_faculty_results($fid);
			while($r = $results->fetch_assoc()){
				$i++;
				$status = $r['status'];
				$badge = "";
				if($status == 'draft') $badge = "<span style='background:#95a5a6;color:#fff;padding:3px 8px;'>Draft</span>";
				elseif($status == 'submitted') $badge = "<span style='background:#f39c12;color:#fff;padding:3px 8px;'>Submitted</span>";
				elseif($status == 'returned') $badge = "<span style='background:#e74c3c;color:#fff;padding:3px 8px;'>Returned</span>";
				elseif($status == 'approved') $badge = "<span style='background:#27ae60;color:#fff;padding:3px 8px;'>Approved</span>";
		?>
		<tr>
			<td><?php echo $i; ?></td>
			<td><?php echo htmlspecialchars($r['student_name']); ?> (<?php echo htmlspecialchars($r['st_id']); ?>)</td>
			<td><?php echo htmlspecialchars($r['subject_name']); ?></td>
			<td><?php echo htmlspecialchars($r['semester']); ?></td>
			<td><?php echo $r['total_marks'] !== null ? $r['total_marks']."/100" : "-"; ?></td>
			<td><?php echo htmlspecialchars($r['letter_grade']); ?></td>
			<td><?php echo $badge; ?>
				<?php if($status == 'returned' && !empty($r['remarks'])){ echo "<br><small style='color:#e74c3c;'>".htmlspecialchars($r['remarks'])."</small>"; } ?>
			</td>
			<td>
				<?php if($status == 'draft' || $status == 'returned'){ ?>
					<a href="fct_result_form.php?id=<?php echo $r['id']; ?>">Edit</a>
				<?php } else { ?>
					<a href="fct_result_form.php?id=<?php echo $r['id']; ?>&view=1">View</a>
				<?php } ?>
			</td>
		</tr>
		<?php } ?>
		<?php if($i == 0){ ?>
		<tr><td colspan="8">No results entered yet.</td></tr>
		<?php } ?>
	</table>
</div>
<?php include "php/footerbottom.php";?>
<?php ob_end_flush() ; ?>
