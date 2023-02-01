<html>
<head>
 <title>Heigh's Flooring and Tile House Repository</title>
</head>
<body>
<h1>Heigh's Flooring and Tile House Repository - Entry Results</h1>
<?php
 // variables
 $sku=$_POST['sku'];
 $price=$_POST['price'];
 $name=$_POST['name'];
 $color=$_POST['color'];
 $category=$_POST['category'];
 $quantity_available=$_POST['quantity_available'];
 $image=$_POST['image'];
 // cheack if all fields are empty
 if (!$sku || !$price || !$name || !$color || !$category || !$quantity_available || !$image)
 {
 echo "You have not entered all the required details.<br />"
 ."Please go back and try again.";
 exit;
 }

 if (!get_magic_quotes_gpc())
 {
 $sku = addslashes($sku);
 $price = doubleval($price);
 $name = addslashes($name);
 $color = addslashes($color);
 $category = addslashes($category);
 $quantity_available = intval($quantity_available);
 $image = addslashes($image);
 }
 // access database
 @ $db = new mysqli('localhost', 'u565780247_heigh_employee', 'fl00rAndTile',
'u565780247_floor_store');
 if (mysqli_connect_errno())
 {
 echo "Error: Could not connect to database. Please try again later.";
 exit;
 }
 // insert new product into db
 $query = "insert into products values
 ('".$sku."', '".$price."', '".$name."', '".$color."', '".$category."',
'".$quantity_available."', '".$image."')";
 $result = $db->query($query);
 // confirmation of insert
 if ($result)
 {
 echo $db->affected_rows." product inserted into database.";
 }
 else
 {
 echo "An error has occurred. The item was not added.";
 }

 // return to dash
 echo '<br>';
 echo '<a href="dashboard.html">Return to dashboard</a>';
 $result->free();
 $db->close();

?>
</body>
</html>