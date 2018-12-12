<?php
	header('Content-type: text/html; charset=utf-8');	
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
	
	echo "Successfully connected to MYSQL DB.<br>";	
	
	// Select all dates from radiation table.
	$result =  dbQuery($conn, "", "", 1);	
	$urlBase = "http://day.lt/diena/";
	$added = 0;
	
	// For each radiation date, try
	// to find a day length.
	while ($row = $result->fetch_assoc())
	{	
		$radDate =  $row[''];

		// Check for existing records in a day table.
		$result2 = dbQuery($conn, "", "", 1);
		
		if ($result2->num_rows > 0)
			continue;
		
		// Format day date for a web url.
		$date = str_replace("-", ".", $radDate);
		$url  = $urlBase . $date;
		$data = file_get_contents($url);
		$length = "";
				
		$dom = new DOMDocument();
		libxml_use_internal_errors(true);
		$dom->loadHTML($data);
		libxml_use_internal_errors(false);
		
		// Iterate through dom and find day length.
		foreach($dom->getElementsByTagName('td') as $td) 
		{
			$cond = strpos($td->nodeValue, "ilgumas");
			if ($cond !== false)
			{						
				$length = substr($td->nodeValue, $cond + 8, 5);
				break;				
			}
		}
		// Regex validation.
		// String must have numbers and no letters.
		if (preg_match('/[0-9]/', $length) === 0 ||
			preg_match("/[a-z]/i", $length)) {
			continue;
		}
		
		// Get day length in hours.
		$lengthH = ((substr($length,0,1) === "0") ? 
		substr($length, 1, 1): substr($length, 0, 2));
		
		// Total day length in minutes.
		$lengthT = $lengthH * 60 + substr($length, 3, 3);
		
		// Insert new record into day table.
		$added += dbPrepQuery($conn, "", array(0, 1),array($radDate, $lengthT), 0);	
	}
	echo "Total day data added: " . (($added===""?"0":$added)) . "<br>";
	
	$conn->close();	
?>