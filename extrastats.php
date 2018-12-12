<?php
	/* Function, that finds a time interval for a 
	   timestamp(HH:II).
	   @param array $values - Array, that holds results for every interval.
	   @param string $time - A timestamp for whom to find a time interval.
	   
	   @return array.
	*/	
	function getMeasurTime($values, $time) {
		
		if ($time !== "99:99")
		{
			for ($y = 0, $a = 0; $y < 8;$y++, $a+=3)
				$values[$y] += compareTime($time, $a, $a + 3);							
		}
			
		return $values;
	}
	
	/* Function, that compares a timestamp between two
	   intervals.
	   @param string $value - Timestamp.
	   @param int $int1  - Lower bound.
	   @param int $int2  - Higher bound.
	   
	   @return int.
	*/	
	function compareTime($value, $int1, $int2) {
		
		$number = 0;
		
		if (substr($value, 0, 1) === "0")
			$number = intval(substr($value, 1, 1));
		else if (substr($value, 0, 1) !== "0")
			$number = intval(substr($value, 0, 2));
				
		if ($number >= $int1 && $number < $int2)
			return 1;

		return 0;
	}
	
	/* Function, that calculates mean value for every weekday.
	   @param array $weekdays - Array, that holds weekday count for every value.
	   @param array $weekdaysValues - Array, that holds a sum of 
	   radiation values for a particular weekday.
	   @param float $radValue - Radiation value for which to find a weekday.
	   @param date $radDate  - Date that corresponds to a radiation value.
	   @param int $mode - Variable, that controls sum/divide actions.
	   
	   Possible $mode values:	   
	   0 - add value to array
	   1 - divide the sum of a weekday by weekday value count.
	   
	   @return array.
	*/		
	function meanRadByWDay($weekdays, $weekdaysValues, $radValue, $radDate, $mode) {
		
		if ($mode === 0)
		{
			$weekdays[date('w', strtotime($radDate))]++;
			$weekdaysValues[date('w', strtotime($radDate))] += $radValue;
		}
		else if ($mode === 1)
		{
			for ($y = 0; $y < count($weekdays);$y++)
				$weekdaysValues[$y] = number_format(round($weekdaysValues[$y] / $weekdays[$y], 3), 3, '.', '');
		}

		return array($weekdays, $weekdaysValues);
	}
	
	/* Function, that calculates value frequencies.
	   @param array $frequency - Array, that holds all the frequencies.
	   @param float $radValue - Current radiaton value.
	   @param int $mode - Determine if we need to sort the array 
	   or save the value.
	   
	   Possible $mode values:	   
	   0 - save value to a $frequency array.
	   1 - sort the $frequency array.
	   
	   @return array.
	*/		
	function valueFrequency($frequency, $radValue, $mode) {
		
		if ($mode === 0)
		{
			/* For a first value, put it inside array.
			   If the key does not exist, add it.
			   If it exists, add one to current number.
			*/
			if (count($frequency) === 0)
				$frequency[$radValue] = 1;		
			else if (!array_key_exists($radValue, $frequency))
				$frequency[$radValue] = 1;
			else
				$frequency[$radValue]++;
		}		
		else if ($mode === 1)
			ksort($frequency);
		
		return $frequency;
	}
	
	/* Function, that calculates mean value for a current month.
	   @param array $values - Array, that holds all the mean values.
	   @param float $radValue - Sum of all radiation values for a current month
	   @param date $radDate  - Month date in (YYYY-MM-DD).
	   @param int $monthCount - Measurements for current month.
	   @param int $mode - Determines save/return mode.
	   
	   Possible $mode values:   
	   0 - save both the mean and the measurement count and 
		   return updated array.
	   1 - return reversed array.
	   
	   @return array.
	*/
	function meanRadByMonth($values, $radValue, $radDate, $monthCount, $mode) {
		
		if ($mode === 0)
		{
			$values[0][$radDate] = $radValue / $monthCount;
			$values[1][$radDate] = $monthCount;
		}		

		return (($mode === 0) ? $values:
		array(array_reverse($values[0]), array_reverse($values[1])));
	}
	
	/* Function, that calculates lowest and highest radiation value for a 
	   particular month.
	   @param array $values - Array, that holds min/max values for every month.
	   @param float $radValue1 - Lowest month value.
	   @param float $radValue2 - Highest month value.
	   @param date $radDate - Month date in (YYYY-MM-DD).
	   @param $mode - Determines save/read mode.
	   
	   Possible $mode values:
	   
	   0 - save value to an array and 
		   return updated array.
	   1 - return reversed array.
	   
	   @return array.
	*/
	function minMaxRadByMonth($values, $radValue1, $radValue2, $radDate, $mode) {
		
		if ($mode === 0)
		{
			$values[0][$radDate] = $radValue1;
			$values[1][$radDate] = $radValue2;
		}
		
		return (($mode === 0) ? $values:
		array(array_reverse($values[0]), array_reverse($values[1])));		
	}
?>