<?php


		include 'DBLayer.php';

		error_reporting(E_ERROR | E_PARSE);
		
		$memberID = $_REQUEST["memberID"];
		
		echo getMemberInfo($memberID);
?>