<html>
<head>
 <title>Heigh's Flooring and Tile House Repository</title>
</head>
<body>
<h1>Heigh's Flooring and Tile House Repository - Create Account Confirmation</h1>
<?php
 // variables
 $username=$_POST['username'];
 $password=$_POST['password'];
 $fname=$_POST['fname'];
 $minit=$_POST['minit'];
 $lname=$_POST['lname'];
 $address=$_POST['address'];
 $email=$_POST['email'];
 // cheack if all fields are empty
 if (!$username || !$password || !$fname || !$minit || !$lname || !$address || !$email)
 {
 echo "You have not entered all the required details.<br />"
 ."Please go back and try again.";
 exit;
 }

 if (!get_magic_quotes_gpc())
 {
 $username = addslashes($username);
 $password = addslashes($password);
 $fname = addslashes($fname);
 $minit = addslashes($minit);
 $lname = addslashes($lname);
 $address = addslashes($address);
 $email = addslashes($email);
 }
 // access database
 @ $db = new mysqli('localhost', 'u565780247_heigh_employee', 'fl00rAndTile',
'u565780247_floor_store');
 if (mysqli_connect_errno())
 {
 echo "Error: Could not connect to database. Please try again later.";
 exit;
 }
 // insert new account into db
 $query = "insert into customer values
 ('".$username."', '".$password."', '".$fname."', '".$minit."', '".$lname."',
'".$address."', '".$email."')";
 $result = $db->query($query);
 // confirmation of insert
 if ($result)
 {
 echo "<h2>Success!<h2>";
 echo "<br>Account created!";
 }
 else
 {
 echo "An error has occurred. The item was not added.";
 }

 // return to dash
 echo '<br>';
 echo '<a href="login.html">Return to landing</a>';
 $result->free();
 $db->close();

?>
</body>
</html>