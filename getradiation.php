<?php
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
	
	/* Url for facebook page.
	   Facebook API access token.
	   Url with parts for while loop.
	*/
	$facebookPage = "VilniausRadiacinisFonas";
	$accessToken  = "";
	$urlPart1  = "https://graph.facebook.com/v3.0/$facebookPage?fields=";
	$url = $urlPart1 . "posts.limit(100)&access_token=" . $accessToken ;	
	// Utility variables.
	$added 	  = 0;
	$lastDate = "";
	$foundData = 0;
	
	// Iterate as long as new data is there.
	while ($foundData === 0)
	{	
		// Get Facebook JSON data.
		$data  = file_get_contents($url);
		$decoded = json_decode($data, true);		
		$foundData = 1;
		
		// Check if FB JSON data is not empty.
		if (isset($decoded['posts']['data']) === false )
			continue;
		
		// Iterate through data.
		foreach ($decoded['posts']['data'] as $item) 
		{
			// If message is empty.
			if (isset($item['message']) === false )
				continue;
			
			$foundData 	 = 0;
			$message 	 = $item['message'];
			$messageDate = substr($item['created_time'], 0, 10);
			$time 		 = "99:99";
			$amount		 = "";
			
			// If we found radiation units in a string, proceed further.
			if (strpos($message, "µSv/h") !== false && 
			strpos($message, "Gedimino paminklas") === false)
			{
				// Check if we have radiation data already.
				$radDate = dbPrepQuery($conn, "",
				array(0), array($messageDate), 1);
				
				// If not, add it to DB.
				if ($radDate === NULL)
				{
					// In case we have a precise time.
					if (strlen($message) === 36)
						$time = substr($message, 0, 5);
					
					// Regex to get radiation data.
					preg_match('/fonas ([0-9]+),([0-9]+)/', trim(substr($message, 0, 
					strrpos($message, "µ", -1))), $matches, PREG_OFFSET_CAPTURE);
					
					// If Regex failed, use another method to find radiation data.
					if (count($matches) !== 0)
						$amount = trim(str_replace("fonas", "", $matches[0][0]));
					else
					{
						$amount = substr($message,strpos($message,"µSv/h")-6, 
						strlen($message) - strpos($message,"µSv/h")-1);
					}					
					$amount = str_replace(",", ".", $amount);
					
					// Insert new record.
					$added += dbPrepQuery($conn, "", array(0, 1, 2),array($messageDate, $time, $amount), 0);
				}			
			}
			// Save last record date for later.
			$lastDate = $messageDate;
		}
		// Convert last date to UNIX timestamp and iteratre 
		// through older posts.
		$dateUnix = strtotime($lastDate);
		$url = $urlPart1 . "posts.until($dateUnix).limit(100)&access_token=$accessToken";	
	}	
	echo "Total radiation data added: " . (($added===0)?"0":$added) . "<br>";
	
	$conn->close();
?>