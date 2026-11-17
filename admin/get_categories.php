<?php
include("../config/db.php");

if(isset($_POST['section'])){
    $section = $_POST['section'];

    $result = mysqli_query($conn, "SELECT * FROM categories WHERE home_section='$section'");

    echo '<option value="">Select Category</option>';

    while($row = mysqli_fetch_assoc($result)){
        echo '<option value="'.$row['id'].'">'.$row['category_name'].'</option>';
    }
}
?>