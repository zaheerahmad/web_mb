<?php



		include 'DBLayer.php';
header("Access-Control-Allow-Origin: *");
		error_reporting(E_ERROR | E_PARSE);



		$pollID = $_REQUEST["pollID"];

		$answers = $_REQUEST["answer"];

 



		echo updatePollResults($pollID,$answers);



?>