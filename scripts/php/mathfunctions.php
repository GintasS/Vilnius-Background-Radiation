<?php
	/* Function, that gets mode(statistics).
	   @param array $values - Array, that holds all the data for a particular
	   time period.
	   
	   @return string.
	*/
	function getAllModes($values) {
		
		// Count value in array.
		$values1 = array_count_values($values);
		// Sort values in reverse order. 
		arsort($values1);
		// Get first most frequent value.
		$popular = array_slice(array_keys($values1), 0, count($values), true);
		
		$mode = $popular[0] . " (" . $values1[$popular[0]] .")";
		
		// In statistics, it's possibile for two or more values to be the
		// most frequent ones, so find all the values.
		for ($x = 1; $x < count($values);$x++)
		{
			if ($values1[$popular[0]] === $values1[$popular[$x]])
				$mode = $mode . " " . "<br>" . " " . $popular[$x] . " (" . $values1[$popular[$x]] .")";
		}
		
		return $mode;
	}
	
	/* Function, that calculates correlation between:
	   radiation and precipitation,
	   radiation and solar flux,
	   radiation and day length.
	   @param array $corData - Array, that holds all the correlation values.
	   @param float $radValue - Current radiation value.
       @param double $weatValue - Current weather (precipitation) value.
	   @param float $sunValue - Current sun (solar flux) value.
       @param int $dayValue - Current day length value.
       @param int $dataCount - Total count of all the values.
	   @param int $mode - Determines if we are in calculation mode or in final mode.
	   
	   Possible $mode values:   
	   0 - calculation mode.
	   1 - final mode: divide values and return them.
	   
	   @return array.
	*/
	function correlation($corData, $radValue, $weatValue, $sunValue, $dayValue, $dataCount, $mode) {
		
		$corData[0] = (($mode === 0) ? ($corData[0] + ($radValue * $weatValue)):($corData[0] /$dataCount ));
		$corData[1] = (($mode === 0) ? ($corData[1] + ($radValue * $sunValue)):($corData[1] / $dataCount));
		$corData[2] = (($mode === 0) ? ($corData[2] + ($radValue * $dayValue)):($corData[2] / $dataCount));
		
		$corData[3] = (($mode === 0) ? ($corData[3] + $radValue):($corData[3] / $dataCount)) ;
		$corData[4] = (($mode === 0) ? ($corData[4] + $weatValue):($corData[4] / $dataCount));
		$corData[5] = (($mode === 0) ? ($corData[5] + $sunValue):($corData[5] / $dataCount));
		$corData[6] = (($mode === 0) ? ($corData[6] + $dayValue):($corData[6] / $dataCount));
		
		$corData[7] = (($mode === 0) ? ($corData[7] + ($radValue *  $radValue)):($corData[7] / $dataCount));
		$corData[8] = (($mode === 0) ? ($corData[8] + ($weatValue * $weatValue)):($corData[8] / $dataCount));
		$corData[9] = (($mode === 0) ? ($corData[9] + ($sunValue * $sunValue)):($corData[9] / $dataCount));
		$corData[10] = (($mode === 0) ? ($corData[10] + ($dayValue * $dayValue)):($corData[10] / $dataCount));
				
		for($x = 11; $x < 15 && $mode === 1;$x++)
			$corData[$x] = $corData[$x - 8] * $corData[$x - 8];
				
		for ($x = 15; $x < 18 && $mode === 1;$x++)
		{
			$numerator = $corData[$x - 15] - ($corData[3] *  $corData[$x - 11]);
			$denumerator = sqrt($corData[7] - $corData[11]) *  sqrt($corData[$x - 7] - $corData[$x-3]);
			$corData[$x] = ( $numerator / $denumerator ) * 100;
		}
			
		return (($mode === 0) ? $corData: 
		array($corData[15], $corData[16], $corData[17]));
	}
	
	/* Function, that returns correlation strength by
	   value.
	   @param $double $value - correlation value.
	   
	   @return string.
	*/
	function correlationStrength($value) {
		
		switch ($value) {
			case abs($value) === 0:
				return "no relation";
			case abs($value) > 0 && abs($value) < 20:
				return "very weak";
			case abs($value) >= 20 && abs($value) < 40:
				return "weak";
			case abs($value) >= 40 && abs($value) < 60:
				return "medium";
			case abs($value) >= 60 && abs($value) < 80:
				return "strong";				
			case abs($value) >= 80 && abs($value) <= 100:
				return "very strong";								
			default:
				return "no relation";
		}
	}
	
	/* Function, that both finds and prints statistics data.
		@param array $values - Array, that holds all the radiation data.
		@param array $dates  - Array, that holds all the radiation dates.
		@param double $deviation - Standart deviation.
		@param array $monthValues - Array, that holds min/max radiation values for every month.
		@param array $monthDates - Array, that holds full dates(YYYY-MM-DD) for every month.
		@param int $mode - Variable, that determines print mode(0 - month, 1 - total data).
		
		@return object.
	*/	
	function statistics($values, $dates, $deviation, $monthValues, $monthDates, $mode) {	
	
		$modeS = getAllModes($values);		
		$dataObject = [];
		
		// Lowest month value.
		$dataObject[(($mode === 0) ? "MinValue":"totalMinValue")] = min($values);
		// Date for lowest month value.
		$dataObject[(($mode === 0) ? "MinDate":"totalMinDate")] = 
		$dates[array_keys($values, min($values))[0]];		
		
		// Highest month value.
		$dataObject[(($mode === 0) ? "MaxValue":"totalMaxValue")] = max($values);
		// Date for highest month value.
		$dataObject[(($mode === 0) ? "MaxDate":"totalMaxDate")] = 
		$dates[array_keys($values, max($values))[0]];
		
		// Highest/lowest month.
		if ($mode === 1)
		{
			$dataObject["totalMonthMinDate"] = date('F, Y', 
			strtotime($monthDates[array_search(min($monthValues), $monthValues)]));
		
			$dataObject["totalMonthMaxDate"] = date('F, Y', 
			strtotime($monthDates[array_search(max($monthValues), $monthValues)]));			
		}
		
		// Standart deviation.
		$dataObject[(($mode === 0) ? "Deviation":"totalDeviation")] = 
		(string)round(sqrt($deviation / (count($values) - 1)), 3);
		
		// Mean value.
		$dataObject[(($mode === 0) ? "Mean":"totalMean")] = 
		(string)round((array_sum($values) / count($values)), 3);
		
		sort($values); 
		
		// Median value.
		$dataObject[(($mode === 0) ? "Median":"totalMedian")] = 
		((count($values) % 2 === 0) ?  ( round(($values[count($values) / 2] + 
		$values[(count($values) / 2) - 1]) / 2, 3)):$values[count($values) / 2]);
			
		// Mode, range and measurement count.		
		$dataObject[(($mode === 0) ? "Mode":"totalMode")] = $modeS;
		$dataObject[(($mode === 0) ? "Range":"totalRange")] = (string)(max($values) - min($values));
		$dataObject[(($mode === 0) ? "Count":"totalCount")] = (string)count($values);
		
		return $dataObject;			
	}
	/* Function, that finds and prints extra statistics for additional charts.
	   @param array $measurTime - Array for the first chart.
	   @param array $averageByWeekD - Array for 2nd and 3rd charts.
	   @param array $frequency - Array for 4th chart.
	   @param array $averageRadByMonth - Array for 5th and 6th charts.
	   @param array $maxMinValues - Array for 7th and 8th charts.
	   
	   @return object.
	*/		
	function extraStatistics($measurTime, $averageByWeekD, $frequency, $averageRadByMonth, $maxMinValues) {	
	
		$dataObject = [];
		// Arrays for time intervals and weekday names.
		$measurKeys = array( "00-03", "03-06", "06-09",
		"09-12", "12-15", "15-18", "18-21", "21-24");
		
		$averageWeekDays = array('Sunday', 'Monday', 
		'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday');
		
		// (1) Measurements by day time.
		for ($x = 0; $x < count($measurTime);$x++)
			$dataObject["MeasurTime"][$measurKeys[$x]] = (string)$measurTime[$x];
		
		// (2) Average radiation by week day.
		// (3) Measurement count by week day.
		for ($x = 0; $x < count($averageByWeekD[0]);$x++)
		{
			$dataObject["AverageRadByDay"][$averageWeekDays[$x]]  = (string)$averageByWeekD[1][$x];
			$dataObject["MeasurCountByDay"][$averageWeekDays[$x]] = (string)$averageByWeekD[0][$x];
		}	
		// (4) Value frequency.
		foreach ($frequency as $key => $value) 
			$dataObject["Frequency"][$key] = (string)$value;
		
		// (5) Average radiation by month.
		foreach ($averageRadByMonth[0] as $key => $value)
			$dataObject["AverageRadByMonth"][$key] = (string)round($value,3);
		
		// (6) Measurement count by month.
		foreach ($averageRadByMonth[1] as $key => $value)
			$dataObject["MeasurCountByMonth"][$key] = (string)$value;
		
		// (7) Lowest radiation for every month.
		foreach ($maxMinValues[0] as $key => $value)
			$dataObject["MinValueByMonth"][$key] = (string)$value;
		
		// (8) Highest radiation for every month.
		foreach ($maxMinValues[1] as $key => $value)
			$dataObject["MaxValueByMonth"][$key] = (string)$value;
			
		return $dataObject;
	}
	
	/* Function, that sets correlation values to a JSON.
	   @param array $corData - correlation values.	
		
	   @return array.
	*/
	function correlationStats($corData) {
		
		$dataObject = [];
		
		$dataObject["radiatToPrecip"]       = (string)$corData[0];
		$dataObject["radiatPrecipStrength"] = correlationStrength($corData[0]);
		$dataObject["radiatToSolFlux"]      = (string)$corData[1];
		$dataObject["radiatSolarStrength"]  = correlationStrength($corData[1]);		
		$dataObject["radiatToDayLength"]    = (string)$corData[2];
		$dataObject["radiatDayStrength"]    = correlationStrength($corData[2]);
		
		return $dataObject;		
	}
?>