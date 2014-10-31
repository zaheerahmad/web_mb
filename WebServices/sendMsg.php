<?php



		include 'DBLayer.php';
		header("Access-Control-Allow-Origin: *");
		error_reporting(E_ERROR | E_PARSE);
		
		 $content = $_REQUEST["content"];
		 $username = $_REQUEST["username"];
		 $subject = $_REQUEST["subject"];
		
		echo sendMsg($username,$content,$subject);



?>