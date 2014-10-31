<?php


		include 'DBLayer.php';

		error_reporting(E_ERROR | E_PARSE);
		
		$me = $_REQUEST["me"];
		$playing = $_REQUEST["playing"];
		$tag = $_REQUEST["tag"];

		
		echo updateYourself($me,$playing,$tag);
?>