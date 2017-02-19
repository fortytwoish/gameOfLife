<?php

    error_reporting(E_ALL);
    $responseStatus = '200 OK';
    $responseText = '';
    $boardContent;
    //TODO: Check if already existing Write first then
    $myfile = fopen("tmpfile.txt", "ab");
    foreach ($_POST as $key => $value)
    {
        fwrite($myfile, $key . " " . $value . "\r\n");
    }
    fclose($myfile);
    $reponseStatus = '204 No Content';
    header($_SERVER['SERVER_PROTOCOL'].' '.$responseStatus);
    header('Content-type: text/html; charset=utf-8');
    echo $responseText;

    //iterate through text file
    $lineCount = 0;
    $gameDim   = null;
    $boardName = null;
    $money     = null;

    $rows = array();

    $myfile = fopen("tmpfile.txt", "r");
    if ($myfile)
    {
        while (($line = fgets($myfile)) !== false)
        {
            if(substr($line, 0, 1) == "!")
            {
                $metadata  = explode(";", substr($line, 1));
                $gameDim   = $metadata[0];
                $boardName = $metadata[1];
                $money     = $metadata[2];

                echo "found - gameDim: ".$gameDim;
            }
            else
            {
                $rest = substr($line, 4);
                $restWithoutLineEnd = substr($rest, 0, count($rest) - 3);
                $rows[intval(substr($line, 0, 3), 10)] = $restWithoutLineEnd;
            }

            $lineCount++;

            echo " lines: ".$lineCount;
        }

        if($gameDim != null && $boardName != null && $money != null && intval($gameDim) == ($lineCount - 1))
        {   //All packets have arrived.
            echo ('<br>'.$gameDim.", ".$boardName.", ".$money);

            $boardState = '';
            for($i = 0; $i < $gameDim; $i++)
            {
                $boardState.=$rows[$i];
            }

            include "database.php";
            $db = new dataBase(null);

            $db->updateUserBoard($boardName, $boardState, $money);

            file_put_contents("tmpfile.txt", "");
        }

        fclose($myfile);
    }

    //Check whether the line with ! is in yet
?>