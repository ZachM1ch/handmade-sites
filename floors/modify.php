<html>
<head>
 <title>Heigh's Flooring and Tile House Repository</title>
</head>
<body>
 <h1>Heigh's Flooring and Tile House Repository - Modify Product</h1>
<?php
 // variables
 $prod_sku=$_POST['prod_sku'];

 // confirmation
 echo "<h2>Modifying product of SKU: $prod_sku<h2>";
 // form of fields to modify
 echo '<form action="modify_confirm.php" method="post">';
 echo '<table border="0">';
 echo '<tr>';
 echo '<td>Price $</td>';
echo '<td><input type="text" name="price" maxlength="8" size="8"></td>';
 echo '</tr>';
 echo '<tr>';
 echo '<td>Name</td>';
 echo '<td> <input type="text" name="name" maxlength="100" size="100"></td>';
 echo '</tr>';
 echo '<tr>';
 echo '<td>Color</td>';
echo '<td> <input type="text" name="color" maxlength="50" size="50"></td>';
 echo '</tr>';
 echo '<tr>';
 echo '<td>Category</td>';
echo '<td><input type="text" name="category" maxlength="30" size="30"></td>';
 echo '</tr>';
 echo '<tr>';
 echo '<td>Quantity</td>';
echo '<td><input type="text" name="quantity_available" maxlength="4"
size="4"></td>';
 echo '</tr>';
 echo '<tr>';
 echo '<td>Image Name</td>';
echo '<td><input type="text" name="image" maxlength="16" size="16"></td>';
 echo '</tr>';
 echo '<tr>';
 echo '<td colspan="2"><input type="submit" value="Register"></td>';
 echo '</tr>';
 echo '</table>';
 echo '<input type="hidden" name="sku" value="'.$prod_sku.'">';
 echo '</form>';

 // return to dash
 echo '<br>';
 echo '<a href="dashboard.html">Return to dashboard</a>';
?>
</body>
</html>