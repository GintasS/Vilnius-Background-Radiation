<?php 
	header('Content-Type: application/json');	
	require 'mathfunctions.php';
	require 'functions.php';
	require 'extrastats.php';
	
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
	
	// Get all the data from DB.
	$result = dbQuery($conn, "", "", 1);
	
	// Arrays, that save radiation dates and amounts.
	$dataValues = array();
	$dataDates	= array();
	
	/* Arrays for extra statistics(additional charts).
		$exStatsMeasTime - "Measurement distribution by day interval".
		
		$exStatsAvgCombo - "Mean radiation for every weekday",
						   "Measurement distribution for every weekday".
						   
		$exStatsFrequency - "Radiation value frequency".
		
		$exStatsAvgMonth - "Mean radiation for every month",
						   "Measurement count by month".
							
		$exStatsMinMaxMonth - "Highest radiation by month",
							  "Lowest radiation by month".
	*/	
	$exStatsMeasTime =  array_fill (0, 8, 0);
	$exStatsAvgCombo = array(
		array_fill (0, 7, 0);,
		array_fill (0, 7, 0);
	);
	
	$exStatsFrequency = array();
	$exStatsAvgMonth = array();
	$exStatsMinMaxMonth = array();
		
	/* Arrays for saving month data.
	   $monthDataValues - radiation amounts.
	   $monthDataDates - radiation dates.
	   
	   $monthDataSumValues - month sums.
	   $monthDataSumDates - month dates.
	   
	   $monthDataMeanValues - month means.
	   
	   $monthDataTemp - temporary array for values.
	*/	
	$monthDataValues     = array();
	$monthDataDates      = array();
	$monthDataSumValues  = array();
	$monthDataSumDates   = array();
	$monthDataMeanValues = array();
	$monthDataTemp		 = array();
	
	// 'Analysis' section data. 
	$analysisData = array_fill(0,18,0);
	
	$jsonData = array();
	
	// Standart deviations.
	$standDeviation = 0;
	$monthDeviation = 0;
	
	// Loop count.
	$firstCount = 0;
	
	// Last date, used to determine 
	// if we found a new month or not.
	$lastDate = "";

	// Get values from DB and
	// calculate mean for every month.	
	while($row = $result->fetch_assoc()) 
	{			
		$dataValues[] = $row[""];
		$dataDates[]  = $row[""];

		// If new month is found.
		if (($firstCount !== 0 && substr($row[""], 5, 2) !== substr($lastDate, 5, 2)) || 
			$firstCount + 1 === $result->num_rows ) {
				
			if ($firstCount + 1 === $result->num_rows)
				$monthDataTemp[] = $row[""];
		
			$monthDataMeanValues[] = array_sum($monthDataTemp) / count($monthDataTemp);
			$monthDataTemp = array();
		}
		$monthDataTemp[] = $row[""];
		$lastDate 		 = $row[""];
		$firstCount++; 
						
		// We call extra statistics functions in order
		// to save some computation.
		$exStatsMeasTime = getMeasurTime($exStatsMeasTime, $row[""]);		
		$exStatsAvgCombo = meanRadByWDay($exStatsAvgCombo[0], $exStatsAvgCombo[1],
		$row[""], $row[""], 0);	
		$exStatsFrequency = valueFrequency($exStatsFrequency, $row[""], 0);
		
		$analysisData = correlation($analysisData, $row[""],
		$row[""], $row[''], $row[''], $result->num_rows, 0);
	}

	$lastDate  = "";
	// Mean, used to calculate standart deviation for all the radiation values.
	$totalMean = array_sum($dataValues) / $result->num_rows;
	
	// Loop again through the values, but now calculate month values.
	// $x index is for radiation values,
	// $y is for month mean values.
	for ($x = 0, $y = 0; $x < $result->num_rows;$x++)
	{
		// Below if is for a new month, that has been detected.
		if (($x !== 0 && substr($dataDates[$x], 5, 2) !== substr($lastDate, 5, 2)) || 
			$x + 1 === $result->num_rows) 
		{
			
			if ($x + 1 === $result->num_rows)
			{
				$monthDataValues[] = $dataValues[$x];
				$monthDataDates[]  = $dataDates[$x];	
				$monthDeviation += pow($dataValues[$x] - $monthDataMeanValues[$y], 2);
			}
		
			// JSON data for x'th month.
			$jsonData["MonthData"][] = statistics($monthDataValues,
			$monthDataDates, $monthDeviation, "", "", 0);
			
			$monthSum = array_sum($monthDataValues);

			// Extra statistics
			$exStatsAvgMonth = meanRadByMonth($exStatsAvgMonth, $monthSum,
			$lastDate, count($monthDataValues), 0);
			
			$exStatsMinMaxMonth = minMaxRadByMonth($exStatsMinMaxMonth,
			min($monthDataValues), max($monthDataValues), $lastDate, 0);

			// Save x'th month data.
			$monthDataSumValues[] = $monthSum;
			$monthDataSumDates[]  = $lastDate;
			
			// Reset arrays for a new month.
			$monthDataValues = array();
			$monthDataDates  = array();
			$monthDeviation = 0;
			$y++;
		}
		
		// Add values for x'th month.
		$monthDataValues[] = $dataValues[$x];
		$monthDataDates[]  = $dataDates[$x];
		
		$lastDate = $dataDates[$x];
			
		// Calculate deviations
		$standDeviation += pow($dataValues[$x] - $totalMean, 2);	
		$monthDeviation += pow($dataValues[$x] - $monthDataMeanValues[$y], 2);		
	}

	
	// Get analysis data.
	$jsonData["Analysis"] = correlationStats(
		correlation($analysisData, "", "", "", "", $result->num_rows, 1)
	);
	
	// Get extra statistics.
	$jsonData["ExtraStatistics"] = extraStatistics(
		$exStatsMeasTime,
		meanRadByWDay($exStatsAvgCombo[0], $exStatsAvgCombo[1], "", "", 1),
		valueFrequency($exStatsFrequency, "", 1),
		meanRadByMonth($exStatsAvgMonth, "", "", "", 1),
		minMaxRadByMonth($exStatsMinMaxMonth, "", "", "", 1)
	);
	
	// Get total statistics.
	$jsonData["Total"] = statistics($dataValues,
		$dataDates, $standDeviation, $monthDataSumValues, $monthDataSumDates,1
	);
	
	// Print all the JSON data.
	echo json_encode($jsonData, JSON_PRETTY_PRINT);	
	
	$conn->close();
?>