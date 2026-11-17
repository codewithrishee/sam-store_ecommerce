<?php
$conn = mysqli_connect("localhost", "root", "", "lavistore");

if (!$conn) {
    die("Connection Failed: " . mysqli_connect_error());
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
?>