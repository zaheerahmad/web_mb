<?php
    include 'DBLayer.php';
    error_reporting(E_ERROR | E_PARSE);
    
    $platform = $_REQUEST["platform"];
    echo getTopTenGames($platform);
?>