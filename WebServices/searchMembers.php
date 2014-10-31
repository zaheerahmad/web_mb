<?php


		include 'DBLayer.php';

		error_reporting(E_ERROR | E_PARSE);
		
		$criteria = $_REQUEST["criteria"];
		$key = $_REQUEST["key"];

		
		echo searchMembers($criteria,$key);
?>