<?php

	include 'DBLayer.php';

    error_reporting(E_ERROR | E_PARSE);

    $username=$_REQUEST["username"];
    $password=$_REQUEST["password"];
	$email=$_REQUEST["email"];
	$dob=$_REQUEST["dob"];
	$gender=$_REQUEST["gender"];

     echo Register($username, $password,$email,$dob,$gender);
    

?>