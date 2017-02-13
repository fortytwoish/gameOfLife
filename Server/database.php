<?php

/**
 * Description of db
 *
 * @author Benjamin Reichelt
 */
class dataBase
{
    //Einstellungen für XAMPP
    private $dbName   = "pbs2h15amu_gol";
    private $linkName = "mysqlpb.pb.bib.de";
    private $user     = "pbs2h15amu";
    private $pw       = "hZtNe7Pe";

    public function __construct()
    {

    }

    public function selectFromDB($sql)
    {
        $db = $this->linkDB();
        $result = mysqli_query($db, $sql);
		$fehler = mysqli_error($db);
    	$db->close();

        if ($fehler != '')
        {
            echo "Fehler: " .$fehler;
        }

        return $result;
    }

    public function insertIntoDB($sql)
    {
        $db = $this->linkDB();
        mysqli_query($db, $sql);
		$fehler = mysqli_error($db);
    	$db->close();

        return $fehler;
    }

    public function linkDB()
    {
        $db = new \mysqli($this->linkName, $this->user, $this->pw);

        if ($db->connect_error)
        {
            die('Connect Error (' . $db->connect_errno . ') ' . $db->connect_error);
        }

        mysqli_select_db($db, $this->dbName);

        return $db;
    }

    public function createGUID()
    {
        $data = openssl_random_pseudo_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    public function addUser($userName, $userPw)
    {
        echo "test";
        $guid = $this->createGUID();

        $salt = rand(); /* todo: make this fit into bigint(64) uniqid(mt_rand(), true); */

        $sql = "INSERT INTO user (uid, name, password, salt, score) VALUES (\"{$guid}\",\"{$userName}\",\"{$userPw}\",\"{$salt}\",\"0\")";

        echo "<script>alert(\"{$this->insertIntoDB($sql)}\");</script>";
    }

    public function addUserBoard($boardArr)
    {
        $guid = $this->createGUID();

        $sql = "INSERT INTO board (bid, boardstate, name, sid, uid) VALUES";
        //$sql .= "('".$guid."','".$boardArr['board']."','".$boardArr['name']."','".$boardArr['sid']."','".$boardArr['uid']"'')";

        echo $sql;
    }

    public function setUserProgress($userProgress)
    {
        $guid = $userProgress['uid'];

        $sqlRequest = "UPDATE user SET score=".$userProgress['points']." WHERE uid='".$userProgress['uid']."'";

        return $sqlResult;
    }

    public function getUserGUID($userName)
    {
        $sqlRequest = "SELECT guid FROM user WHERE name='".$userName."'";

        $sqlResult = $this->selectFromDB($sqlRequest);

        return $sqlResult;
    }

    public function getSidByDimension($dimension)
    {
        $sqlRequest = "SELECT sid FROM size WHERE dimension=".$dimension;

        $sqlResult = $this->selectFromDB($sqlRequest);

        return $sqlResult;
    }

    public function getLeaderboard()
    {
        $sqlRequest = "SELECT name, score FROM user ORDER BY score desc";

        $sqlResult = $this->insertIntoDB($sqlRequest);

        return $sqlResult;
    }

    public function getMaxScore()
    {
        $sqlRequest = "SELECT dimension, maxscore FROM size";

        $sqlResult = $this->insertIntoDB($sqlRequest);

        return $sqlResult;
    }

    public function getUserProgress($uid){

        $sqlRequest = "SELECT sid, uid, score FROM progress WHERE uid='".$uid."'";

        $sqlResult = $this->insertIntoDB($sqlRequest);

        return $sqlResult;
    }
}
?>