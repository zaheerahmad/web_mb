<?php



		include 'DBLayer.php';



		error_reporting(E_ERROR | E_PARSE);


		$profileType=$_REQUEST["profileType"];

		echo changePrivacy($profileType);



?>