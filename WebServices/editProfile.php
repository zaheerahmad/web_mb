<?php


		include 'DBLayer.php';

		error_reporting(E_ERROR | E_PARSE);
		
		$fname = $_REQUEST["fname"];
		$lname = $_REQUEST["lname"];
		$dname = $_REQUEST["dname"];
		$email = $_REQUEST["email"];
		$gender = $_REQUEST["gender"];
		$dob = $_REQUEST["dob"];
		$oldPass = $_REQUEST["oldPass"];
		$newPass = $_REQUEST["newPass"];

		
		echo editProfile($fname,$lname,$dname,$email,$gender,$dob,$oldPass,$newPass);
?>