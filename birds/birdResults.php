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
			p     
			{
				font-family: "Rockwell";
				font-size: 120%;
			}
			input 
			{ 
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
			b
			{
				font-family: "Rockwell";
				font-size: 120%;
			}
		</style>
	</head>

	<body style="background-color:PapayaWhip;">
	
	
		<div style="background-color: LightSkyBlue; spacing: 0; border: 4px solid #6094B2;">
		
		<h1 class="p1">A Place for Cool and Colorful Birds</h1>
		<h1 class="p1">Admin Search</h1>
		</div>
		
		<br><br><br><br>
			
			
		<?php
			
			// variables
			$tabs = ".&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;";
			$searchTerm=$_POST['searchTerm'];
			
			
			// cheack if all fields are empty
			if (!$searchTerm)
			{
				echo $tabs ."You have not entered anything";
			}
			else
			{
				
				// connect
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
				$query = "select * from birds where colloquial_name like '%".$searchTerm."%'";
				$statement = $db->prepare($query);
				$statement->execute();
				$rows = $statement->fetchAll();
				$count = $statement->rowCount();
				
				echo '<table>';
				
				for ($i = 0; $i < $count; $i++)
				{
					$line = "<label><b>".$tabs."".($i+1).". ".$rows[$i][1]." </b></label>";
					
					echo '<tr>';
					
					echo '<form method = "post" action = "modifyBird.php">';
					echo '<td>'.$line.'</td>';
					echo '<input type = "hidden" name = "sentID" value = "'.$rows[$i][0].'">';
					echo '<td><input type = "submit" value = "Modify"></td>';
					echo '</form>';
					
					echo '<form method = "post" action = "deleteBird.php">';
					echo '<input type = "hidden" name = "sentID" value = "'.$rows[$i][0].'">';
					echo '<td><input type = "submit" value = "Delete"></td>';
					echo '</form>';
					
					echo '</tr>';
					
				}
				echo '</table>';
				
			}
			
		?>
		
		<table>
			<tr>
				<td>.&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;</td>
				<td><h5 class="p4"><a href="birdDashboard.php">Return to Dashboard</a></h5></td>
			</tr>
		</table>
		
		
		<br><br><br><br>
		<div style="background-color: LightSkyBlue; spacing: 0; border: 4px solid #6094B2;">
		<br><br><br><br><br><br>
		</div>
		
	</body>
</html>