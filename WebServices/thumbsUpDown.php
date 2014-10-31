<?php



		include 'DBLayer.php';
		header("Access-Control-Allow-Origin: *");
		error_reporting(E_ERROR | E_PARSE);
		
		 $postID = $_REQUEST["postID"];
		 $votetype = $_REQUEST["votetype"];



		echo thumbsUpDown($postID,$votetype);



?>