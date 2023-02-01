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
 $quantity_desired=$_POST['quantity_desired'];
 $prod_sku=$_POST['prod_sku'];
 $qty_av=$_POST['qty_av'];
 // check if enough products are available to add to cart
 if ($quantity_desired > $qty_av)
 {
 echo 'You have added more product than is available to your cart. Please go back and try
again.';
 exit;
 }

 // append sku and quantity to session vars
 array_push($_SESSION["sku_cart"], "$prod_sku");
 array_push($_SESSION["qty_cart"], "$quantity_desired");

 // confirmation
 echo "<p>$quantity_desired product(s) added to cart.";
 // return to home
 echo '<br>';
 echo '<a href="home.html">Return to home</a>';
 $result->free();
 $db->close();
?>
</body>
</html>