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
			input {cursor: pointer;}
			input[type=image]:hover
			{
				height: 515px;
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
		$randColor = rand(0, 2);
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
		
		<h2 class="p2"><u>Bird of the Visit</u></h2>
		
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
			$query = "select * from birds where id = :id";
			
			$statement = $db->prepare($query);
			$statement->bindValue(':id', $_SESSION['botv']);
			$statement->execute();
			$row = $statement->fetch();
			$statement->closeCursor();
			
			echo "<p class='p3'>".$row['colloquial_name']."</p>";
			
			$imagepath = "./images/".$row['image_path']."";
			echo '<form method = "post" action = "details.php">';
			echo '<table class="center">';
			echo "<tr>";
			echo '<input type = "hidden" name = "birdBoy" value = "'.$_SESSION['botv'].'">';
			echo '<td><input type = "image" src = "'.$imagepath.'" style="box-shadow: 4px 8px '.$_SESSION['colorSelect'][$randColor].';"></td>';
			echo "</tr>";
			echo "</table>";
			
			$statement->closeCursor();
		?> 
		
		<br><br>
		<div style="background-color: LightSkyBlue; spacing: 0; border: 4px solid #6094B2;">
		<br><br><br><br><br>
		</div>
		
	</body>
</html>