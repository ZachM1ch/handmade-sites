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
			h1 { text-align: center; }
			form  { display: table; }
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
			.p5
			{
				font-family: "Rockwell";
				font-size: 180%;
			}
		</style>
	</head>

	<body style="background-color:PapayaWhip;">
	
		<div style="background-color: LightSkyBlue; spacing: 0; border: 4px solid #6094B2;">
		
		<h1 class="p1">A Place for Cool and Colorful Birds</h1>
		<h1 class="p1">Admin Login</h1>
		</div>
		
		<br><br><br><br>
		
		<form method = "post" action = "">
			<p>
				<label>.&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;</label>
				<label>User ID: </label>
				<input type = "text" name = "userID">
			</p>
			<p>
				<label>.&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;</label>
				<label>Password: </label>
				<input type = "text" name = "passbird">
			</p>
			<p>
				<label>.&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;</label>
				<input type = "submit" value = "Login">
			</p>
		</form>
		
		
		<?php 
			
			if (!empty($_POST['userID']) || !empty($_POST['passbird']))
			{
				
				//post variables
				$userID=$_POST['userID'];
				$passbird=$_POST['passbird'];
				
				$dsn = 'mysql:host=localhost;dbname=u565780247_bird_nest'; 
				$username = 'u565780247_birdwatcher';   
				$password = 'Binoculars2'; 
 
				try { 
					$db = new PDO($dsn, $username, $password); 
				} catch(PDOException $e) {
					echo "Connection failed: " . $e->getMessage();
					exit(); 
				} 
			
				// get stuff from search info from db
				$query = "select * from admin";
				
				$statement = $db->prepare($query);
				$statement->execute();
				$rows = $statement->fetchAll();
				$count = $statement->rowCount();
				
				echo '<br>';
				echo '<table>';
				echo '<tr>';
				echo '<td>.&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;</td>';
				if ($userID != $rows[0][0] || $passbird != $rows[0][1])
				{
					echo '<td><h5 style="color: Red;">The User ID/Password you entered is invalid</h5></td>';
					
				}
				else
				{
					echo '<td><h5 class="p5"><a href="birdDashboard.php">To Admin Dashboard</a></h5></td>';

				}
				echo '</tr>';
				echo '</table>';
			
				$statement->closeCursor();
			}
		?> 
		
		<table>
		<tr>
		<td>.&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;</td>
		<td><h5 class="p4"><a href="home.php">Return to Home</a></h5></td>
		</tr>
		</table>
		
		<br><br><br><br>
		<div style="background-color: LightSkyBlue; spacing: 0; border: 4px solid #6094B2;">
		<br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>
		</div>
		
	</body>
</html>