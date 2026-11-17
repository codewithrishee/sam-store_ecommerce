<?php

include("../config/db.php");

$category_id = $_POST['category_id'];

$query = mysqli_query(
    $conn,
    "SELECT * FROM sub_categories 
    WHERE category_id='$category_id'"
);

echo '<option value="">Select Subcategory</option>';

while($row = mysqli_fetch_assoc($query)){
?>

<option value="<?= $row['id']; ?>">
<?= $row['name']; ?>
</option>

<?php } ?>