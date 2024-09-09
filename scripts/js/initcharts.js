/* Function, that initializes a simple line chart.
   @constructor  
   @param {object array} data - Chart data to render.
   @param {string} targetDiv - DOM element to write chart in.
   @param {string} nameX - X axis name.
   @param {string} nameY - Y axis name.
   
   @return {Void}.
*/
function initLineChart(data, targetDiv, nameX, nameY) {	

	let chart = am4core.createFromConfig({
	  "paddingRight": 20,
	  "data": data,
	  "xAxes": [{
		"type": "DateAxis",
		"renderer": {
		  "grid": {
			"location": 0
		  }
		},
		"title":{
			"text": nameX
		}
	  }],
	  "yAxes": [{
		"type": "ValueAxis",
		"tooltip": {
		  "disabled": true
		},
		"renderer": {
		  "minWidth": 35
		},
		"title":{
			"text": nameY
		}
	  }],
	  "series": [{
		"id": "s1",
		"type": "LineSeries",
		"dataFields": {
		  "dateX": "date",
		  "valueY": "value"
		},
		"tooltipText": "{valueY.value}"
	  }],
	  "cursor": {
		"type": "XYCursor"
	  }
	}, targetDiv, "XYChart");
	
	chart.scrollbarX = new am4core.Scrollbar();
	chart.scrollbarX.parent = chart.bottomAxesContainer;	
	chart.scrollbarX.background.fill = "#E0DFD6";
}

/* Function, that initializes a simple column chart.
   @constructor  
   @param {object array} data - Chart data to render.
   @param {string} targetDiv - DOM element to write chart in.
   @param {string} nameX - X axis name.
   @param {string} nameY - Y axis name.
   
   @return {Void}.
*/
function initColumnChart(data, targetDiv, nameX, nameY) {
	
	am4core.useTheme(am4themes_animated);
	
	let chart = am4core.createFromConfig({
	  "colors": {
		"saturation": 0.4
	  },
	  "data": data,
	  "yAxes": [{
		"type": "ValueAxis",
		"renderer": {
		  "maxLabelPosition": 0.98
		},
		"title": {
			"text": nameY
		}
	  }],
	  "xAxes": [{
		"type": "CategoryAxis",
		"renderer": {
		  "minGridDistance": 20,
		  "grid": {
			"location": 0
		  }
		},
		"dataFields": {
		  "category": "date"
		},
		"title": {
			"text": nameX
		}
	  }],
	  "series": [{
		"type": "ColumnSeries",
		"dataFields": {
		  "categoryX": "date",
		  "valueY": "value"
		},
		"bullets": [{
		  "type": "LabelBullet",
		  "label": {
			"text":"{valueY}",
			"verticalCenter":"bottom",
			"dy":-10,
			"hideOversized":false,
			"truncate":false
		  }
		}],
		"defaultState": {
		  "ransitionDuration": 1000
		},
		"sequencedInterpolation": true,
		"sequencedInterpolationDelay": 100,
		"columns": {
		  "strokeOpacity": 0,
		  "adapter": {
			"fill": function (fill, target) {
			  return chart.colors.getIndex(target.dataItem.index);
			}
		  }
		}
	  }],
	  "cursor": {
		"type": "XYCursor",
		"behavior": "zoomX"
	  }
	}, targetDiv, "XYChart");
}
/* Function, that initializes a simple bar chart.
   @constructor  
   @param {object array} data - Chart data to render.
   @param {string} targetDiv - DOM element to write chart in.
   @param {string} nameX - X axis name.
   @param {string} nameY - Y axis name.
   
   @return {Void}.
*/
function initBarChart(data, targetDiv, nameX, nameY) {
	
	am4core.useTheme(am4themes_animated);
	
	let chart = am4core.createFromConfig({
		"data": data,
	  "yAxes": [{
		"type": "CategoryAxis",
		"numberFormatter":{
			"numberFormat":"#"
		},
		"renderer": {
		  "inversed":true,
		  "grid":{
			"template":{
				"location":0
			}
		  },
		  "cellStartLocation":0.1,
		  "cellEndLocation":0.8,
		  "minGridDistance":20
		},
		"dataFields": {
		  "category": "date"
		},
		"title": {
			"text": nameY
		}
	  }],
	  "xAxes": [{
		"type": "ValueAxis",
		"renderer": {
		  "opposite":true
		},
		"title": {
			"text": nameX
		}
	  }],
	  "series": [{
		"type": "ColumnSeries",
		"dataFields": {
		  "categoryY": "date",
		  "valueX": "value"
		},
		"bullets": [{
		  "type": "LabelBullet",
		  "label": {
			"text":"{valueX} µSv/h",
			"horizontalCenter":"left",
			"dx":10,
			"hideOversized":false,
			"truncate":false
		  }
		}],
		"defaultState": {
		  "ransitionDuration": 1000
		},
		"sequencedInterpolation": true,
				
	  }],
	  "cursor": {
		"type": "XYCursor",
		"behavior": "zoomX"
	  }
	  
	}, targetDiv, "XYChart");
}
/* Function, that initializes a gauge chart.
   @constructor  
   @param {decimal} data - Chart data to render.
   @param {string} targetDiv - DOM element to write chart in.
   
   @return {Void}.
*/
function initGaugeChart(data, targetDiv) {
	
	am4core.useTheme(am4themes_animated);

	let chart = am4core.createFromConfig({
	  "innerRadius": -15,
	  "xAxes": [{
		"type": "ValueAxis",
		"min": 0,
		"max": 100,
		"strictMinMax": true,
		"axisRanges": [{
		  "value": 0,
		  "endValue": 50,
		  "axisFill": {
			"fillOpacity": 1,
			"fill": "#67b7dc"
		  }
		}, {
		  "value": 50,
		  "endValue": 80,
		  "axisFill": {
			"fillOpacity": 1,
			"fill": "#6771dc"
		  }
		}, {
		  "value": 80,
		  "endValue": 100,
		  "axisFill": {
			"fillOpacity": 1,
			"fill": "#a367dc"
		  }
		}]
	  }],
	  "hands": [{
		"type": "ClockHand",
		"id": "h1",
		"value":data
	  }]
	}, targetDiv, "GaugeChart");
}
/* Function, that initializes a XY chart with multiple axis.
   @constructor  
   @param {object array} data - Chart data to render.
   @param {string} targetDiv - DOM element to write chart in.
   
   @return {Void}.
*/
function initMultiValueChart(data, targetDiv) {
	
	am4core.useTheme(am4themes_animated);

	let chart = am4core.createFromConfig({
	  "paddingRight": 20,
	  "data": data,
	    "legend": {
		},
	  "xAxes": [{
		"type": "DateAxis",
		"renderer": {
		  "grid": {
			"location": 0,
			"minGridDistance":50
		  }      
		}
	  }],

	  // Create Y axes
	  "yAxes": [{
		"type": "ValueAxis",
		"id":"v1",
		"tooltip": {
		  "disabled": true
		},
		"renderer": {
		  "minWidth": 35,
		  "opposite":false,
		  "line": {
			"strokeOpacity":1,
			"strokeWidth":2,
			"stroke": "#FF6600"
		  },
		 "grid": {
			"disabled": true
		  }	  
		}},
		{
		"type": "ValueAxis",
		"id":"v2",
		"tooltip": {
		  "disabled": true
		},
		"renderer": {
		  "minWidth": 35,
		  "opposite":false,
		  "line": {
			"strokeOpacity":1,
			"strokeWidth":2,
			"stroke": "#FCD202"
		  },
		 "grid": {
			"disabled": true
		  }	 
		}},
		{
		"type": "ValueAxis",
		"id":"v3",
		"tooltip": {
		  "disabled": true
		},
		"renderer": {
		  "minWidth": 35,
		  "opposite":true,
		  "line": {
			"strokeOpacity":1,
			"strokeWidth":2,
			"stroke": "#7CFC00"
		  },
		 "grid": {
			"disabled": true
		  }	 
		}},
		{
		"type": "ValueAxis",
		"id":"v4",
		"tooltip": {
		  "disabled": true
		},
		"renderer": {
		  "minWidth": 35,
		  "opposite":true,
		  "line": {
			"strokeOpacity":1,
			"strokeWidth":2,
			"stroke": "#2538FF"
		  },
		 "grid": {
			"disabled": true
		  }	 
		}}],
	  "series": [{
		"yAxis": "v1",
		"type": "LineSeries",
		"stroke":"#FF6600",
		"name":"Radiation (µSv/h)",
		"dataFields": {
		  "dateX": "date",
		  "valueY": "value1"
		},
		"tooltipText": "{valueY.value} µSv/h",
		"tooltip": {
		  "getFillFromObject": false,
		  "background": {
			"fill": "#FF6600"
		  }
		}
	  },
	  {
		"yAxis": "v2",
		"type": "LineSeries",
		"stroke":"#FCD202",
		"name":"Precipitation (mm)",
		"dataFields": {
		  "dateX": "date",
		  "valueY": "value2"
		},
		"tooltipText": "{valueY.value} mm",
		"tooltip": {
		  "getFillFromObject": false,
		  "background": {
			"fill": "#FCD202"
		  }
		}
	  },{
		"yAxis": "v3",
		"type": "LineSeries",
		"stroke":"#7CFC00",
		"name":"Solar flux (W*m-2*Hz)",
		"dataFields": {
		  "dateX": "date",
		  "valueY": "value3"
		},
		"tooltipText": "{valueY.value} (W*m-2*Hz)",
		"tooltip": {
		  "getFillFromObject": false,
		  "background": {
			"fill": "#7CFC00"
		  }
		}
	  },{
		"yAxis": "v4",
		"type": "LineSeries",
		"stroke":"#2538FF",
		"name":"Day length (min)",
		"dataFields": {
		  "dateX": "date",
		  "valueY": "value4"
		},
		"tooltipText": "{valueY.value} min",
		"tooltip": {
		  "getFillFromObject": false,
		  "background": {
			"fill": "#2538FF"
		  }
		}
	  }],
	  "cursor": {
		"type": "XYCursor"
	  }
	}, targetDiv, "XYChart");
	
	chart.scrollbarX = new am4core.Scrollbar();
	chart.scrollbarX.parent = chart.bottomAxesContainer;	
	chart.scrollbarX.background.fill = "#E0DFD6";
}