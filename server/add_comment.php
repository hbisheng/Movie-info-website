<?php

function add_comment($conn, $user_name, $user_comment, $user_rate, $movie_id) {
	$statement = "INSERT INTO Review VALUE('{$user_name}', CURRENT_TIMESTAMP, {$movie_id}, {$user_rate}, '{$user_comment}');";
	console_log($statement);
	$query_result = $conn->query($statement);	
	return $query_result;
}

include 'dbhelper.php';
if ($_SERVER["REQUEST_METHOD"] == "GET") {
	$user_name 		= $_GET['user_name'];
	$user_comment 	= $_GET['user_comment'];
	$user_rate 		= $_GET['user_rate'];
	$movie_id 		= $_GET['movie_id'];
	
	$result = null;
	// make sure it's a number
	if (isset($user_name) 
			&& isset($user_comment) 
			&& isset($user_rate)
			&& isset($movie_id)
			&& is_numeric($movie_id) ) {
		$conn = init_sql();
		$status = add_comment($conn, $user_name, $user_comment, $user_rate, $movie_id);
		close_sql($conn);
		$result = array("status"=> $status);
	} 
	echo json_encode( $result );
}
?>