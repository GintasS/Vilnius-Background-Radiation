<?php		
	require 'functions.php';

	$servername = "";
	$username   = "";
	$password   = "";
	$dbname     = "";
	
	// Accuweather API
	$apiKeyAcu    	   = "";
	$apiLocationKeyAcu = "231459";
	$apiUrl1           = "http://dataservice.accuweather.com/currentconditions/v1/";
	$urlAcu 		   =  $apiUrl1 . "$apiLocationKeyAcu?apikey=$apiKeyAcu&details=true";

	$conn = new mysqli($servername, $username, $password, $dbname);

	// If connection encountered an error, stop execution.
	if ($conn->connect_error)
		die("Connection to MYSQL DB failed: " . $conn->connect_error);
		
	// If mysqli charset is wrong, stop execution.
	if (!$conn->set_charset("utf8")) 
		die("Error loading character set: %s\n" . $conn->error);
		
	echo "Successfully connected to MYSQL DB.<br>";
			
	$rows 				= 0;
	$newestRadiatDate 	= "";
	$oldestRadiatDate 	= "";
	$newestWeatherDate	= "";
	$curDate			= date("Y-m-d");
	
	// Get dates from DB.
	$newestRadiatDate = dbQuery($conn, "", "", 0);
	$oldestRadiatDate = dbQuery($conn, "", "", 0);
	$newestWeatherDate = dbQuery($conn, "", 0);
	
	// If any SQL query failed, stop execution.
	if ($newestRadiatDate === NULL ||
		$oldestRadiatDate === NULL ||
		$newestWeatherDate === NULL) {
		die("No data from DB!<br>");
	}
		
	// If we are up to date or 
	// we don't have new data, exit script.	
	if ($newestWeatherDate === $newestRadiatDate ||  
		$curDate === $newestWeatherDate) 
	{
		if ($curDate === $newestRadiatDate) 
			die("Weather data is up to date!<br>");
		else
			die("No new radiaction data!<br>");
	}
	
	// Get today's weather from Accuweather.
	$data  	  = file_get_contents($urlAcu);
	$jsonData = json_decode($data);

	// If we didn't get any data, stop execution.
	if ($jsonData === NULL)
		die("No JSON data from Accuweather API!<br>");
	
	// Get precipitation of past 12 hours from JSON.
	// Get percipitation date.
	$precipitation = $jsonData[0]->PrecipitationSummary->Past12Hours->Metric->Value;
	$weatherDate   = substr($jsonData[0]->LocalObservationDateTime, 0, 10);
						
	// Validate data.
	if (DateTime::createFromFormat('Y-m-d', $weatherDate) === false || 
		is_numeric($precipitation) === false) {
		die("Corrupt data from Accuweather API!<br>");
	}
	
	// Check for existing data.	
	$result = dbPrepQuery($conn,"",
	array(0), array($weatherDate), 1);
	
	// If no data is found, add it to MYSQL DB.
	$added = "";
	if ($result === NULL)
	{
		$added = dbPrepQuery($conn, "", array(0, 1),
		array($weatherDate, $precipitation), 0) . "<br>";
	}	
	echo "Total weather data added: " . (($added===""?"0":$added)) . "<br>";
	
	$conn->close();	
?>