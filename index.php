<?php
header ( "Content-Type: application/json" );
include "include/mysql.php";
/*
 * latematt's file upload script
 * - json output
 * - randomized file names
 * - extension blocking
 * - easy key protection
 * - file logging system so you know who's uploading what
 *
 * used for http://u.lmao.gq
 */
function generateRandomFileName() {
	$characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
	$newname = '';
	for($i = 0; $i < rand ( 4, 8 ); $i ++) {
		$newname .= $characters [rand ( 0, strlen ( $characters ) - 1 )];
	}
	
	return $newname;
}
function checkExtension($extension, $blockedExtensions) {
	$blockedFound = false;
	foreach ( $blockedExtensions as $bExtension ) {
		if ($extension == $bExtension) {
			$blockedFound = true;
		}
	}
	
	return $blockedFound;
}
function checkKey($key, $mysql_host, $mysql_user, $mysql_password, $mysql_database) {
	$mysqli = new mysqli ( $mysql_host, $mysql_user, $mysql_password, $mysql_database );
	if ($mysqli->connection_errno > 0) {
		$data = array (
				'mysql_error' => $mysqli->connect_error,
				"mysql_error_type" => "database_connection" 
		);
		echo json_encode ( $data );
	}
	
	$result = $mysqli->query ( "SELECT * FROM users" );
	if (! $result) {
		$data = array (
				'mysql_error' => $mysqli->error,
				"mysql_error_type" => "user_fetching" 
		);
		echo json_encode ( $data );
	}
	
	$mysqli->close ();
	$keyValid = false;
	while ( $row = $result->fetch_assoc () ) {
		if ($row ['key'] == $key && $row ['enabled'] == 1) {
			$keyValid = true;
		}
	}
	
	return $keyValid;
}
function logFile($username, $key, $filename, $mysql_host, $mysql_user, $mysql_password, $mysql_database) {
	$mysqli = new mysqli ( $mysql_host, $mysql_user, $mysql_password, $mysql_database );
	if ($mysqli->connection_errno > 0) {
		$data = array (
				'mysql_error' => $mysqli->connect_error,
				"mysql_error_type" => "database_connection" 
		);
		echo json_encode ( $data );
	}
	
	$username = $mysqli->escape_string ( $username );
	$key = $mysqli->escape_string ( $key );
	$filename = $mysqli->escape_string ( $filename );
	$result = $mysqli->query ( "INSERT INTO `logs`(`id`, `user`, `key`, `filename`) VALUES (NULL, '" . $username . "', '" . $key . "', '" . $filename . "')" );
	if (! $result) {
		$data = array (
				'mysql_error' => $mysqli->error,
				"mysql_error_type" => "file_logging" 
		);
		echo json_encode ( $data );
	}
	
	$mysqli->close ();
}
function getUsernameFromKey($key, $mysql_host, $mysql_user, $mysql_password, $mysql_database) {
	$mysqli = new mysqli ( $mysql_host, $mysql_user, $mysql_password, $mysql_database );
	if ($mysqli->connection_errno > 0) {
		$data = array (
				'mysql_error' => $mysqli->connect_error,
				"mysql_error_type" => "database_connection" 
		);
		echo json_encode ( $data );
	}
	
	$result = $mysqli->query ( "SELECT * FROM users" );
	if (! $result) {
		$data = array (
				'mysql_error' => $mysqli->error,
				"mysql_error_type" => "user_fetching" 
		);
		echo json_encode ( $data );
	}
	
	$mysqli->close ();
	$username = false;
	while ( $row = $result->fetch_assoc () ) {
		if ($row ['key'] == $key && $row ['enabled'] == 1) {
			$username = $row ['user'];
		}
	}
	
	return $username;
}

$key = $_POST ['key'];
// blocked extension array (add wanted blocked extensions)
$blockedExtensions = array (
		"js",
		"php",
		"php4",
		"php3",
		"phtml",
		"rhtml",
		"html",
		"html",
		"xhtml",
		"jhtml",
		"css",
		"swf" 
);

if (isset ( $key )) {
	if (checkKey ( $key, $mysql_host, $mysql_user, $mysql_password, $mysql_database )) {
		$uploadedFile = $_FILES ["file"];
		$basefilename = basename ( $uploadedFile ["name"] );
		$extension = explode ( ".", $uploadedFile ["name"] );
		$extension = end ( $extension );
		if (checkExtension ( $extension, $blockedExtensions )) {
			$data = array (
					'error' => 'You are not allowed to upload this type of file.' 
			);
			echo json_encode ( $data );
		} else {
			$newfilename = generateRandomFileName () . "." . $extension;
			$target = getcwd () . "/../" . $newfilename;
			if (move_uploaded_file ( $uploadedFile ['tmp_name'], $target )) {
				$userFromKey = getUsernameFromKey ( $key, $mysql_host, $mysql_user, $mysql_password, $mysql_database );
				logFile ( $userFromKey, $key, $newfilename, $mysql_host, $mysql_user, $mysql_password, $mysql_database );
				
				// method sets how it returns data
				// available methods:
				// - json
				// - directlink
				// this defaults to json if it is not set
				
				$method = $_POST ['method'];
				if (! isset ( $method )) {
					$method = "json";
				}
				
				if ($method == "json") {
					$data = array (
							'filename' => $newfilename 
					);
					echo json_encode ( $data );
				} else if ($method == "plaintext") {
					echo $newfilename;
				} else {
					$data = array (
							'error' => "Invalid method." 
					);
					echo json_encode ( $data );
				}
			} else {
				$data = array (
						'error' => 'Sorry, there was a problem uploading your file.' 
				);
				echo json_encode ( $data );
			}
		}
	} else {
		$data = array (
				'error' => 'Your key is incorrect.' 
		);
		echo json_encode ( $data );
	}
} else {
	$data = array (
			'error' => 'You have not specified a key.' 
	);
	echo json_encode ( $data );
}

header_remove ( "Content-Type" ); // not too sure if this works or not, but without this text files don't show up in web browsers.
?>