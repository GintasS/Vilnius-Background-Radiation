<?php 
	header('Content-Type: application/json');
	require 'functions.php';
	
	$servername = "";
	$username   = "";
	$password   = "";
	$dbname     = "";
	
	$conn = new mysqli($servername, $username, $password, $dbname);
	
	// If connection encountered an error, stop execution.
	if ($conn->connect_error) 
		die("Connection to MYSQL DB failed: " . $conn->connect_error);

	// If mysqli charset is wrong, stop execution.
	if (!$conn->set_charset("utf8")) 
		die("Error loading character set: %s\n" . $conn->error);
	
	// SQL query.
	$result = dbQuery($conn,"", "", 1);
	
	// Array, that will hold our JSON data.
	$jsonData = array();
	
	// Iterate through DB data.
	while($row = $result->fetch_assoc()) 
	{			
		$jsonData["info"]["radiation_info"][] =  array(
			"Date" => $row[""],
			"Amount" => $row[""]
		);
	}
	echo json_encode($jsonData, JSON_PRETTY_PRINT);
	
	$conn->close();
?>