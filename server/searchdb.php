<?php

function like_escape($s, $e) {
	return str_replace(array($e, '_', '%'), array($e.$e, $e.'_', $e.'%'), $s);
}

function query_actor_by_name($conn, $keyword) {
	// escape the string, in case keyword contains % and _
	$escape_keyword = like_escape($keyword, "=");
	
	// split the keywords by spaces/tabs/new lines
	$sepa_keywords = preg_split('/\s+/', $escape_keyword); 
	
	// build the query statement for multiple keywords
	$statement = "SELECT * FROM Actor "; 
	$cnt = count($sepa_keywords); // assert $cnt != 0
	$statement .= "WHERE (";
	for($i = 0; $i < $cnt; $i++) {
		$statement .= "concat(first,' ',last) LIKE '%{$sepa_keywords[$i]}%' ESCAPE '=' ";
		if($i != $cnt - 1) $statement .= "AND ";
	}
	$statement .= ") ORDER BY first, last;";
	
	console_log($statement);
	$query_result = $conn->query($statement);
	while($row = $query_result->fetch_assoc()) {
		$actors[] = $row;
	}
	return $actors;
}

// Identical to query actor
function query_director_by_name($conn, $keyword) {
	// escape the string, in case keyword contains % and _
	$escape_keyword = like_escape($keyword, "=");

	// split the keywords by spaces/tabs/new lines
	$sepa_keywords = preg_split('/\s+/', $escape_keyword);

	// build the query statement for multiple keywords
	// any better way to do it? ...
	$statement = "SELECT * FROM Director ";
	$cnt = count($sepa_keywords); // assert $cnt != 0
	$statement .= "WHERE (";
	for($i = 0; $i < $cnt; $i++) {
		$statement .= "concat(first,' ',last) LIKE '%{$sepa_keywords[$i]}%' ESCAPE '=' ";
		if($i != $cnt - 1) $statement .= "AND ";
	}
	$statement .= ") ORDER BY first, last;";

	console_log($statement);
	$query_result = $conn->query($statement);
	while($row = $query_result->fetch_assoc()) {
		$actors[] = $row;
	}
	return $actors;
}

function query_movie_by_name($conn, $keyword) {
	$escape_keyword = like_escape($keyword, "=");
	$sepa_keywords = preg_split('/\s+/', $escape_keyword);
	
	$statement = "SELECT * FROM Movie WHERE ";
	$cnt = count($sepa_keywords);
	for($i = 0; $i < $cnt; $i++) {
		$statement .= "title LIKE '%" . $sepa_keywords[$i] . "%' ESCAPE '=' ";
		if($i != $cnt - 1) $statement .= "AND ";
	}
	$statement .= "ORDER BY title;";
	
	console_log($statement);
	$query_result = $conn->query($statement);
	while($row = $query_result->fetch_assoc()) {
		$movies[] = $row;
	}
	return $movies;
}


include 'dbhelper.php';
if ($_SERVER["REQUEST_METHOD"] == "GET") {
	$keyword = $_GET['keyword'];
	$request_category = json_decode($_GET['request_category'], true); // three bits to represent Actor, Director, Movie
	
	if (isset($keyword)) {
		$conn = init_sql();
		$actors = null;
		$movies = null;
		$directors = null;
		
		if(array_key_exists('actors', $request_category)) $actors = query_actor_by_name($conn, $keyword);
		if(array_key_exists('movies', $request_category)) $movies = query_movie_by_name($conn, $keyword);
		if(array_key_exists('directors', $request_category)) $directors = query_director_by_name($conn, $keyword);
		
		close_sql($conn);
		$result = array("actors" => $actors, "movies" => $movies, "directors" => $directors);
		echo json_encode( $result );
	} 
}
?>