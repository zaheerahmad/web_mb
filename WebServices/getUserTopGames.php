<?php



	require_once('DBLayer.php');
	header("Access-Control-Allow-Origin: *");
    error_reporting(E_ERROR | E_PARSE);

    

	$limit=$_REQUEST["limit"];
	$userID=$_REQUEST["userID"];

	

    echo getUserTopGames($limit,$userID);

	

	

?>



