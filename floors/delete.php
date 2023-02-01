<html>
<head>
 <title>Heigh's Flooring and Tile House Repository</title>
</head>
<body>
<h1>Heigh's Flooring and Tile House Repository - Delete Product</h1>
<?php
 // variable
 $prod_sku=$_POST['prod_sku'];
 // access database
 @ $db = new mysqli('localhost', 'u565780247_heigh_employee', 'fl00rAndTile',
'u565780247_floor_store');
 if (mysqli_connect_errno())
 {
 echo "Error: Could not connect to database. Please try again later.";
 exit;
 }
 // delete product from db
 $query = "delete from products where sku like '%".$prod_sku."%'";
 $result = $db->query($query);
 // display confirmation
 if ($result)
 {
 echo $db->affected_rows." product deleted from database.";
 }
 else
 {
 echo "An error has occurred. The item was not deleted.";
 }

 // return to dash
 echo '<br>';
 echo '<a href="dashboard.html">Return to dashboard</a>';
 $result->free();
 $db->close();

?>
</body>
</html>