<?php
	header('Content-type: text/html; charset=utf-8');		
	require 'functions.php';
	
	$servername = "";
	$username   = "";
	$password   = "";
	$dbname     = "";
	$url 		= "ftp://ftp.geolab.nrcan.gc.ca/data/solar_flux/daily_flux_values/fluxtable.txt";

	$conn = new mysqli($servername, $username, $password, $dbname);
	
	// If connection encountered an error, stop execution.
	if ($conn->connect_error)
		die("Connection to MYSQL DB failed: " . $conn->connect_error);
	
	// If mysqli charset is wrong, stop execution.
	if (!$conn->set_charset("utf8")) 
		die("Error loading character set: %s\n" . $conn->error);
	
	echo "Successfully connected to MYSQL DB.<br>";	
	
	// Curl instead of file_get_contents for FTP usage.
	$curl = curl_init(); 
	curl_setopt($curl, CURLOPT_URL,$url); 
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); 
	curl_setopt($curl, CURLOPT_TIMEOUT, 300); 
	curl_setopt($curl, CURLOPT_VERBOSE, 1);
	curl_setopt($curl, CURLOPT_FTP_USE_EPSV, 0);
	
	// Execute curl and get data.
	$info   = curl_exec($curl); 
	$status = curl_getinfo($curl); 
	curl_close($curl);  
	
	// Write all the data to .txt file, for storage.
	$myfile = fopen("", "w") or die("Unable to open file!");
	fwrite($myfile, $info);
	
	// Explode data by a new line.
	$dataArray = explode(PHP_EOL, $info);
	
	// Start from 2016-07-03 (12743), date, then first
	// radiation data was added.
	$added = 0;
	for ($x = 12743; $x < count($dataArray);$x++)
	{	
		// Explode a line by double space character.
		// Format data, instead of YYYYMMDD make to YYYY-MM-DD.
		$valueArray = explode("  ", $dataArray[$x]);
		$valueArray[0] = substr($valueArray[0], 0, 4) . "-" .  
		substr($valueArray[0], 4, 2) . "-" . substr($valueArray[0], 6, 2);
				
		// If we have data already in sun DB, do not add it again.
		$sunDate = dbPrepQuery($conn, "",
		array(0), $valueArray, 1);
						
		if ($sunDate === NULL)
		{
			// If we have radiation data for a particular date, but we don't for sun data
			// insert a new record into sun DB.
			$radDate = dbPrepQuery($conn,"",		
			array(0), $valueArray, 1);
														
			if ($radDate !== NULL)
			{
				// Trim all elements of an array.
				$valueArray = array_map('trim', $valueArray);
								
				// Edge case for dates: 2016-07-15 & 2016-10-11
				$index = 0;
				if (strlen($valueArray[10]) === 0 && strlen($valueArray[9]) !== 0)
					$index--; 
				
				// Convert to a float value.
				$valueArray[10 + $index] = str_replace(".", "", $valueArray[10 + $index]);
				$valueArray[10 + $index] = substr($valueArray[10 + $index], 0, 1) . "." . 
				substr($valueArray[10 + $index], 1, strlen($valueArray[10 + $index]));
				
				// Insert a new record.
				$added += dbPrepQuery($conn, "", array(0, 2, 5, 6,
				10 + $index, 13 + $index, 16 + $index), $valueArray, 0);
			}
		}	
	}	
	echo "Total data added: " . (($added==="")?"0":$added) . "<br>"; 
	
	$conn->close();	
?>		