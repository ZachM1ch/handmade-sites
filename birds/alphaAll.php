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
			h2 {text-align: center;}
			h3 {text-align: center;}
			h5 {text-align: center;}
			input {cursor: pointer;}
			input[type=image] 
			{
				width: 400px;
				height: auto;
			}
			input[type=image]:hover
			{
				height: 430px;
				width: auto;
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
			.p2 
			{
				font-family: "Bahnschrift SemiBold";
				font-size: 190%;
			}
			.p3 
			{
				font-family: "Rockwell";
				font-size: 140%;
				text-align: center;
			}
			.p4
			{
				font-family: "Rockwell";
				font-size: 100%;
			}
			.topRight
			{
				position:absolute;
				top:0;
				right:0;
			}
			.topLeft
			{
				position:absolute;
				top:0;
				left:0;
			}
		</style>
	</head>

	<body style="background-color:PapayaWhip;">
	
	<?php
		//just variable things
		$randBird = rand(1,52);
	?>
	
		<div style="background-color: LightSkyBlue; spacing: 0; border: 4px solid #6094B2;">
		
		<form method = "post" action = "login.php">
			<input type = "submit" value = "Admin Login" class="topRight">
		</form>
		<form method = "post" action = "suggest.php">
			<input type = "submit" value = "Suggest Birds" class="topLeft">
		</form>
		
		<h1 class="p1">A Place for Cool and Colorful Birds</h1>
		<table class="center">
		<tr>
			<td>
				<form method = "post" action = "alphaAll.php">
					<input type = "submit" value = "All Birds">
				</form>
			</td>
			<td>
				<form method = "post" action = "alphaSearch.php">
					<input type = "text" placeholder = "search" name = "searchTerm">
					<input type = "submit" value = "Go">
				</form>
			</td>
			<td>
				<form method = "post" action = "details.php">
				<?php
					echo '<input type = "hidden" name = "birdBoy" value = "'.$randBird.'">';
				?>
					<input type = "submit" value = "Random Bird">
				</form>
			</td>
		</tr>
		</table>
		<br>
		</div>
		
		<table class="center">
		<tr>
			<td>
				<form method = "post" action = "wingAll.php">
					<input type = "submit" value = "Switch to Wingspan">
				</form>
			</td>
			<td>
				<h2 class="p2"><u>All Birds (alphabetical)</u></h2>
			</td>
			<td>
				<p>.&emsp;&emsp;&emsp;&emsp;</p>
			</td>
		</tr>
		</table>
		
		<br>
		
		<?php 
			
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
			$query = "select id, colloquial_name, image_path from birds";
			
			$statement = $db->prepare($query);
			$statement->execute();
			$rows = $statement->fetchAll();
			$count = $statement->rowCount();
			
			//Sorting contents of $rows alphabetically
			for($i = 0; $i < $count; $i++)
			{
				for ($j = 0; $j < $count - $i - 1; $j++)
				{
					if ($rows[$j][1] > $rows[$j+1][1])
					{
						$temp = $rows[$j];
						$rows[$j] = $rows[$j+1];
						$rows[$j+1] = $temp;
					}
				}
			}	
			
			
			$colorCounter = rand(0,2);
			$birdCounter = 0;
			$countCounter = $count;
			echo '<table class="center" cellspacing="15">';
			for($i = 0; $i < ceil($count/4); $i++)
			{
				echo "<tr>";
				
				for($j = 0; $j < 4; $j++)
				{
					if($countCounter > 0)
					{
						echo "<td>";
						echo "<p class='p3'>".$rows[$birdCounter][1]."</p>";
						echo '<form method = "post" action = "details.php">';
						echo '<input type = "hidden" name = "birdBoy" value = "'.$rows[$birdCounter][0].'">';
						echo '<input type = "image" src = "./images/'.$rows[$birdCounter][2].'" style="box-shadow: 4px 8px '.$_SESSION['colorSelect'][$colorCounter].';"></form></td>';
						$birdCounter++;
						$countCounter--;
						$colorCounter++;
						if ($colorCounter == 3)
						{
							$colorCounter = 0;
						}
					}
				}
				echo "</tr>";
			}
			echo "</table>";

			
			$statement->closeCursor();
		?> 
		
		<h5 class="p4"><a href="home.php">Return to Home</a></h5>
		
		<br><br>
		<div style="background-color: LightSkyBlue; spacing: 0; border: 4px solid #6094B2;">
		<br><br><br>
		</div>
		
	</body>
</html>