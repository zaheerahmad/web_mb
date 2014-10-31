<?php

		include 'DBLayer.php';
header("Access-Control-Allow-Origin: *");
		error_reporting(E_ERROR | E_PARSE);

    

		$postID = $_REQUEST["id"];

		echo getTopicReplies($postID);

		



?>