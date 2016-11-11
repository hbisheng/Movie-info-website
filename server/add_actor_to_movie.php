<?php

include 'dbhelper.php';

function add_if_not_exist($conn, $aid, $mid, $role) {
	$query_result = $conn->query("SELECT * FROM MovieActor WHERE mid={$mid} AND aid={$aid}");
	if($query_result->fetch_assoc()) return false; // the relation already exists
	
	$conn->query("INSERT INTO MovieActor VALUE({$mid},{$aid}, '{$role}')");
	return true;
}

if ($_SERVER["REQUEST_METHOD"] == "GET") {
	$aid 	= $_GET['aid'];
	$mid 	= $_GET['mid'];
	$role	= $_GET['role'];
	$result = null;
	if (isset($aid) 
			&& isset($mid)) {
		$conn = init_sql();
		$status = add_if_not_exist($conn, $aid, $mid, $role);
		close_sql($conn);
		$result = array("status"=> $status);
	} 
	echo json_encode( $result );
}
?>