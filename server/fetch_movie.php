<?php

function fetch_movie($conn, $id) {
	$statement = "SELECT * FROM Movie WHERE id={$id}";
	$query_result = $conn->query($statement);
	$row = $query_result->fetch_assoc();
	return $row;
}

function fetch_actors($conn, $movie_id) {
	$statement = 
		"SELECT * " . 
		"FROM MovieActor as M, Actor as A " . 
		"WHERE M.mid={$movie_id} AND M.aid=A.id " .
		"ORDER BY A.first";
	$query_result = $conn->query($statement);
	console_log($query_result);
	while($row = $query_result->fetch_assoc()) {
		$actors[] = $row;
	}
	return $actors;
}

function fetch_comments($conn, $movie_id) {
	$statement =
		"SELECT * " .
		"FROM Review " .
		"WHERE mid={$movie_id} " .
		"ORDER BY time DESC";
	
	$query_result = $conn->query($statement);
	while($row = $query_result->fetch_assoc()) {
		$comments[] = $row;
	}
	
	return $comments;
}

function fetch_directors($conn, $movie_id) {
	$statement =
	"SELECT D.first, D.last " .
	"FROM MovieDirector as MD, Director as D " .
	"WHERE MD.mid={$movie_id} AND MD.did=D.id " .
	"ORDER BY D.first";
	
	$query_result = $conn->query($statement);
	while($row = $query_result->fetch_assoc()) {
		$directors[] = $row;
	}
	return $directors;
}

function fetch_genres($conn, $movie_id) {
	$statement =
	"SELECT genre " .
	"FROM MovieGenre " .
	"WHERE mid={$movie_id} " .
	"ORDER BY genre";

	$query_result = $conn->query($statement);
	while($row = $query_result->fetch_assoc()) {
		$genres[] = $row;
	}
	return $genres;
}

include 'dbhelper.php';
if ($_SERVER["REQUEST_METHOD"] == "GET") {
	$id = $_GET['id'];
	$result = null;
	
	// make sure it's a number
	if (isset($id) && is_numeric($id) ) {
		$conn = init_sql();
		
		$movie = fetch_movie($conn, $id);
		$actors = null;
		$comments = null;
		if(isset($movie)) {
			$actors = fetch_actors($conn, $movie['id']);
			$comments = fetch_comments($conn, $movie['id']);
			$directors = fetch_directors($conn, $movie['id']);
			$genres = fetch_genres($conn, $movie['id']);
		}
		close_sql($conn);
		$result = array("movie"=> $movie, "actors"=> $actors, "comments"=>$comments, "directors"=>$directors, "genres"=>$genres);
	} 
	echo json_encode( $result );
}
?>