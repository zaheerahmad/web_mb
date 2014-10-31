<?php



		include 'DBLayer.php';
		header("Access-Control-Allow-Origin: *");
		error_reporting(E_ERROR | E_PARSE);
		
		 $content = $_REQUEST["content"];
		 $item_id = $_REQUEST["item_id"];
		 $secondary_item_id = $_REQUEST["secondary_item_id"];



		echo commentOnActivity($content,$item_id,$secondary_item_id);



?>