<?php

		include 'DBLayer.php';
header("Access-Control-Allow-Origin: *");
		error_reporting(E_ERROR | E_PARSE);

    

		$platform = $_REQUEST["platform"];

		$alphabet=$_REQUEST["alphabet"];

		echo searchGames($platform,$alphabet);

?>
