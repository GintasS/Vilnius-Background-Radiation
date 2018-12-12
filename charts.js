/* Function, that appends statistics table to the body.
   @constructor
   @param {string} targetDiv - DOM element to write chart in.
   @param {array} data - Data to fill into the table.
   @param {int} mode - Determine if we are appending month
   statistics or total statistics.
   
   Possible mode values:
   * Month statistics - 0 or any other value.
   * Total statistics - 1.
   
   @return {Void}.
*/
function appendStats(targetDiv, data, mode) {
		
	// Multi dimentional array for table:
	// First column: <td> name,
	// Second column: data extension.
	
	let tableData = [
		["Highest radiation:", " µSv/h"],
		["Lowest radiation:", " µSv/h"],
		["Most radioactive day:", ""],
		["Least radioactive day:",""],
		["Median:", " µSv/h"],
		["Mode:", ""],
		["Range:", " µSv/h"],
		["Standart deviation:", " µSv/h"],
		["Mean:", " µSv/h"],
		["Days:", ""]
	];
	
	// Extra multi dimentional array for
	// total statistics.
	let extraTableData = [
		["Most radioactive month:", ""],
		["Least radioactive month:", ""]
	];
	
	// HTML table structure.
	let tableString = "<br> \
	<table class='table'> \
	<tbody>";
	
	let index = ((mode === 1) ? 2:0),
		loopIndex = 0;
	
	for (let i = 0; i < 9;i+=2)
	{	
		if (i === 4)
			loopIndex = index;
				
		tableString += 
		"<tr> \
			<td class='col-md-3'>" + tableData[i][0] + "</td> \
			<td class='col-md-3'>" + data[i + loopIndex] + tableData[i][1] + "</td> \
			<td class='col-md-3'>" + tableData[i + 1][0] + "</td> \
			<td class='col-md-3'>" + data[i + loopIndex + 1] + tableData[i + 1][1] + "</td> \
		</tr>";
		
		// Total statistics.
		if (i === 2 && mode === 1)
		{
			tableString += 
			"<tr> \
				<td class='col-md-3'>" + extraTableData[0][0] + "</td> \
				<td class='col-md-3'>" + data[4] + extraTableData[0][1] + "</td> \
				<td class='col-md-3'>" + extraTableData[1][0] + "</td> \
				<td class='col-md-3'>" + data[5] + extraTableData[1][1] + "</td> \
			</tr>";						
		}			
	}
	
	// Append to DOM.
	$(targetDiv).append(tableString);
}
/* Function, that appends chart to body.
   @constructor
   @param {string} targetDiv - DOM element to write chart in.
   @param {string} chartDiv - Chart id.
   @param {string} chartName - Chart title.
   @param {int} mode - Determine if the chart needs to be loaded
   on click event or not.
   
   Possible mode values:
   * 0 - chart is going to load on click event.
   * 1 - chart is loaded on document ready.
		  	  
   @param {int} nameMode - Determine if chart needs a title.

   Possible nameMode values:
   * 0 - needs a title.
   * 1 - does not need a title 

   @return {Void} .
*/
function appendChart(targetDiv, chartDiv, chartName, mode, nameMode) {
	
	if (nameMode === 1)
		appendChartName(targetDiv,chartName);
	
	if (mode === 0)
	{
		$(targetDiv).append(
		"<button class='col' id = '" + chartDiv + "BTN' \
		data-toggle='collapse' data-target='#" + chartDiv + "COL'>Press here to open.\
		<i class='fa fa-line-chart' aria-hidden='true'></i> \
		<i class='fa fa-table' aria-hidden='true'></i> \
		</button> \
		<div id='" + chartDiv + "COL' class='collapse'> \
			<div id='" + chartDiv + "'></div><br> \
		</div><br>"	
		);
	}
	else if (mode === 1)
		$(targetDiv).append("<div id='" + chartDiv + "'></div>");			
}
/* Function, that appends year options to our single dropdown.
   @constructor
   @param {string} targetDiv - DOM element to add year option to.
   @param {string} targetDiv2 - DOM element to add charts to.
   @param {string} buttonName - DOM element's ID name, on whose click chart will show up.
   @param {string} buttonText - DOM element's inner text.
   
   @return {Void}.
*/
function appendYearButton(targetDiv, targetDiv2, buttonName, buttonText) {
	
	$(targetDiv).append(
	"<li id='" + buttonName +"BTN' class='years'><a  \
	data-toggle='collapse' data-target='#" + buttonName  + "COL'><center>" + buttonText + " \
	</center></a></li>"
	);
	
	$(targetDiv2).append(
		"<div id='" + buttonName +"COL' class='collapse'> \
		<br></div>"
	);		
}

/* Function, that initializes click manager for a dropdown.
   @constructor
   
   @return {Void}.
*/
function dropdownClick() {
	
	// For every dropdown option.
	$(".dropdown-menu li").click(function() {
		
		// Check for any options that have "active" class.
		var dataIsActive = $(".dropdown-menu li").hasClass("active");
		
		// Add "active" class to an option if we didn't found any.
		if (!dataIsActive)
		{
			$(this).addClass("active");
			enableDisableOptions(".dropdown-menu li", 
			"active", "disabled", false, 0);	 	
		}
		else if ($(this).hasClass("active"))
		{
			$(this).removeClass("active");
			enableDisableOptions(".dropdown-menu li", 
			"disabled", "disabled", true, 1);	  
		}
	});
}

/* Function, that enables/disables dropdown options.
   @constructor
   @param {string} target - DOM element that has dropdown options.
   @param {string} searchClass - DOM class name that we are searching for.
   @param {string} addRemoveClass - DOM class name to add/remove.
   @param {bool} found - Determines if a DOM element needs to have specific
   class or not.
   @param {int} mode - Determines if we need to add or remove a class.	
   
   Possible mode values:
   0 - add addRemoveClass to an option.
   1 - remove addRemoveClass to an option.
   
   @return {Void}.
*/
function enableDisableOptions(target, searchClass, addRemoveClass, found, mode) {
	
	$(target).each(function(i) 
	{
		if ($(this).hasClass(searchClass) === found) 
		{
			if (mode === 0)
			{
				$(this).addClass(addRemoveClass);
				$(this).find("a").removeAttr("data-toggle");
			}
			else if (mode === 1)
			{
				$(this).removeClass(addRemoveClass);
				$(this).find("a").attr("data-toggle","collapse");			
			}
		}	
	});  	
}

/* Function, that appends chart name to body.
   @constructor
   @param {string} targetDiv - DOM element to append chart name to.
   @param {string} name - Chart name to append.
   
   @return {Void}.
*/
function appendChartName(targetDiv, name) {
	
	$(targetDiv).append("<br><h3>" + name + "</h3>");
}
/* Function, that sets style for a chart.
   @constructor  
   @param {string} targetDiv - Chart DOM element that needs styling. 
   @param {int} mode - Determine chart height.
   
   Possible mode values:
   0 - 300px height.
   1 - 200px height.
   
   @return {Void}.
*/
function chartStyle(targetDiv, mode) {
	
	$(targetDiv).css("width", "100%");
	$(targetDiv).css("background-color", "white");
	
	if (mode == 0)
		$(targetDiv).css("height", "300px");
	else if (mode == 1)
		$(targetDiv).css("height", "200px");	
}
/* Function, that initializes charts based on click event
   parameters.
   @constructor  	
   @param {object} event - Data for click event.
   @param {string} event.data.param1 - DOM element to initialize chart to.
   @param {array} event.data.param2 - Data to render inside a chart.
   @param {string} event.data.param3 - DOM element, whose button to 
   disable after 1st click.
   @param {int} even.data.param4 - Determine what chart to render.

   Possible even.data.param4 values:	
   0,1 - line chart.
   2 - column chart.
   3 - bar chart.
   4 - multiple axes chart.
   5 - gauge chart.
	
   @return {Void}.	
*/
function onColllapseClick(event) {	

	if (event.data.param4 === 0 || event.data.param4 === 1)
	{
		initLineChart(event.data.param2, event.data.param1,
		event.data.param5, event.data.param6);
	}
	else if (event.data.param4 === 2)
	{
		initColumnChart(event.data.param2, event.data.param1,
		event.data.param5, event.data.param6);		
	}
	else if (event.data.param4 === 3)
	{
		initBarChart(event.data.param2, event.data.param1,
		event.data.param5, event.data.param6);	
	}
	else if (event.data.param4 === 4)
	{
		initMultiValueChart(event.data.param2,
		event.data.param1);
	}
	else if (event.data.param4 === 5)
	{
		initGaugeChart(event.data.param2,
		event.data.param1);
	}
	
	chartStyle("#" + event.data.param1, 0);	
    $(event.data.param3).off("click");	
}