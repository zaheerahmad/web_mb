<?php



		require_once ('DBLayer.php');
header("Access-Control-Allow-Origin: *");


		error_reporting(E_ERROR | E_PARSE);



		$gameProperties=$_REQUEST["game"];

		$genre=$_REQUEST["genre"];

		



		echo saveSurveyDraft($gameProperties,$genre);

		



?>