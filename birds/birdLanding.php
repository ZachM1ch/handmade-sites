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
			.p1 
			{
				font-family: "Arial Black";
				font-size: 400%;
			}
			.p2 
			{
				font-family: "Arial Black";
				font-size: 280%;
			}
			input[type=submit]
			{
				background-color: LightSkyBlue;
				border: 2px solid #6094B2;
				color: Black;
				padding: 12px 24px;
				text-decoration: none;
				cursor: pointer;
				margin: 4px 2px;
				height: 80px;
				width: 220px;
				font-family: "Arial Black";
				font-size: 180%;
				border-radius: 25px;
			}
		</style>
	</head>

	<body style="background-color:PapayaWhip;">
	
	<?php
		//session stuff
		$_SESSION['botv'] = rand(1,17);
		$_SESSION['colorSelect'] = ['#4169E1', '#FF69B4', '#00FA9A'];
	?>
		<br><br><br><br><br><br><br><br><br><br><br>
		<h1 class="p1">Welcome to CoolerBirds.com</h1>
		<h1 class="p2">A Place for Cool and Colorful Birds</h1>
		<table class="center">
		<tr>
			<td>
				<form method = "post" action = "home.php">
					<input type = "submit" value = "Enter Site">
				</form>
			</td>
		</tr>
		</table>
	</body>
</html>