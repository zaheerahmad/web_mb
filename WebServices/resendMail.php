<?php



			include 'DBLayer.php';

header("Access-Control-Allow-Origin: *");

			error_reporting(E_ERROR | E_PARSE);

			

			$uName=$_REQUEST["username"];

			$A_key=$_REQUEST["activation_key"];

			$mail=$_REQUEST["email"];

			

			echo resendMail($uName,$A_key,$mail);



?>