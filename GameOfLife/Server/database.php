<?php
/**
 * Description of db
 *
 * @author Mavin Müller & Paul Scheel
 * (c) Benjamin Reichelt
 */
class dataBase
{
    private $Content;
    //Einstellungen für XAMPP
    private $dbName   = "pbs2h15amu_gol";
    private $linkName = "mysqlpb.pb.bib.de";
    private $user     = "pbs2h15amu";
    private $pw       = "hZtNe7Pe";
    public function __construct($_content)
    {
        $this->Content = $_content;
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
    // WORKING
    public function createUser($name, $pw)
    {
        $db = $this->linkDB();
        $uid = $this->createGUID();
        $score = 0;
        $options = ['cost' => 11];
        $salt = uniqid(mt_rand(), true);
        $hash = password_hash($pw.$salt, PASSWORD_BCRYPT, $options);
        
        if($stmt = $db->prepare("INSERT INTO user VALUES (?,?,?,?,?)")){
        
            $stmt->bind_param("sssis",$uid, $name, $hash, $score, $salt);
            $stmt->execute();
        }        
        else
        {
            var_dump($db->error);
        }
    }
    // WORKING
    public function loginUser($name, $pw){
        $db = $this->linkDB();
        $resultFeedback = false;
        if ($stmt = $db->prepare("SELECT pw, salt FROM user WHERE name=?"))
        {
            $stmt->bind_param("s",$name);
            $queryFeedback = $stmt->execute();
            $resultFeedback = false;
            $stmt->store_result();
            if($stmt->num_rows == 1)
            {
                $options = ['cost' => 11];
                $stmt->bind_result($pwFromDb,$salt);
                $stmt->fetch();
                
                if (password_verify($pw . $salt, $pwFromDb)) 
                {
                    $resultFeedback = true;
                }
            }
            $stmt->free_result();
        }
        else { var_dump($db->error); }
        return $resultFeedback;
    }

    public function addUserBoard($name, $name, $sid)
    {
        $guid = $this->createGUID();
        $db = $this->linkDB();

        $bid = getCurrentUserID();

        $sid = "12831ß9208371ß2809381";

        $sql = "INSERT INTO board (bid, boardstate, name, sid, uid) VALUES";
        $sql .= "(\"{$bid}\",\"{$blob}\",\"{$name}\",\"{$sid}\",\"{$uid}\")";

        $blob = fopen("http://pbs2h15ash.webpb.pb.bib.de/GameOfLife/Server/tmp.txt", 'rb');
      
        $stmt = $this->pdo->prepare($sql);
 
        $stmt->bindParam(':mime', $mime);
        $stmt->bindParam(':data', $blob, PDO::PARAM_LOB);
 
        return $stmt->execute();
    }

    /*public function setUserProgress($uid)
    {
        $uid = $userProgress['uid'];

       $blob = fopen($filePath, 'rb');
 
        $sql = "INSERT INTO files(mime,data) VALUES(:mime,:data)";
        $stmt = $this->pdo->prepare($sql);
 
        $stmt->bindParam(':mime', $mime);
        $stmt->bindParam(':data', $blob, PDO::PARAM_LOB);
 
        return $stmt->execute();
    } */

    //WORKING
    public function getCurrentUserID()
    {
        $db = $this->linkDB();
        $name = $this->Content->userName;

        if($stmt = $db->prepare("SELECT uid FROM user WHERE name=?"))
        {
            $stmt->bind_param("s",$name);
            $stmt->execute();
            $stmt->store_result();

            if($stmt->num_rows == 1)
            {
                $stmt->bind_result($currentUid);

                $stmt->fetch();
                return $currentUid;
            }
            else
            {   
                var_dump("Current User Name: " . $name . " <br><br>");
                var_dump("Number of rows from statement " .$stmt->num_rows . " <br><br>");
            }
        }        

        return "HURZ in getCurrentUserID";
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