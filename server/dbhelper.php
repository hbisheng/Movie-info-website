<?php

$debug = false;
function console_log( $data ) {
	global $debug;
	if($debug) {
		echo '<script>';
		echo 'console.log('. json_encode( $data ) .')';
		echo '</script>';
	}
}

function init_sql() {
    $servername = "localhost";
    $username = "cs143";
    $password = null;
    // Create connection
    
    $conn = new mysqli($servername, $username, $password);
    
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    } 
    console_log("Connected successfully");

    // make foo the current db
    
    if (!$conn->select_db('CS143')) {
        die ('Can\'t use CS143 : ' . mysql_error());
    }
    
    $conn->query("SET character_set_results = 'utf8', character_set_client = 'utf8', character_set_connection = 'utf8', character_set_database = 'utf8', character_set_server = 'utf8'");
    
    console_log("Load successfully");
	return $conn;
}

function close_sql($conn) {
    $conn->close();
}

// Copy from SO to debug PHP
function process_error_backtrace($errno, $errstr, $errfile, $errline, $errcontext) {
	global $debug;
	if(!($debug)) return;
	if(!(error_reporting() & $errno))
		return;
		switch($errno) {
			case E_WARNING      :
			case E_USER_WARNING :
			case E_STRICT       :
			case E_NOTICE       :
			case E_USER_NOTICE  :
				$type = 'warning';
				$fatal = false;
				break;
			default             :
				$type = 'fatal error';
				$fatal = true;
				break;
		}
		$trace = array_reverse(debug_backtrace());
		array_pop($trace);
		if(php_sapi_name() == 'cli') {
			echo 'Backtrace from ' . $type . ' \'' . $errstr . '\' at ' . $errfile . ' ' . $errline . ':' . "\n";
			foreach($trace as $item)
				echo '  ' . (isset($item['file']) ? $item['file'] : '<unknown file>') . ' ' . (isset($item['line']) ? $item['line'] : '<unknown line>') . ' calling ' . $item['function'] . '()' . "\n";
		} else {
			echo '<p class="error_backtrace">' . "\n";
			echo '  Backtrace from ' . $type . ' \'' . $errstr . '\' at ' . $errfile . ' ' . $errline . ':' . "\n";
			echo '  <ol>' . "\n";
			foreach($trace as $item)
				echo '    <li>' . (isset($item['file']) ? $item['file'] : '<unknown file>') . ' ' . (isset($item['line']) ? $item['line'] : '<unknown line>') . ' calling ' . $item['function'] . '()</li>' . "\n";
				echo '  </ol>' . "\n";
				echo '</p>' . "\n";
		}
		if(ini_get('log_errors')) {
			$items = array();
			foreach($trace as $item)
				$items[] = (isset($item['file']) ? $item['file'] : '<unknown file>') . ' ' . (isset($item['line']) ? $item['line'] : '<unknown line>') . ' calling ' . $item['function'] . '()';
				$message = 'Backtrace from ' . $type . ' \'' . $errstr . '\' at ' . $errfile . ' ' . $errline . ': ' . join(' | ', $items);
				error_log($message);
		}
		if($fatal)
			exit(1);
}

set_error_handler('process_error_backtrace');
?>