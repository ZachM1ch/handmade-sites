<?php
session_start();
?>
<html>
<head>
 <title>Heigh's Flooring and Tile House Repository</title>
</head>
<body>
<h1>Heigh's Flooring and Tile House Repository - Search Results</h1>
<?php

 // variables
 $searchtype=$_POST['searchtype'];
 $searchterm=trim($_POST['searchterm']);
 // check if fields are empty
 if (!$searchtype || !$searchterm)
 {
 echo 'You have not entered search details. Please go back and try again.';
 exit;
 }

 if (!get_magic_quotes_gpc())
 {
 $searchtype = addslashes($searchtype);
 $searchterm = addslashes($searchterm);
 }
 // access database
 @ $db = new mysqli('localhost', 'u565780247_heigh_employee', 'fl00rAndTile',
'u565780247_floor_store');
 if (mysqli_connect_errno())
 {
 echo 'Error: Could not connect to database. Please try again later.';
 exit;
 }
 // get products from search info from db
 $query = "select * from products where ".$searchtype." like '%".$searchterm."%'";
 $result = $db->query($query);
 $num_results = $result->num_rows;
 // display products
 for ($i=0; $i <$num_results; $i++)
 {
 $row = $result->fetch_assoc();
 echo "<p><strong>".($i+1).". ";
 echo htmlspecialchars(stripslashes($row['name']));
 echo '<br>';
 $imagepath = $row['image'];
 echo '<img src="'.$imagepath.'" width="300" height="180" title="'.$imagepath.'"
alt="'.$imagepath.'" />';
 echo "</strong><br />Category: ";
 echo stripslashes($row['category']);
 echo "<br />Color: ";
 echo stripslashes($row['color']);
 echo "<br />Price: $";
 echo stripslashes($row['price']);
 echo "<br />Amount On-Hand: ";
 echo stripslashes($row['quantity_available']);
 echo "<br />SKU: ";
 echo stripslashes($row['sku']);

 echo '<form action="added.php" method="post">';
 echo '------------------<br />';
 echo 'Quantity Desired:';
 echo '<input name="quantity_desired" type="text" size="3">';
 echo '<input type="submit" name="submit" value="Add To Cart">';
 $sku = $row['sku'];
 echo'<input type="hidden" name="prod_sku" value="'.$sku.'">';
 $quantity_available = $row['quantity_available'];
 echo '<input type="hidden" name="qty_av" value="'.$quantity_available.'">';
 echo '</form>';
 echo '<br />';
 }

 // return to home
 echo '<br>';
 echo '<a href="home.html">Return to home</a>';
 $result->free();
 $db->close();
?>
</body>
</html>