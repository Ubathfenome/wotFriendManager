<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
		<title>WoT ReplaysManager</title>
		<link rel="shortcut icon" href="./images/favicon.png">
		<link rel="stylesheet" type="text/css" href="./css/common.css">
		<link rel="stylesheet" type="text/css" href="./css/style002.css">
	</head>
	<body>
		<div id="db" class="debug"><?php require('./db/connect.php'); ?></div>
		<a href="./index.php"><h1>World of Tanks - Replays Manager</h1></a>
		<p>Download & Execute the ReplayLogger</p>
		<div id="id01">
			<a class="ICON" href="./scripts/ReplayLogger.bat">
				<img src="./images/VideoLogger.png" alt="ReplayLogger"/>
			</a>
		</div>
		<p>Upload your replay list</p>
		<div id="id02">	
			<span id="dropzone" ondrop="drop(event)" ondragover="allowDrop(event)">
				<img src="./images/Dropzone.png" alt="Drop your file here"/>
				<p>Drop your file here</p>
			</span>
		</div>
		<div id="charts" class="modal">
			<div class="modal-content">
				<img id="loadgif" src="./images/LoadingAnimation.png" alt="Loading icon"/>
			</div>
		</div>
		<div id="chartdiv_tanks">
			<h3>Loading content...</h3>
		</div>
		<div id="chartdiv_maps">
			<h3>Loading content...</h3>
		</div>
		<div id="anchor"></div>
		<div id="id03"></div>
		<div id="id04">
			<table id="data">
				<tr id="headerBlock">
					<th>Country</th>
					<th>Tank</th>
					<th>Mapcode</th>
					<th>Mapname</th>
					<th>Date</th>
					<th>Time</th>
				</tr>
			</table>
		</div>
		<script src="./scripts/get_params.js"></script>
		<script title="userid">
			var userid=0;
			var params = getSearchParameters();
			if(params.userid!=null){
				userid=params.userid;
			} else {
				alert("Invalid userid");
			}
		</script>
		<script title="filesystem">
			var success=false;
			var lines=null;
			var length=0;
			// Check for the various File API support.
			if (window.File && window.FileReader && window.FileList && window.Blob) {
			  // Great success! All the File APIs are supported.
			  success=true;
			} else {
			  alert('The File APIs are not fully supported in this browser.');
			}
		</script>
		<script src="https://www.amcharts.com/lib/3/amcharts.js"></script>
		<script src="https://www.amcharts.com/lib/3/serial.js"></script>
		<script type="text/javascript" src="http://www.amcharts.com/lib/3/themes/black.js"></script>
		<script title="chart">
			var chartDataTanks = [];
			var chartDataMaps = [];
			var chart = null;

			function updateChart(){
				// http://www.w3schools.com/howto/howto_css_modals.asp		
				document.getElementById("charts").style.display = "block";
				AmCharts.makeChart( "chartdiv_tanks",{
					"type": "serial",
					"categoryField": "tank",
					"startDuration": 1,
					"theme": "black",
					"categoryAxis": {
						"gridPosition": "start",
						"labelRotation": 90
					},
					"chartCursor": {
						"enabled": true
					},
					"chartScrollbar": {
						"enabled": true
					},
					"trendLines": [],
					"graphs": [
						{
							"balloonText": "[[value]] battles with [[tank]]",
							"fillAlphas": 1,
							"id": "AmGraph-1",
							"title": "graph 1",
							"type": "column",
							"valueField": "battles"
						}
					],
					"guides": [],
					"valueAxes": [
						{
							"id": "ValueAxis-1",
							"title": "Number of battles"
						}
					],
					"allLabels": [],
					"balloon": {},
					"titles": [
						{
							"id": "Title-1",
							"size": 15,
							"text": "Battles per tank"
						}
					],
					"dataProvider": chartDataTanks
				});
				AmCharts.makeChart( "chartdiv_maps",{
					"type": "serial",
					"categoryField": "map",
					"startDuration": 1,
					"theme": "black",
					"categoryAxis": {
						"gridPosition": "start",
						"labelRotation": 90
					},
					"chartCursor": {
						"enabled": true
					},
					"chartScrollbar": {
						"enabled": true
					},
					"trendLines": [],
					"graphs": [
						{
							"balloonText": "[[value]] battles on [[map]]",
							"fillAlphas": 1,
							"id": "AmGraph-1",
							"title": "graph 1",
							"type": "column",
							"valueField": "battles"
						}
					],
					"guides": [],
					"valueAxes": [
						{
							"id": "ValueAxis-1",
							"title": "Number of battles"
						}
					],
					"allLabels": [],
					"balloon": {},
					"titles": [
						{
							"id": "Title-1",
							"size": 15,
							"text": "Battles per map"
						}
					],
					"dataProvider": chartDataMaps
				});
				document.getElementById("charts").style.display = "none";
				jump('anchor');
			}
		</script>
		<script title="drag_drop">
			// http://www.w3schools.com/html/html5_draganddrop.asp
			// https://www.sitepoint.com/html5-file-drag-and-drop/
			// http://www.html5rocks.com/es/tutorials/file/dndfiles/
			// http://stackoverflow.com/questions/14438187/javascript-filereader-parsing-long-file-in-chunks
			function allowDrop(ev) {
				ev.preventDefault();
			}

			function drag(ev) {
				ev.dataTransfer.setData("text", ev.target.id);
			}

			function drop(ev) {
				ev.preventDefault();
				var files = ev.target.files || ev.dataTransfer.files;
				// process all File objects
				if(success){
					dbDeleteReplays();
					for (var i = 0, f; f = files[i]; i++) {
						parseFile(f);
					}
				}
			}
			
			function parseFile(file, callback){
				var invalidReplays = 0;
				var reader = new FileReader();
				var lines = null;
				
				reader.onload = function(progressEvent){
					// By lines
					lines = this.result.split("\n");
					var length = lines.length;
					for(var line = 0; line < length; line++){
						// Sample line: 20130923_2355_ussr-KV1_73_asia_korea.wotreplay (Length: 6)
						// Sample line: 20160404_2110_ussr-S-51_35_steppes.wotreplay (Length: 5)
						// Sample line: 20160406_0059_germany-G120_M41_90_GrandFinal_73_asia_korea.wotreplay (Length: 9)
						var values = lines[line].split("_");
						
						var date = values[0];
						var invalidLines=null;
						if(date.length==8){
							var time = 0;
							var appendedtime = values[1];
							if (appendedtime.length == 4){
								time = appendedtime.substring(0, 2) + ":" + appendedtime.substring(2) + ":00"
							}
							
							var entity = values[2].split("-");
							var country = entity[0];
							
							var tank=null;
							if(entity.length<=2){
								tank = entity[1];
							} else {
								tank=entity[1];
								for(var i=2; i < entity.length;i++){
									tank+="-"+entity[i];
								}
							}
							
							var mapcode=null;
							var mapname="";
							var index=-1;
							for(var i=values.length-1;i>2;i--){
								if(isNumeric(values[i])){
									mapcode=values[i];
									index=i;
									break;
								}
							}
							for(var i=3;i<index;i++){
								tank+=" " + values[i];
							}
							
							if(index+1<values.length-1){
								mapname=values[index+1];
								for(var i=index+2;i < values.length-2;i++){
									mapname+=" " + values[i];
								}
								mapname+=" ";
							}
							mapname+=values[values.length-1].split(".")[0];
							
							// Send values into DB
							dbInsertReplay(date, time, country, tank, mapcode, mapname);
						} else {
							if (invalidLines==null)
								invalidLines="<p>Line " + lines[line] + " is invalid because of date: " + date + "</p>\n";
							else
								invalidLines+="<p>Line " + lines[line] + " is invalid because of date: " + date + "</p>\n"
							invalidReplays++;
						}
					}
					var valid=length-invalidReplays;
					document.getElementById("id03").innerHTML = "<p>Located " + valid + " replays of " + length + " detected.</p>\n";
					if(invalidLines!=null)
						document.getElementById("id03").innerHTML+=invalidLines;
					
					dbLoadReplays();
					dbFillGraph();
				};

				// Read in the file
				reader.readAsText(file);
			}
			
			function isNumeric(n) {
				// http://stackoverflow.com/questions/18082/validate-decimal-numbers-in-javascript-isnumeric
				return !isNaN(parseInt(n)) && isFinite(n);
			}
			
			function dbInsertReplay(date, time, country, tank, mapcode, mapname){
				// http://www.coderslexicon.com/the-basics-of-passing-values-from-javascript-to-php-and-back/
				// http://stackoverflow.com/questions/406316/how-to-pass-data-from-javascript-to-php-and-vice-versa
				console.log(userid+", "+date+", "+time+", "+country+", "+tank+", "+mapcode+", "+mapname);
				
				var xmlhttp = new XMLHttpRequest();
				var url="./db/insert.php";
				var params="userid="+userid+"&country="+country+"&tank="+tank+"&mapcode="+mapcode+"&mapname="+mapname+"&date="+date+"&time="+time;
				
				xmlhttp.open("POST", url, true);
				xmlhttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
				
				xmlhttp.onreadystatechange=function() {
					if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
						parseResult(xmlhttp.responseText);
					}
				};				
				xmlhttp.send(params);
			}
			
			function parseResult(response){
				document.getElementById("db").innerHTML += response;
			}
			
			function dbLoadReplays(){
				var xmlhttp = new XMLHttpRequest();
				var url="./db/select.php";
				
				xmlhttp.open("POST", url, true);
				xmlhttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
				
				xmlhttp.onreadystatechange=function() {
					if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
						if(xmlhttp.responseText!="")
							parseSelectTable(xmlhttp.responseText);
					}
				};				
				xmlhttp.send();
			}
			
			function dbFillGraph(){
				var urlQuery1="./db/selectTanks.php";
				var urlQuery2="./db/selectMaps.php";
				requestURLTanks(urlQuery1);
				requestURLMaps(urlQuery2);
			}
			function requestURLTanks(query){
				var xmlhttp = new XMLHttpRequest();
				var data = [];
				chartDataTanks = [];
				
				xmlhttp.open("POST", query, true);
				xmlhttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
				
				xmlhttp.onreadystatechange=function() {
					if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
						data = parseSelectTanks(xmlhttp.responseText);
						
						chartDataTanks = convertDataToChart(data);
						updateChart();
					}
				};				
				xmlhttp.send();
			}
			function requestURLMaps(query){
				var xmlhttp = new XMLHttpRequest();
				var data = [];
				chartDataMaps = [];
				
				xmlhttp.open("POST", query, true);
				xmlhttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
				
				xmlhttp.onreadystatechange=function() {
					if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
						data = parseSelectMaps(xmlhttp.responseText);
						
						chartDataMaps = convertDataToChart(data);
						updateChart();
					}
				};				
				xmlhttp.send();
			}
			
			function parseSelectTanks(response){
				var lines = response.split("@");
				var tanks = [];
				
				for(var i = 0; i < lines.length; i++){
					var values = lines[i].split(";");
					
					if(values.length==2){
						tanks.push({
							tank: values[0],
							battles: values[1]
						});
					}
				}
				
				return tanks;
			}
			function parseSelectMaps(response){
				var lines = response.split("@");
				var maps = [];
				
				for(var i = 0; i < lines.length; i++){
					var values = lines[i].split(";");
					
					if(values.length==2){
						maps.push({
							map: values[0],
							battles: values[1]
						});
					}
				}
				
				return maps;
			}
			
			function parseSelectTable(response){
				// http://www.solvetic.com/tutoriales/article/1469-gestionando-tablas-din%C3%A1micas-con-el-plugin-datatables-de-jquery/
				var header = document.getElementById("headerBlock").innerHTML;
				var lines = response.split("@");
				var string = "";
				
				for(var i = 0; i < lines.length; i++){
					var values = lines[i].split(";");
					string+="<tr>\n";
					for(var j = 0; j < values.length; j++){
						string+="<td>"+values[j]+"</td>";
					}
					string+="\n</tr>\n";
				}
				document.getElementById("data").innerHTML=header+string;
			}
			
			function convertDataToChart(data){
				var len = data.length;
				var chartData = [];
				
				chartData = data;
				
				return chartData;
			}
			function jump(h){
				var top = document.getElementById(h).offsetTop;
				window.scrollTo(0, top);
			}
			function dbDeleteReplays(){
				var xmlhttp = new XMLHttpRequest();
				var url="./db/delete.php";
				
				xmlhttp.open("POST", url, true);
				xmlhttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
				
				xmlhttp.onreadystatechange=function() {
					if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
						parseResult(xmlhttp.responseText);
					}
				};				
				xmlhttp.send();
			}
			
			window.onload = function() {
				dbLoadReplays();
				dbFillGraph();
			}
		</script>
		<?php require('./footer.php'); ?>
	</body>
</html>