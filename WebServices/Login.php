<?php



	include 'DBLayer.php';
header("Access-Control-Allow-Origin: *");
    error_reporting(E_ERROR | E_PARSE);

    $username=$_REQUEST["username"];

    $password=$_REQUEST["password"];

    echo Login($username, $password);






?>