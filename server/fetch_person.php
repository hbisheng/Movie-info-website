<?php

function fetch_actor($conn, $id) {
	$statement = "SELECT * FROM Actor WHERE id={$id}";
	$query_result = $conn->query($statement);
	$row = $query_result->fetch_assoc();
	return $row;
}

function fetch_movies_with_actor($conn, $actor_id) {
	$statement = 
		"SELECT * " . 
		"FROM MovieActor as MA, Movie as M " . 
		"WHERE MA.aid={$actor_id} AND MA.mid=M.id " .
		"ORDER BY M.title";
	$query_result = $conn->query($statement);
	
	while($row = $query_result->fetch_assoc()) {
		$movies[] = $row;
	}
	return $movies;
}


include 'dbhelper.php';
if ($_SERVER["REQUEST_METHOD"] == "GET") {
	$id = $_GET['id'];
	$result = null;
	// make sure it's a number
	if (isset($id) && is_numeric($id) ) {
		$conn = init_sql();
		
		$actor = fetch_actor($conn, $id);
		$movies_with_actor = null;
		
		if(isset($actor)) {
			$movies_with_actor = fetch_movies_with_actor($conn, $actor['id']);
		}
		
		close_sql($conn);
		$result = array("actor"=> $actor, "movies_with_actor"=> $movies_with_actor);
	} 
	echo json_encode( $result );
}
?>