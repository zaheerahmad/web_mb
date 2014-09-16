<?php
    
include 'response.php';
header('Content-type: application/json');
    
	session_start();
    $jsonResponse = new responsejson();
    
    $obj = new stdClass();
    
    if(isset($_SESSION['username'])) {
        $obj->isSession = "true";
        $obj->username = $_SESSION['username'];
        
        $jsonResponse->code = 0;
        $jsonResponse->status = "Success";
        $jsonResponse->message = "Session Found !";
        $jsonResponse->response = $obj;
        echo json_encode($jsonResponse);
    }
    else {
        $obj->isSession = "false";
        
        $jsonResponse->code = 1;
        $jsonResponse->status = "Failure";
        $jsonResponse->message = "Session not Found !";
        $jsonResponse->response = $obj;
        echo json_encode($jsonResponse);
        
        
       
    }
?>