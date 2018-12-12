<?php	
	/* Function, that queries DB with a simple query.
	   @param object $con - Object, that holds data about current connection.
	   @param string $sql - SQL query.
	   @param string $retData - Column name to return from DB.
	   @param int $mode - Determines what data to return.
	   
	   Possible $mode values:  
	   0 - fetch one row and return it.
	   1 - return an object, that holds all the rows.
	   
	   @return array element or an object.
	*/	
	function dbQuery($con, $sql, $retData, $mode) {
		
		$result = $con->query($sql);
		
		if (!$result)
			echo "Error on query: " . $sql . " data to return: " . $retData . "<br>";

		$data = (($mode === 0) ? $result->fetch_assoc() : "");		
		return (($mode === 0) ? $data[$retData] : $result);		
	}

	/* Function, that queries DB with a prepared statement.
	   @param object $con - Object, that holds data about current connection.
	   @param string $sql - SQL query.
	   @param array $args - Array of indexes for $extraData array.
	   @param array $extraData - Array, that holds actual data to bind.
	   @param int $mode - Determines return mode.
	   
	   Possible $mode values:	   
	   0 - return numeric value, so we
	   could get count of successfully completed queries.
	   1 - instead of numeric value, return result object.
	   
	   @return string or an object.
	*/
	function dbPrepQuery($con, $sql, $args, $extraData, $mode) {
		
		$result = $con->prepare($sql);
		$returnResult = "";
		
		// Prepare arguments.
		$argTypes = str_repeat('s',count($args));
		$tempArray = array();
		
		for ($x = 0; $x < count($args);$x++)
			$tempArray[$x] = $extraData[$args[$x]];
		
		$result->bind_param($argTypes, ...$tempArray);
		
		if (!$result->execute())
		{
			echo "Execute failed: (" . $result->errno . ") " . $result->error . "<br>";
			return (($mode === 0) ? "0":$returnResult);
		}
		
		if ($mode === 1)
		{
			$result->bind_result($returnResult);
			$result->fetch();
		}
		
		$result->close();
		return (($mode === 0) ? "1":$returnResult);		
	}
?>