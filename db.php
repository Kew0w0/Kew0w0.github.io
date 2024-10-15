<?php
$host = "localhost";
$u = "root";
$p = "";
$dbn = "food_list";

$db = mysqli_connect($host,$u,$p,$dbn);

if(!$db){
    echo "error" .mysqli_error($db);
}
?>