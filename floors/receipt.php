<?php
session_start();
?>
<html>
<head>
 <title>Heigh's Flooring and Tile House Repository</title>
</head>
<body>
<h1>Heigh's Flooring and Tile House Repository - Order Confirmation</h1>
<?php

 // variables
 $ordernum = rand(100000000,999999999);
 $date = date("m-d-Y");

 // access database
 @ $db = new mysqli('localhost', 'u565780247_heigh_employee', 'fl00rAndTile',
'u565780247_floor_store');
 if (mysqli_connect_errno())
 {
 echo 'Error: Could not connect to database. Please try again later.';
 exit;
 }

 // insert new product into db
 $query = "insert into orders values
 ('".$ordernum."', '".$date."', '".$_SESSION['grand_total']."',
'".$_SESSION['userid']."')";
 $result = $db->query($query);

 // update product quantities
 for ($i=0; $i < count($_SESSION["sku_cart"]); $i++)
 {
 $sku = $_SESSION["sku_cart"][$i];

 $query = "select * from products where sku = '$sku'";
 $result = $db->query($query);
 $row = $result->fetch_assoc();

 $quant = $row['quantity_available'];
 $quant -= $_SESSION["qty_cart"][$i];

 $query = "update products set quantity_available = $quant where sku = '$sku'";
 $result = $db->query($query);

 $query = "insert into contents values
 (DEFAULT, '".$ordernum."', '".$sku."', '".$_SESSION['qty_cart'][$i]."',
'".$_SESSION['prc_cart'][$i]."')";
 $result = $db->query($query);
 }

 // display order number
 echo "<br>Your order number is ";
 echo $ordernum;
 echo "<br>";

 // display confirmation email info
 $userid = $_SESSION['userid'];
 $query = "select * from customer where username = '$userid'";
 $result = $db->query($query);
 $row = $result->fetch_assoc();
 $email = $row['email'];
 echo "A confirmation email has been sent to $email";

 // reset session vars
 $_SESSION["sku_cart"] = array();
 $_SESSION["qty_cart"] = array();
 $_SESSION["prc_cart"] = array();
 $_SESSION["grand_total"] = 0;
 // return to home
 echo '<br>';
 echo '<a href="home.html">Return to home</a>';
 $result->free();
 $db->close();
?>
</body>
</html>