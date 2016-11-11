<?php

include 'dbhelper.php';

function add_if_not_exist($conn, $did, $mid) {
	$query_result = $conn->query("SELECT * FROM MovieDirector WHERE mid={$mid} AND did={$did}");
	if($query_result->fetch_assoc()) return false; // the relation already exists
	
	$conn->query("INSERT INTO MovieDirector VALUE({$mid},{$did})");
	return true;
}

if ($_SERVER["REQUEST_METHOD"] == "GET") {
	$did 	= $_GET['did'];
	$mid 	= $_GET['mid'];
	$result = null;
	if (isset($did) 
			&& isset($mid)) {
		$conn = init_sql();
		$status = add_if_not_exist($conn, $did, $mid);
		close_sql($conn);
		$result = array("status"=> $status);
	} 
	echo json_encode( $result );
}
?>