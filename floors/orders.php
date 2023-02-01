<?php
session_start();
?>
<html>
<head>
 <title>Heigh's Flooring and Tile House Repository</title>
</head>
<body>
<h1>Heigh's Flooring and Tile House Repository - Orders</h1>
<?php
 // variables
 $userid = $_SESSION["userid"];
 // access database
 @ $db = new mysqli('localhost', 'u565780247_heigh_employee', 'fl00rAndTile',
'u565780247_floor_store');
 if (mysqli_connect_errno())
 {
 echo 'Error: Could not connect to database. Please try again later.';
 exit;
 }

 // find number of orders
 $query1 = "select * from orders where user = '$userid'";
 $result1 = $db->query($query1);
 $num_results = $result1->num_rows;
 // display order
 for ($i=0; $i < $num_results; $i++)
 {

 $row1 = $result1->fetch_assoc();
 $ordernum = $row1['ordernum'];
 echo "<br><strong>Order ".$row1['ordernum']." on ".$row1['order_date']."</strong><br>";

 // join three tables to get product info for each order
 $query = "select distinct products.sku, products.name, products.price, contents.cost,
contents.quantity from orders join contents on contents.order_num = '$ordernum' and orders.user =
'$userid' join products on products.sku = contents.sku";
 $result = $db->query($query);
 $number_results = $result->num_rows;

 // display product info
 for ($j=0; $j < $number_results; $j++)
 {
 $row = $result->fetch_assoc();

 echo "<br>";
 echo "<em>".($j+1).". " .$row['name']. "</em>";
 echo "<br />SKU: ";
 echo stripslashes($row['sku']);
 echo "<br />Quantity: ";
 echo stripslashes($row['quantity']);
 echo " | Price Per Unit: $";
 echo stripslashes($row['price']);
 echo " | Total Price: $";
 echo stripslashes($row['cost']);
 echo "<br>";

 }

 // display grand total for an order
 echo "---------------------------------------";
 echo "<br>Grand Total Price: $";
 echo $row1['total_cost'];
 echo "<br>---------------------------------------<br>";
 }

 // return to home
 echo '<br>';
 echo '<a href="home.html">Return to home</a>';
 $result->free();
 $db->close();
?>
</body>
</html>