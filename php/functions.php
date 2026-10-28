<?php
class login_registration_class{
	public function __construct(){
		$db = new databaseConnection();
	}
	
	//All function for Student
	
	//function for student registration
	public function st_registration($st_id,$st_name,$st_pass,$st_email,$bday,$st_dept,$st_contact,$st_gender,$st_add){
		global $conn;
		$query = $conn->query("select st_id from st_info where st_id='$st_id' or email ='$st_email' ");

		$num = $query->num_rows;
		$in_sql = "INSERT INTO st_info (st_id,name,password,email,bday,program,contact,gender,address) VALUES ('$st_id','$st_name','$st_pass','$st_email','$bday','$st_dept','$st_contact','$st_gender','$st_add') ";
		if($num == 0){
			$conn->query($in_sql);
			return true;
		}else{
			return false;
		}
	}
	
	//function for student login
	public function st_userlogin($st_id, $st_pass){
		global $conn;
		$sql = "SELECT st_id,name FROM st_info WHERE st_id=? and password=?";
		$stmt = $conn->prepare($sql);
		$stmt->bind_param("ss", $st_id, $st_pass);
		$stmt->execute();
		$result = $stmt->get_result();
		$userdata = $result->fetch_assoc();
		$count = $result->num_rows;
		if($count == 1){
			session_start();
			$_SESSION['st_login'] = true; 
			$_SESSION['sid'] = $userdata['st_id']; 
			$_SESSION['sname'] = $userdata['name']; 
			//$_SESSION['login_msg'] = "Login Success"; 
			return true;
		}else{
			return false;
		}
		
	}
	
	//function for get student Name 
	public function getusername($sid){
		global $conn;
		$query = $conn->query("select name from st_info where st_id='$sid'");
		$result = $query->fetch_assoc();
		echo $result['name'];
	}
	// Get all info of a specific student by Student ID
	public function getuserbyid($st_id){
		global $conn;
		$stmt = $conn->prepare("select * from st_info where st_id=?");
		$stmt->bind_param("s", $st_id);
		$stmt->execute();
		return $stmt->get_result();
	}
	//Update Student Profile
	public function updateprofile($sid,$st_name,$st_email,$st_dept,$st_gender,$st_contact,$st_add,$file){
		global $conn;
		$query = $conn->query("update st_info set name='$st_name',email='$st_email',program='$st_dept',gender='$st_gender',contact='$st_contact', address='$st_add',img='$file' where st_id='$sid'");
		return true;
	}
	
	//Change Student Password
	public function updatePassword($sid, $newpass, $oldpass){
		global $conn;
		$query = $conn->query("select st_id from st_info where st_id='$sid' and password='$oldpass' ");
		$count = $query->num_rows;
		if($count == 0){
			return print("<p style='color:red;text-align:center'>old password not exist.</p>");
		}else{
			$update = $conn->query("update st_info set password='$newpass' where st_id='$sid' ");
			return print("<p style='color:green;text-align:center'>Password changed successfully.</p>");
		}
	}
	//Session Unset for Student info //Log out option
	public function st_logout(){
		$_SESSION['st_login'] = false;
		unset($_SESSION['sid']); 
		unset($_SESSION['sname']);
		unset($_SESSION['st_login']);
		
		//session_destroy();
	}
	public function getsession(){
		return @$_SESSION['st_login'];
	}

	//Ends student releted function 
	
	/**
	---------------------------------
	All functions for faculty section
	---------------------------------
	**/
	public function fct_registration($name,$uname, $pass,$email, $bday,$gender,$edu,$contact,$address){
		global $conn;
		$fct = $conn->query("select id from faculty where username='$uname' ");
		$count = $fct->num_rows;
		if($count == 0){
			$sql = "insert into faculty(name,username,password,email,birthday,gender,education,contact,address) values('$name','$uname','$pass','$email','$bday','$gender','$edu','$contact','$address')";
			$result = $conn->query($sql);
			return true;
		}else{
			return false;
		}
	}
	//get faculty 
	public function get_faculty_by_username($uname){
		global $conn;
		$sql = "select * from faculty where username='$uname'";
		$result = $conn->query($sql);
		return $result;
	}
	public function get_faculty(){
		global $conn;
		$sql = "select * from faculty order by id ASC";
		$result = $conn->query($sql);
		return $result;
	}
	//login for faculty 
	public function fct_login($uname, $pass){
		global $conn;
		$sql = "select id,username,name from faculty where username=? and password=?";
		$stmt = $conn->prepare($sql);
		$stmt->bind_param("ss", $uname, $pass);
		$stmt->execute();
		$result = $stmt->get_result();
		$count = $result->num_rows;
		$fctinfo = $result->fetch_assoc();
		if($count == 1){
			session_start();
			$_SESSION['fct_login'] = true;
			$_SESSION['f_id'] = $fctinfo['id'];
			$_SESSION['f_uname'] = $fctinfo['username'];
			$_SESSION['f_name'] = $fctinfo['name'];
			return true;
		}else{
			return false;
		}
	}
	public function faculty_logout(){
		$_SESSION['fct_login'] = false;
		unset($_SESSION['f_id']);
		unset($_SESSION['f_uname']);
		unset($_SESSION['f_name']);
		unset($_SESSION['fct_login']);
	}
	public function get_faculty_session(){
		return @$_SESSION['fct_login'];
	}
	
	/*
	**********************
	----------------------
	All functions for Admin 
	----------------------
	**********************
	*/
	
	//for getting All student infomation 
	public function get_all_student(){
		global $conn;
		$sql = "select * from st_info order by st_id ASC";
		$query = $conn->query($sql);
		return $query;
	}
	//search student
	//Search Query
	public function search($query){
		global $conn;
		$result = $conn->query("SELECT * FROM st_info WHERE (st_id LIKE '%".$query."%'
							OR name LIKE '%".$query."%'
								OR contact LIKE '%".$query."%'
									OR email LIKE '%".$query."%') order by st_id");
		return $result;
	}
	
	//Admin log in function 
	public function admin_userlogin($username, $password){
		global $conn;
		$sql  = "select id,username from admin where username=? and password=?";
		$stmt = $conn->prepare($sql);
		$stmt->bind_param("ss", $username, $password);
		$stmt->execute();
		$result = $stmt->get_result();
		$admin_info = $result->fetch_assoc();
		$count = $result->num_rows;
		if($count == 1){
			session_start();
			$_SESSION['admin_login'] = true;
			$_SESSION['admin_id'] = $admin_info['id'];
			$_SESSION['admin_name'] = $admin_info['username'];
			return true;
		}else{
			return false;
		}
		
	}
	public function get_admin_session(){
		return @$_SESSION['admin_login'];
	}
	//admin logout 
	public function admin_logout(){
		$_SESSION['admin_login'] = false;
		unset($_SESSION['admin_id']);
		unset($_SESSION['admin_name']);
		unset($_SESSION['admin_login']);
	}
	//delete student
	public function delete_student($st_id){
		global $conn;
		$sql = "delete from st_info where st_id='$st_id' ";
		$result = $conn->query($sql);
		if($result){
			return true;
		}else{
			return false;
		}
	}
	//attendance system
	
	public function attn_student(){
		global $conn;
		$sql = "select * from at_student";
		$result = $conn->query($sql);
		return $result;
	}
	public function add_attn_student($name,$stid){
		global $conn;
		$sql = "insert into at_student(name,st_id) values('$name','$stid')";
		$result = $conn->query($sql);
		return $result;
	}
	public function insertattn($cur_date,$atten = array()){
		global $conn;
		// Check if attendance for this date has already been taken
		$check = $conn->query("select id from attn where at_date='$cur_date' limit 1");
		if($check && $check->num_rows > 0){
			return false;
		}
		if(empty($atten)){
			return false;
		}
		$all_ok = true;
		foreach($atten as $key =>$attn_value ){
			if($attn_value == "present" || $attn_value == "absent"){
				$sql = "insert into attn(st_id,atten,at_date) values('$key','$attn_value','$cur_date')";
				$att_res = $conn->query($sql);
				if(!$att_res){
					$all_ok = false;
				}
			}
		}
		return $all_ok;
	}
	public function delete_atn_student($at_id){
		global $conn;
		// find the student's st_id first so we can also remove their attendance history
		$find = $conn->query("select st_id from at_student where id='$at_id'");
		if($find && $find->num_rows > 0){
			$row = $find->fetch_assoc();
			$conn->query("delete from attn where st_id='".$row['st_id']."'");
		}
		$res = $conn->query("delete from at_student where id = '$at_id' ");
		return $res;
	}
	public function get_attn_date(){
		global $conn;
		$res = $conn->query("select distinct at_date from attn where at_date != '0000-00-00' order by at_date desc");
		return $res;
		
	}
	public function attn_all_student($date){
		global $conn;
		$res = $conn->query("select at_student.name, attn.*
			from at_student
			inner join attn
			on at_student.st_id = attn.st_id
			where at_date = '$date' ");
		return $res;
	}
	public function update_attn($date,$atten){
		global $conn;
		if(empty($atten)){
			return false;
		}
		$all_ok = true;
		foreach($atten as $key =>$attn_value ){
			if($attn_value == "present" || $attn_value == "absent"){
				$sql = "update attn set atten='$attn_value' where st_id='$key' and at_date='$date' ";
				$att_res = $conn->query($sql);
				if(!$att_res){
					$all_ok = false;
				}
			}
		}
		return $all_ok;
	}
	//grading system -- LEGACY (pre-workflow). Old flat marks were moved to
	//result_legacy by the migration; these are kept only so old data stays
	//queryable, they are NOT used by the new result-workflow pages.
	public function add_marks($stid,$subject,$semester,$marks){
		global $conn;
		$qry = "select * from result_legacy where st_id='$stid' and sub='$subject' and semester='$semester' ";
		$query = $conn->query($qry);
		$count = $query->num_rows;
		if($count>0){
			return false;
		}else{
		$sql = "insert into result_legacy(st_id,marks,sub,semester) values('$stid','$marks','$subject','$semester')";
		$result = $conn->query($sql);
		return $result;
		}
	}
	//show marks
	public function show_marks($stid,$semester){
		global $conn;
		$result = $conn->query("select * from result_legacy where st_id='$stid' and semester='$semester' ");
		$count = $result->num_rows;
		if($count>0){
			return $result;
		}else{
			return false;
		}
		
	}
	//update student result
	public function update_result($stid,$subject = array(),$semester){
		global $conn;
		foreach($subject as $key =>$mark ){
			$sql = "update result_legacy set marks='$mark' where st_id='$stid' and semester='$semester' and sub='$key' ";
				$result = $conn->query($sql);	
		}
		if($result){
			return true;
		}else{
			return false;
		}
	}
	public function view_cgpa($stid){
		global $conn;
		$sql = "select * from result_legacy where st_id='$stid'";
		$result = $conn->query($sql);
		return $result;
	}

	/*
	**************************************************************
	Result Workflow: components -> draft -> submit -> approve/return
	**************************************************************
	*/

	// ---- subjects ----
	public function get_subjects(){
		global $conn;
		return $conn->query("select * from subjects order by name asc");
	}
	public function get_subject_by_id($subject_id){
		global $conn;
		$stmt = $conn->prepare("select * from subjects where id=?");
		$stmt->bind_param("i", $subject_id);
		$stmt->execute();
		return $stmt->get_result()->fetch_assoc();
	}

	// ---- grading policy: percentage -> letter grade -> 4.0 grade point ----
	// Standard scale, agreed grading policy:
	// A 90-100 (4.0) | A- 85-89.99 (3.7) | B+ 80-84.99 (3.3) | B 75-79.99 (3.0)
	// B- 70-74.99 (2.7) | C+ 65-69.99 (2.3) | C 60-64.99 (2.0) | C- 55-59.99 (1.7)
	// D 50-54.99 (1.0) | F below 50 (0.0)
	public function grade_from_percentage($percentage){
		$percentage = (float)$percentage;
		if($percentage >= 90)  return array("A",  4.0);
		if($percentage >= 85)  return array("A-", 3.7);
		if($percentage >= 80)  return array("B+", 3.3);
		if($percentage >= 75)  return array("B",  3.0);
		if($percentage >= 70)  return array("B-", 2.7);
		if($percentage >= 65)  return array("C+", 2.3);
		if($percentage >= 60)  return array("C",  2.0);
		if($percentage >= 55)  return array("C-", 1.7);
		if($percentage >= 50)  return array("D",  1.0);
		return array("F", 0.0);
	}

	// Clamp a component mark into its valid [0, max] range so bad input
	// (negative, or over the component cap) can never silently corrupt totals.
	private function clamp_mark($value, $max){
		if($value === null || $value === ''){ return 0; }
		$value = (float)$value;
		if($value < 0) return 0;
		if($value > $max) return $max;
		return $value;
	}

	// Central place where every number on a result gets (re)computed.
	// Used by both the live preview and the actual save, so what the
	// teacher sees while typing is guaranteed to match what gets stored.
	public function compute_result_values($attendance,$assignments,$quizzes,$project,$midterm,$final_exam){
		$attendance  = $this->clamp_mark($attendance, 10);
		$assignments = $this->clamp_mark($assignments, 10);
		$quizzes     = $this->clamp_mark($quizzes, 10);
		$project     = $this->clamp_mark($project, 10);
		$midterm     = $this->clamp_mark($midterm, 20);
		$final_exam  = $this->clamp_mark($final_exam, 40);

		$internal_total = $attendance + $assignments + $quizzes + $project + $midterm; // out of 60
		$total_marks = $internal_total + $final_exam; // out of 100
		$percentage = round(($total_marks / 100) * 100, 2); // total is already /100, kept explicit for clarity
		list($letter, $gpa) = $this->grade_from_percentage($percentage);

		return array(
			"attendance" => $attendance,
			"assignments" => $assignments,
			"quizzes" => $quizzes,
			"project" => $project,
			"midterm" => $midterm,
			"internal_total" => $internal_total,
			"final_exam" => $final_exam,
			"total_marks" => $total_marks,
			"percentage" => $percentage,
			"letter_grade" => $letter,
			"grade_point" => $gpa
		);
	}

	// ---- faculty: create / save-as-draft / edit-while-draft-or-returned ----
	// Returns the result id on success, or false. Enforces that a result
	// can only be written while it doesn't exist yet, or is 'draft'/'returned'.
	public function save_result_draft($result_id, $st_id, $subject_id, $semester, $faculty_id,
		$attendance,$assignments,$quizzes,$project,$midterm,$final_exam){
		global $conn;

		$v = $this->compute_result_values($attendance,$assignments,$quizzes,$project,$midterm,$final_exam);

		if($result_id){
			// Editing an existing entry -- only allowed if it's still editable
			// and it belongs to this teacher.
			$existing = $this->get_result_by_id($result_id);
			if(!$existing || $existing['faculty_id'] != $faculty_id){ return false; }
			if($existing['status'] !== 'draft' && $existing['status'] !== 'returned'){ return false; }

			$sql = "update result set
					attendance = ?, assignments = ?, quizzes = ?, project = ?, midterm = ?,
					internal_total = ?, final_exam = ?, total_marks = ?, percentage = ?,
					letter_grade = ?, grade_point = ?, status = 'draft', remarks = NULL
				where id = ? and faculty_id = ?";
			$stmt = $conn->prepare($sql);
			$stmt->bind_param(
				"dddddddddsdii",
				$v['attendance'], $v['assignments'], $v['quizzes'], $v['project'], $v['midterm'],
				$v['internal_total'], $v['final_exam'], $v['total_marks'], $v['percentage'],
				$v['letter_grade'], $v['grade_point'], $result_id, $faculty_id
			);
			$stmt->execute();
			return $result_id;
		} else {
			// New entry. Unique key (st_id, subject_id, semester) stops duplicates.
			$sql = "insert into result
					(st_id, subject_id, semester, faculty_id, attendance, assignments, quizzes, project,
					 midterm, internal_total, final_exam, total_marks, percentage, letter_grade, grade_point, status)
				values (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,'draft')";
			$stmt = $conn->prepare($sql);
			$stmt->bind_param(
				"iisidddddddddsd",
				$st_id, $subject_id, $semester, $faculty_id,
				$v['attendance'], $v['assignments'], $v['quizzes'], $v['project'], $v['midterm'],
				$v['internal_total'], $v['final_exam'], $v['total_marks'], $v['percentage'],
				$v['letter_grade'], $v['grade_point']
			);
			if($stmt->execute()){
				return $conn->insert_id;
			}
			return false;
		}
	}

	// Teacher submits a draft/returned result to the admin. Locks editing.
	public function submit_result($result_id, $faculty_id){
		global $conn;
		$existing = $this->get_result_by_id($result_id);
		if(!$existing || $existing['faculty_id'] != $faculty_id){ return false; }
		if($existing['status'] !== 'draft' && $existing['status'] !== 'returned'){ return false; }
		$stmt = $conn->prepare("update result set status='submitted', remarks=NULL where id=? and faculty_id=?");
		$stmt->bind_param("ii", $result_id, $faculty_id);
		return $stmt->execute();
	}

	// Admin approves a submitted result. Admin never touches the marks themselves.
	public function approve_result($result_id){
		global $conn;
		$existing = $this->get_result_by_id($result_id);
		if(!$existing || $existing['status'] !== 'submitted'){ return false; }
		$stmt = $conn->prepare("update result set status='approved' where id=?");
		$stmt->bind_param("i", $result_id);
		return $stmt->execute();
	}

	// Admin sends a submitted result back to the teacher with a reason.
	public function return_result($result_id, $remarks){
		global $conn;
		$existing = $this->get_result_by_id($result_id);
		if(!$existing || $existing['status'] !== 'submitted'){ return false; }
		$stmt = $conn->prepare("update result set status='returned', remarks=? where id=?");
		$stmt->bind_param("si", $remarks, $result_id);
		return $stmt->execute();
	}

	public function get_result_by_id($result_id){
		global $conn;
		$stmt = $conn->prepare(
			"select r.*, s.name as subject_name, s.credit_hours, st.name as student_name, f.name as faculty_name
			 from result r
			 join subjects s on s.id = r.subject_id
			 join st_info st on st.st_id = r.st_id
			 join faculty f on f.id = r.faculty_id
			 where r.id = ?"
		);
		$stmt->bind_param("i", $result_id);
		$stmt->execute();
		return $stmt->get_result()->fetch_assoc();
	}

	// Find (or detect) the entry for a given student+subject+semester,
	// used by the faculty entry form to decide "new" vs "edit existing".
	public function find_result($st_id, $subject_id, $semester){
		global $conn;
		$stmt = $conn->prepare("select * from result where st_id=? and subject_id=? and semester=?");
		$stmt->bind_param("iis", $st_id, $subject_id, $semester);
		$stmt->execute();
		return $stmt->get_result()->fetch_assoc();
	}

	// All existing result rows for one subject+semester, keyed by st_id,
	// so the bulk-entry grid can pre-fill/lock a row per student in one query.
	public function get_results_map($subject_id, $semester){
		global $conn;
		$stmt = $conn->prepare("select * from result where subject_id=? and semester=?");
		$stmt->bind_param("is", $subject_id, $semester);
		$stmt->execute();
		$res = $stmt->get_result();
		$map = array();
		while($row = $res->fetch_assoc()){
			$map[$row['st_id']] = $row;
		}
		return $map;
	}

	public function get_faculty_results($faculty_id){
		global $conn;
		$stmt = $conn->prepare(
			"select r.*, s.name as subject_name, st.name as student_name
			 from result r
			 join subjects s on s.id = r.subject_id
			 join st_info st on st.st_id = r.st_id
			 where r.faculty_id = ?
			 order by r.updated_at desc"
		);
		$stmt->bind_param("i", $faculty_id);
		$stmt->execute();
		return $stmt->get_result();
	}

	public function get_admin_results($status = null){
		global $conn;
		if($status){
			$stmt = $conn->prepare(
				"select r.*, s.name as subject_name, st.name as student_name, f.name as faculty_name
				 from result r
				 join subjects s on s.id = r.subject_id
				 join st_info st on st.st_id = r.st_id
				 join faculty f on f.id = r.faculty_id
				 where r.status = ?
				 order by r.updated_at desc"
			);
			$stmt->bind_param("s", $status);
			$stmt->execute();
			return $stmt->get_result();
		}
		return $conn->query(
			"select r.*, s.name as subject_name, st.name as student_name, f.name as faculty_name
			 from result r
			 join subjects s on s.id = r.subject_id
			 join st_info st on st.st_id = r.st_id
			 join faculty f on f.id = r.faculty_id
			 order by r.updated_at desc"
		);
	}

	// ---- admin: view one student's results across all statuses (read-only) ----
	public function get_admin_results_for_student($st_id){
		global $conn;
		$stmt = $conn->prepare(
			"select r.*, s.name as subject_name, s.credit_hours, st.name as student_name, f.name as faculty_name
			 from result r
			 join subjects s on s.id = r.subject_id
			 join st_info st on st.st_id = r.st_id
			 join faculty f on f.id = r.faculty_id
			 where r.st_id = ?
			 order by r.semester asc, s.name asc"
		);
		$stmt->bind_param("i", $st_id);
		$stmt->execute();
		return $stmt->get_result();
	}

	// ---- student: approved results only ----
	public function get_student_results($st_id, $semester = null){
		global $conn;
		if($semester){
			$stmt = $conn->prepare(
				"select r.*, s.name as subject_name, s.credit_hours
				 from result r join subjects s on s.id = r.subject_id
				 where r.st_id = ? and r.semester = ? and r.status = 'approved'
				 order by s.name asc"
			);
			$stmt->bind_param("is", $st_id, $semester);
			$stmt->execute();
			return $stmt->get_result();
		}
		$stmt = $conn->prepare(
			"select r.*, s.name as subject_name, s.credit_hours
			 from result r join subjects s on s.id = r.subject_id
			 where r.st_id = ? and r.status = 'approved'
			 order by r.semester asc, s.name asc"
		);
		$stmt->bind_param("i", $st_id);
		$stmt->execute();
		return $stmt->get_result();
	}

	// Credit-hour weighted CGPA across all of a student's APPROVED results.
	public function get_student_cgpa($st_id){
		global $conn;
		$stmt = $conn->prepare(
			"select r.grade_point, s.credit_hours
			 from result r join subjects s on s.id = r.subject_id
			 where r.st_id = ? and r.status = 'approved'"
		);
		$stmt->bind_param("i", $st_id);
		$stmt->execute();
		$res = $stmt->get_result();

		$total_points = 0.0;
		$total_credits = 0.0;
		while($row = $res->fetch_assoc()){
			$total_points += ((float)$row['grade_point']) * ((float)$row['credit_hours']);
			$total_credits += (float)$row['credit_hours'];
		}
		if($total_credits == 0){ return null; }
		return round($total_points / $total_credits, 2);
	}
	
	
	
	/* Total average marks
	public function sgpa(){
		global $conn;
		$sql = "SELECT avg(marks) as sgpa from result where st_id=12103072 and semester='1st'";
		$result = $conn->query($sql);
		return $result;
	}
	*/
	
	
	
	
	
//end class 	
};



?>