<?php
include "db.php";
$id = $_POST["id"];
$ClientID = $_POST["ClientID"];

mysqli_query($conn, "UPDATE data_table SET ClientID='$ClientID' WHERE id=$id");
header("Location: index.php");
?>
