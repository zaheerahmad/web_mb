<?php
	
	include 'DBLayer.php';
    error_reporting(E_ERROR | E_PARSE);
    
    $limit = $_REQUEST["limit"];
    echo getTopMoviesAndShows($limit);

?>