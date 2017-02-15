<?php
    include 'config.php';
    error_reporting(E_ALL);
    $responseStatus = '200 OK';
    $responseText = '';
    $boardContent;
    //TODO: Check if already existing Write first then
    $myfile = fopen("tmpfile.txt", "ab");
    foreach ($_POST as $key => $value){
        fwrite($myfile, $key . " " . $value . "\r\n");
    }
    fclose($myfile);
    $reponseStatus = '204 No Content';
    header($_SERVER['SERVER_PROTOCOL'].' '.$responseStatus);
    header('Content-type: text/html; charset=utf-8');
    echo $responseText;
?>