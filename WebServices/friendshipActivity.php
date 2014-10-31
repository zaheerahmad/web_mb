<?php


		include 'DBLayer.php';

		error_reporting(E_ERROR | E_PARSE);
		
		$friendID = $_REQUEST["friendID"];
		$code = $_REQUEST["code"];
		
		if($code == 1 || $code == 5)
		{
			echo unfriend($friendID,$code);
		}
		else if ($code == 2)
		{
			echo cancelFriendRequest($friendID,$code);
		}
		else if ($code == 3)
		{
			echo acceptFriend($friendID,$code);
		}
		else if($code == 4)
		{
			echo addFriend($friendID,$code);
		}
		
		

		
		
?>