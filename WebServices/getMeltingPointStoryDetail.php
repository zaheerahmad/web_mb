<?php

	include 'DBLayer.php';
    error_reporting(E_ERROR | E_PARSE);
    
    $postID = $_REQUEST["id"];
    echo getMeltingPointStoryDetail($postID);

?>