<?php

		

		require_once ('DBLayer.php');

		header("Access-Control-Allow-Origin: *");

		

		error_reporting(E_ERROR | E_PARSE);

		

		$username=$_REQUEST["userid"];

		$key=$_REQUEST["verified"];

		

		$ip = $_SERVER['REMOTE_ADDR'];

		$details = json_decode(file_get_contents("http://ipinfo.io/{$ip}/json"));

		

		$tz= get_time_zone($details->country,$details->region);	

		

		$check= userActivation($username,$key,$tz);

		

		

		if($check == 0)

		{

				echo "Activation Successfull";

				

		}

		else if ($check == 1)

		{

				echo "Activation NOT Successfull";

				

		}

		else if ($check == 2)

		{

				echo "Unauthorized Activation Key";

		}

		else if ($check == 3)

		{

				echo "Account Already Activated";

		}



?>