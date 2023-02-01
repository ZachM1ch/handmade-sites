<?php
session_start();
?>
<html>
<head>
 <title>Heigh's Flooring and Tile House Repository</title>
</head>
<body>
<h1>Heigh's Flooring and Tile House Repository - Customer Login Authentication</h1>
<?php

 // variables
 $userid=trim($_POST['userid']);
 $password=trim($_POST['password']);

 // session variables
 $_SESSION["sku_cart"] = array();
 $_SESSION["qty_cart"] = array();
 $_SESSION["prc_cart"] = array();
 $_SESSION["grand_total"] = 0;
 $_SESSION["userid"] = $userid;
 // check if all vars are full
 if (!$userid || !$password)
 {
 echo 'You have not entered all your information. Please go back and try again.';
 exit;
 }
 // access database
 @ $db = new mysqli('localhost', 'u565780247_heigh_employee', 'fl00rAndTile',
'u565780247_floor_store');
 if (mysqli_connect_errno())
 {
 echo 'Error: Could not connect to database. Please try again later.';
 exit;
 }
 // get password for entered username
 $query = "select username, password from customer where username like '%".$userid."%'";
 $result = $db->query($query);
 $num_results = $result->num_rows;
 $row = $result->fetch_assoc();

 // confirm whether info is correct
 if ($result == 0 || $password != $row['password'])
 {
 echo 'The credentials you entered are invalid. Please go back and try again.';
 exit;
 }
 // go to dash
 echo "<h2>Success!</h2>";
 echo '<a href="home.html">Continue to home.</a>';
 $result->free();
 $db->close();
?>
</body>
</html>