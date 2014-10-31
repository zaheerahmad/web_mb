<?php



		include 'DBLayer.php';
header("Access-Control-Allow-Origin: *");
		error_reporting(E_ERROR | E_PARSE);



		$topicTitle = $_REQUEST["title"];

		$topicContent = $_REQUEST["content"];

    	$forumID = $_REQUEST["forumID"];



		echo createTopic($topicTitle, $topicContent,$forumID);



?>