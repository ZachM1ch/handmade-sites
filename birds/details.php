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
			img
			{
				width: 600px;
				height: auto;
				box-shadow: 4px 8px;
			}
			td.special 
			{
				background-color:Wheat;
				box-shadow: 4px 4px BurlyWood;
				padding: 12px;
				border: 3px solid Tan;
				width: 350px;
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
			p
			{
				font-family: "Trebuchet MS";
				font-size: 150%;
				color: Chocolate;
			}
			b
			{
				color: GoldenRod;
				font-size: 170%;
			}
			.p3 
			{
				font-family: "Rockwell";
				font-size: 180%;
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
		$birdBoy=$_POST['birdBoy'];
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
			$statement->bindValue(':id', $birdBoy);
			$statement->execute();
			$row = $statement->fetch();
			
			echo '<h1 class="p3">'.$row['colloquial_name'].'</h1>';
			
			
			$imagepath = $row['image_path'];
			echo '<table class="center" cellspacing="10">';
			echo "<tr>";
			echo '<td><img src = "./images/'.$imagepath.'" style="box-shadow: 4px 8px '.$_SESSION['colorSelect'][$randColor].';"></td>';
			echo '<td class="special">';
			if ($row['id'] == $_SESSION['botv'])
			{
				echo '<p><b>Bird of the Visit!</b></p>';
			}
			echo '<p>Scientific Name: '.$row['scientific_name'].'</p><p>Average Wingspan: '.$row['avg_wingspan'].'cm </p> '.$row['formatted_info'].' <p>'.$row['cc_rights'].' </p></td>';
			echo "</tr>";
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