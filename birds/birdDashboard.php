<!DOCTYPE html>

<?php
session_start();
?>

<html>
	<head>
		<meta charset="utf-8">
		<title>CoolerBirds.com</title>
		<style>
			.center
			{
				margin-left: auto;
				margin-right: auto;
			}
			h1 {text-align: center;}
			form  { display: table;      }
			p     
			{
				display: table-row;  
				font-family: "Rockwell";
				font-size: 120%;
			}
			label { display: table-cell; }
			input 
			{ 
				display: table-cell; 
				cursor: pointer;
			}
			input[type=submit]
			{
				background-color: Wheat;
				border: 2px solid Tan;
				color: Black;
				text-decoration: none;
				margin: 4px 2px;
				font-family: "Calibri";
				font-size: 120%;
				border-radius: 10px;
			}
			input[type=text]
			{
				background-color: SeaShell;
				border: 2px solid NavajoWhite;
				color: Black;
				padding: 0px 6px;
				text-decoration: none;
				margin: 4px 2px;
				font-family: "Calibri";
				font-size: 120%;
				border-radius: 10px;
			}
			html, body 
			{
				margin:0;
				padding:0;
			}
			.p1 
			{
				font-family: "Arial Black";
				font-size: 260%;
			}
			.p4
			{
				font-family: "Rockwell";
				font-size: 100%;
			}
		</style>
	</head>

	<body style="background-color:PapayaWhip;">
	
	
		<div style="background-color: LightSkyBlue; spacing: 0; border: 4px solid #6094B2;">
		
		<h1 class="p1">A Place for Cool and Colorful Birds</h1>
		<h1 class="p1">Admin Dashboard</h1>
		</div>
		
		<br><br><br><br>
			
		<h3 style="font-family: 'Rockwell';">.&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;Add New Bird </h3>
		<form method = "post"action="addBird.php">
			<p>
				<label>.&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;</label>
				<label>Colloquial Name: </label>
				<input type = "text" name = "colName">
			</p>
			<p>
				<label>.&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;</label>
				<label>Scientific Name: </label>
				<input type = "text" name = "sciName">
			</p>
			<p>
				<label>.&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;</label>
				<label>Average Wingspan (cm): </label>
				<input type = "text" name = "avgWing">
			</p>
			<p>
				<label>.&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;</label>
				<label>Formatted Information: </label>
				<input type = "text" name = "formInfo">
			</p>
			<p>
				<label>.&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;</label>
				<label>Image Name: </label>
				<input type = "text" name = "imgName">
			</p>
			<p>
				<label>.&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;</label>
				<input type = "submit" value = "Add Bird">
			</p>
		</form>
				
		<br><br>
				
		<h3 style="font-family: 'Rockwell';">.&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;Search Existing Birds </h3>
		<form method = "post" action = "birdResults.php">
			<label>.&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;</label>
			<input type = "text" placeholder = "search" name = "searchTerm">
			<input type = "submit" value = "Search">
		</form>
		
		<h3 style="font-family: 'Rockwell';">.&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;View Bird Suggestions </h3>
		<form method = "post" action = "birdSuggestions.php">
			<label>.&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;</label>
			<input type = "submit" value = "View">
		</form>

		
		<table>
			<tr>
				<td>.&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;</td>
				<td><h5 class="p4"><a href="home.php">Logout</a></h5></td>
			</tr>
		</table>
		
		<br><br><br><br>
		<div style="background-color: LightSkyBlue; spacing: 0; border: 4px solid #6094B2;">
		<br><br><br><br><br><br>
		</div>
		
	</body>
</html>