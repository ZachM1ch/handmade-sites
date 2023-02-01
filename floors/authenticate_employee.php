<html>
<head>
 <title>Heigh's Flooring and Tile House Repository</title>
</head>
<body>
<h1>Heigh's Flooring and Tile House Repository - Employee Login Authentication</h1>
<?php

 // variables
 $employeeid=trim($_POST['employeeid']);
 $password=trim($_POST['password']);
 // check if all vars are full
 if (!$employeeid || !$password)
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
 // get password for entered ssn
 $query = "select ssn, password from employee where ssn like '%".$employeeid."%'";
 $result = $db->query($query);
 $num_results = $result->num_rows;
 $row = $result->fetch_assoc();

 // confirm whether info is correct
 if ($num_results == 0 || $password != $row['password'])
 {
 echo 'The credentials you entered are invalid. Please go back and try again.';
 exit;
 }
 // go to dash
 echo "<h2>Success!</h2>";
 echo '<a href="dashboard.html">Continue to dashboard</a>';
 $result->free();
 $db->close();
?>
</body>
</html>