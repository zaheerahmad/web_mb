<?php



		include 'DBLayer.php';
		header("Access-Control-Allow-Origin: *");
		error_reporting(E_ERROR | E_PARSE);
		
		 $content = $_REQUEST["content"];
		 $receiverID = $_REQUEST["receiverID"];
		 $threadID = $_REQUEST["threadID"];
		 $subject = $_REQUEST["subject"];
		
		echo replyMsg($receiverID,$content,$threadID,$subject);



?>