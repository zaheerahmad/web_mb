<?php



	include 'DBLayer.php';

    error_reporting(E_ERROR | E_PARSE);

    $username=$_REQUEST["username"];

    $password=$_REQUEST["password"];

    echo Login($username, $password);






?>