<?php
session_start();
?>
<html>
<head>
 <title>Heigh's Flooring and Tile House Repository</title>
</head>
<body>
<h1>Heigh's Flooring and Tile House Repository - Your Cart</h1>
<?php

 // access databse
 @ $db = new mysqli('localhost', 'u565780247_heigh_employee', 'fl00rAndTile',
'u565780247_floor_store');
 if (mysqli_connect_errno())
 {
 echo 'Error: Could not connect to database. Please try again later.';
 exit;
 }
 // display products in cart
 for ($i=0; $i < count($_SESSION["sku_cart"]); $i++)
 {
 $sku = $_SESSION["sku_cart"][$i];
 $query = "select * from products where sku = '$sku'";
 $result = $db->query($query);

 $row = $result->fetch_assoc();
 echo "<p><strong>".($i+1).". ";
 echo htmlspecialchars(stripslashes($row['name']));
 echo "</strong><br />SKU: ";
 echo stripslashes($row['sku']);
 echo "<br />Quantity: ".$_SESSION["qty_cart"][$i]."";
 echo " | Price Per Unit: $";
 $price = $row['price'];
 echo stripslashes($price);
 echo " | Total Price: $";
 $total_price = $price * $_SESSION["qty_cart"][$i];
 array_push($_SESSION['prc_cart'], $total_price);
 echo $total_price;

 $temp_total += $total_price;
 }

 // display grand total
 if ($_SESSION["grand_total"] != $temp_total)
 {
 $_SESSION["grand_total"] += $temp_total;
 }

 echo "<br>------------------------------";
 echo "<br>Grand Total Price: $";
 echo $_SESSION["grand_total"];

 // button to checkout
 echo '<form action="receipt.php" method="post">';
 echo '<input type="submit" name="submit" value="Checkout">';
 echo '</form>';
 // return to home
 echo '<br>';
 echo '<a href="home.html">Return to home</a>';
 $result->free();
 $db->close();
?>
</body>
</html>