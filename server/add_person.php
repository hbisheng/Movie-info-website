<?php

include 'dbhelper.php';

function add_person_id($conn) {
	// add maxId, return available id
	$conn->query("UPDATE MaxPersonID SET id=id+1;");
	$query_result = $conn->query("SELECT id FROM MaxPersonID LIMIT 1;");
	$row = $query_result->fetch_assoc();
	return $row["id"];
}

if ($_SERVER["REQUEST_METHOD"] == "GET") {
	$first 	= $_GET['first'];
	$last 	= $_GET['last'];
	$gender = $_GET['gender'];
	$role 	= $_GET['role'];
	$dob	= $_GET['dob'];
	$dod 	= $_GET['dod'];
	
	$result = null;
	if (isset($first) 
			&& isset($last) 
			&& isset($gender) 
			&& isset($role)
			&& isset($dob)) {
		$conn = init_sql();
		
		$statement = null;
		$id = add_person_id($conn);
		
		if($role == 'actor') {
			if(isset($dod)) $statement = "INSERT INTO Actor VALUE({$id}, '{$last}', '{$first}', '{$gender}', '{$dob}', '{$dod}');";
			else $statement = "INSERT INTO Actor VALUE({$id}, '{$last}', '{$first}', '{$gender}', '{$dob}', NULL);";
		} else {
			if(isset($dod)) $statement = "INSERT INTO Director VALUE({$id}, '{$last}', '{$first}', '{$dob}', '{$dod}');";
			else $statement = "INSERT INTO Director VALUE({$id}, '{$last}', '{$first}', '{$dob}', NULL);";
		}
		
		console_log($statement);
		$status = $conn->query($statement);
		close_sql($conn);
		$result = array("status"=> $status, "id"=> $id);
	} 
	echo json_encode( $result );
}
?>