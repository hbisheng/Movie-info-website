<?php

include 'dbhelper.php';

function add_movie_id($conn) {
	// add maxId, return available id
	$conn->query("UPDATE MaxMovieID SET id=id+1;");
	$query_result = $conn->query("SELECT id FROM MaxMovieID LIMIT 1;");
	$row = $query_result->fetch_assoc();
	return $row["id"];
}

if ($_SERVER["REQUEST_METHOD"] == "GET") {
	$title 		= $_GET['title'];
	$company 	= $_GET['company'];
	$rating		= $_GET['rating'];
	$year 		= $_GET['year'];
	$genresJSON = $_GET['genres'];
	
	$result = null;
	if (isset($title) 
			&& isset($company) 
			&& isset($year) 
			&& isset($genresJSON) ) {
		$conn = init_sql();
		
		$id = add_movie_id($conn);
		$statement = "INSERT INTO Movie VALUE({$id}, '{$title}', {$year}, '{$rating}', '{$company}');";
		$status = $conn->query($statement);
		
		// insert into movie table and genre table
		$genres = json_decode($genresJSON, true); // decode into an array
		$cnt = count($genres);
		for($i = 0; $i < $cnt; $i++) {
			$statement = "INSERT INTO MovieGenre VALUE({$id}, '{$genres[$i]}');" ;
			$conn->query($statement);
		}
		
		close_sql($conn);
		$result = array("status"=> $status, "id"=> $id);
	} 
	echo json_encode( $result );
}
?>