<?php

		include 'response.php';
header("Access-Control-Allow-Origin: *");
		header('Content-type: application/json');

    

		session_start();

		$jsonResponse = new responsejson();

    

		$obj = new stdClass();

    

    if(isset($_SESSION['username'])) 

	{

        $obj->username = $_SESSION['username'];

		

		unset($_SESSION['username']);

		session_destroy();

        

        $jsonResponse->code = 0;

        $jsonResponse->status = "Success";

        $jsonResponse->message = "Logged Out Successfully !";

		

		array_push($jsonResponse->response,$obj);

        echo json_encode($jsonResponse);

    }



?>