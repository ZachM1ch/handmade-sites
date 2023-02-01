<html>
<head>
 <title>Heigh's Flooring and Tile House Repository</title>
</head>
<body>
<h1>Heigh's Flooring and Tile House Modify Confirmation</h1>
<?php
 // variables
 $sku=$_POST['sku'];
 $price=$_POST['price'];
 $name=$_POST['name'];
 $color=$_POST['color'];
 $category=$_POST['category'];
 $quantity_available=$_POST['quantity_available'];
 $image=$_POST['image'];
 // check if all fields are empty
 if (!$price && !$name && !$color && !$category && !$quantity_available && !$image)
 {
 echo "You did not enter any information.<br />"
 ."Please go back and try again.";
 exit;
 }
 // access database
 @ $db = new mysqli('localhost', 'u565780247_heigh_employee', 'fl00rAndTile',
'u565780247_floor_store');
 if (mysqli_connect_errno())
 {
 echo "Error: Could not connect to database. Please try again later.";
 exit;
 }

 //count affected fields
 $num_affected = 0;

 // modify each field as needed
 if ($price)
 {
 $query = "update products set price = $price where sku = '$sku'";
 $result = $db->query($query);
 $num_affected = $num_affected + 1;
 }
 if ($name)
 {
 $query = "update products set name = $name where sku = '$sku'";
 $result = $db->query($query);
 $num_affected = $num_affected + 1;
 }
 if ($color)
 {
 $query = "update products set color = $color where sku = '$sku'";
 $result = $db->query($query);
 $num_affected = $num_affected + 1;
 }
 if ($category)
 {
 $query = "update products set category = category where sku = '$sku'";
 $result = $db->query($query);
 $num_affected = $num_affected + 1;
 }
 if ($quantity_available)
 {
 $query = "update products set quantity_available = $quantity_available where sku =
'$sku'";
 $result = $db->query($query);
 $num_affected = $num_affected + 1;
 }
 if ($image)
 {
 $query = "update products set image = $image where sku = '$sku'";
 $result = $db->query($query);
 $num_affected = $num_affected + 1;
 }
 // confirmation for modify results
 if ($result)
 {
 echo $num_affected." entries modified for product.";
 }
 else
 {
 echo "An error has occurred. The product was not modified.";
 }

 // return to dash
 echo '<br>';
 echo '<a href="dashboard.html">Return to dashboard</a>';
 $result->free();
 $db->close();

?>
</body>
</html>