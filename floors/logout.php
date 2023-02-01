<?php
session_start();
?>
<html>
<head>
 <title>Heigh's Flooring and Tile House Repository</title>
</head>
<body>
 <h1>Logging You Out</h1>
<?php

 // delete session vars
 session_unset();
 session_destroy();

 // confirmation and return to login
 echo "<h3>Success!</h3>";
 echo '<a href="index.html">Return to Login Page</a>';

?>
</body>
</html>