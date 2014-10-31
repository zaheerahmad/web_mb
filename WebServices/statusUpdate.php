<?php



		include 'DBLayer.php';
		header("Access-Control-Allow-Origin: *");
		error_reporting(E_ERROR | E_PARSE);
		
		 $content = $_REQUEST["content"];
		 
		echo statusUpdate($content);



?>