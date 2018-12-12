function Statistics() {
	this.monthStats = [];
	this.totalStats = [];
	this.analysisStats = [];
}
function externalData() {
	this.addChartData = [];
	this.radiation = [];
	this.weather = [];
	this.sun = [];
	this.day = [];
}

// Statistical data.
var stats = new Statistics();
// Chart data.
var exterData  = new externalData();

/* Gets data from server via AJAX.
   @constructor  
   @param {array} urls - Url array.
   @param {int} index - Url array index.
   
   @return {Void}.  
*/ 
function getDataFromServer(urls, index) {	

	// AJAX request.
	$.ajax({
		type: "GET",
		dataType: "JSON",
		url: urls[index],
		success:function(data) 
		{
			dataFromServer(data, index);
			if (index + 1 <= 4)
				getDataFromServer(urls, index + 1);		
		},
		error: function (jqXHR, exception) 
		{
			radiationError("Error in AJAX request, Status code: " + jqXHR.status + 
			" - " + exception + " " + jqXHR.responseText);
		}	
	});	
}

/* Parses JSON data.
   @constructor  
   @param {json} data - JSON Data from AJAX request.
   @param {int} index - Data index.
   to get.
   
   Possible index values:   
   0 - statistics data.
   1 - weather (precipitation) data.
   2 - solar (Sun) data.
   3 - day length data.
   4 - radiation data.   
   
   @return {Void}.  
*/
function dataFromServer(data, index) {
	
	let tempArray = [];	
	/* Variables for radiation data.
		lastDate determines if we found a new month.
		lastYear determines if we found a new year.
		monthCounter is an index for accessing statistics array.
	*/
	let lastDate = "",
		lastYear = "",
		monthCounter = 1;
	
	if (index === 0)
	{	
		$.each(data, function(i, v) {
			if (v.totalMaxValue) 
			{
				// Total statistics.
				stats.totalStats.push([
					v.totalMaxValue, v.totalMinValue, v.totalMaxDate, v.totalMinDate,
					v.totalMonthMaxDate, v.totalMonthMinDate, v.totalMedian,
					v.totalMode, v.totalRange, v.totalDeviation, v.totalMean,
					v.totalCount
				]);
			}
			else if (v.radiatToPrecip)
			{
				// Analysis statistics.
				stats.analysisStats.push(
					v.radiatToPrecip, v.radiatPrecipStrength,
					v.radiatToSolFlux, v.radiatSolarStrength,
					v.radiatToDayLength, v.radiatDayStrength
				);
			}
		});	
		// Month statistics.
		$.each(data.MonthData, function(j,b) {
			if (b.MaxValue)
			{
				stats.monthStats.push([
					b.MaxValue, b.MinValue, b.MaxDate, b.MinDate,
					b.Median, b.Mode, b.Range, b.Deviation, b.Mean,
					b.Count
				]);	
			}
		});
		// Extra statistics for additional charts.
		for(var key in data.ExtraStatistics)
		{
			tempArray = [];
			for(var keyy in data.ExtraStatistics[key])
			{
				tempArray.push({
				 "date": String(keyy),
				 "value": Number(data.ExtraStatistics[key][keyy])
				});	
			}
			exterData.addChartData.push(tempArray);
		}
	}
	else if (index === 1)
		exterData.weather = readData(data.info.weather_info);
	else if (index === 2)
		exterData.sun = readData(data.info.sun_info);
	else if (index === 3)
		exterData.day = readData(data.info.day_info);
	else if (index === 4)
	{	
		$.each(data.info.radiation_info, function(i, v) 
		{	
			// If new month has been found.			
			if ((tempArray.length !== 0 && v.Date.substr(5,2) !== lastDate.substr(5,2)) || 
				i + 1 === data.info.radiation_info.length) 
			{
				
				// If we are at the last data point, add it before adding a new
				// month.
				if (i + 1 === data.info.radiation_info.length) 
				{
					tempArray.push({
						date:  moment().format(v.Date),
						value: parseFloat(v.Amount) 			
					});
				}
				// Chart name that consists of year and a month name.
				let name = lastDate.substr(0,4) + " " + moment(lastDate).format('MMMM') + " statistics";		
				appendChart("#" + lastYear + "COL", "month" + monthCounter, name , 0, 1);
				
				// Reverse data so it could be shown in chart.
				exterData.radiation = exterData.radiation.concat(tempArray);
				tempArray = tempArray.reverse();
				
				// Add click event to save website loading time.
				$("#month" + monthCounter + "BTN").click({param1: "month" + monthCounter,
				param2: tempArray, param3: "#month" + monthCounter + "BTN", param4: 0,
				param5: "Days", param6: "µSv/h"}, onColllapseClick);
				
				// Append table with statistics.
				appendStats("#month" + monthCounter + "COL", stats.monthStats[monthCounter - 1], 0);
				monthCounter++;
				tempArray = [];
			}
			/* Add data to a temporary array,
			   that will be reset after a new month
			   is found.
			*/
			tempArray.push({
				date:  moment().format(v.Date),
				value: parseFloat(v.Amount) 			
			});
			
			// All the data above is appended to a year div, we group months by their year
			// so we could save webpage space.
			if (i === 0 || ( i !== 0 && v.Date.substr(0,4) !== lastDate.substr(0,4)))
			{
				if (i !== 0)
					$("#"+lastYear+"COL").append("<br><br>");
				
				lastYear = v.Date.substr(0, 4);
				appendYearButton(".dropdown-menu", "#monthStats", lastYear, lastYear);
			}
			lastDate = v.Date;
		});

		exterData.radiation  = exterData.radiation.reverse();
		
		// Add click event for main month chart.
		$("#mainChartBTN").click({param1: "chartMain",
		param2: exterData.radiation, param3:"#mainChartBTN", param4: 1,
		param5: "Days", param6:"µSv/h"}, onColllapseClick);
		
		appendStats("#chartMainStats", stats.totalStats[0], 1);
		
		analysis();
		analysisAddCharts();
		extraCharts();
		dropdownClick();
	}
}
/* Read JSON data, that we only need to store.
   @constructor  
   @param {string} path - JSON Object path that we can iterate through.
   
   @return {Void}.  
*/
function readData(path) {
	
	let tempArray = [];
	$.each(path, function(i, v) 
	{				
		tempArray.push({
			date: moment().format(v.Date),
			value: v.Amount
		});
	});
	return tempArray;
}
/* Function for analysis section.
   @constructor  

   @returns {Void}.  
*/
function analysis() {
	
	let loopLength = exterData.radiation.length;
	
	// Determine loop length based on highest difference between
	// all the data.
	if (exterData.radiation.length !== exterData.weather.length ||
		exterData.radiation.length !== exterData.sun.length || 
		exterData.radiation.length !== exterData.day.length)
	{
		loopLength = exterData.radiation.length - Math.max(
			Math.abs(exterData.radiation.length - exterData.weather.length),
			Math.abs(exterData.radiation.length - exterData.sun.length),
			Math.abs(exterData.radiation.length - exterData.day.length)
		);
	}
	let tempArray = [];
	
	// Add values to temp array for multiple axes chart.
	for (i = 0; i < loopLength;i++)
	{
		tempArray.push({
			date: moment().format(exterData.radiation[i].date),
			value1: exterData.radiation[i].value,
			value2: exterData.weather[i].value,
			value3: exterData.sun[i].value,
			value4: exterData.day[i].value
		});			
	}
	
	// Analysis main chart.
	$("#analysisMainBTN").click({param1: "analysisMain",
	param2: tempArray, param3: "#analysisMainBTN", param4: 4
	}, onColllapseClick);	
}

/* Function for additional analysis charts.
   @constructor  

   @return {Void}.  
*/
function analysisAddCharts() {
	
	let analysisData = [
		[ "Days", "µSv/h"],
		[ "Days", "mm" ],
		[ "Days", "W*m-2*Hz"],
		[ "Days", "min"]
	];
 		
	for (i = 1,y = 0; i <= 3;i++,y+=2)
	{
		$("#analysisMain"+i+"BTN").click({
			param1: "analysisMainChart" + i,
			param2: Number(stats.analysisStats[y]), 
			param3: "#analysisMain" + i +"BTN", 
			param4: 5,
		}, onColllapseClick);
		
		$("#analysisStrength" + i).append(stats.analysisStats[y + 1]);
	}
	
	/* Additional analysis charts:
	  i = 1 => radiation,
	  i = 2 => weather,
	  i = 3 => Sun data,
	  i = 4 => day length.
	*/
	for (i = 1; i <= 4;i++)
	{
		let tempArray = [];
		
		if (i === 1)
			tempArray = exterData.radiation;
		else if (i === 2)
			tempArray = exterData.weather;
		else if (i === 3)
			tempArray = exterData.sun;
		else if (i === 4)
			tempArray = exterData.day;

		$("#analysis" + i +"BTN").click({param1: "analysis" + i,
		param2: tempArray, param3: "#analysis" +i +"BTN", param4: 1,
		param5: analysisData[i - 1][0], param6:analysisData[i - 1][1]}, onColllapseClick);		
		
	}	
}

/* Function for additional statistics charts.
   @constructor  

   @return {Void}.  
*/
function extraCharts() {
	
	let extraChartData = [
		[ "#addChart1BTN" , "addChart1" , "Time interval (hours)" , "Count", 2 ],
		[ "#addChart2BTN" , "addChart2" , "Mean (µSv/h)" , "Weekday", 3 ],		
		[ "#addChart3BTN" , "addChart3" , "Weekday" , "Count", 2],		
		[ "#addChart4BTN" , "addChart4" , "Value" , "Count" , 2],		
		[ "#addChart5BTN" , "addChart5" , "Month" , "Mean (µSv/h)", 1 ],
		[ "#addChart6BTN" , "addChart6" , "Month" , "Count", 1 ],
		[ "#addChart7BTN" , "addChart7" , "Month" , "Value", 1 ],
		[ "#addChart8BTN" , "addChart8" , "Month" , "Value", 1 ]		
	];
	
	for (i = 0; i < 8;i++)
	{
		$(extraChartData[i][0]).click({
		param1: extraChartData[i][1], 
		param2: exterData.addChartData[i], 
		param3: extraChartData[i][0], 
		param4: extraChartData[i][4], 
		param5: extraChartData[i][2], 
		param6: extraChartData[i][3] 
		}, onColllapseClick);	
	}
}
/* Function for throwing an error;
   @constructor  
   @param {string} msg - Message to display with error.
   
   @return {Void}.  
*/
function radiationError(msg) {
	
	throw new Error(msg);
}