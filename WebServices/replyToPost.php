<?php



		include 'DBLayer.php';
header("Access-Control-Allow-Origin: *");
		error_reporting(E_ERROR | E_PARSE);



		$postContent = $_REQUEST["content"];

		$parentPostID = $_REQUEST["postID"];

    

		echo replyToPost($postContent, $parentPostID);



?>