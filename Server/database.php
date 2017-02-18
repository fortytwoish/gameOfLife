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
        $db     = $this->linkDB();
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
        $db     = $this->linkDB();
        mysqli_query($db, $sql);
		$errors = mysqli_error($db);
    	$db->close();
        return $errors;
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
        $db      = $this->linkDB();
        $uid     = $this->createGUID();
        $score   = 0;
        $options = ['cost' => 11];
        $salt    = uniqid(mt_rand(), true);
        $hash    = password_hash($pw.$salt, PASSWORD_BCRYPT, $options);

        if($stmt = $db->prepare("INSERT INTO user VALUES (?,?,?,?,?)"))
        {

            $stmt->bind_param("sssis",$uid, $name, $hash, $score, $salt);
            $stmt->execute();
        }
        else
        {
            var_dump($db->error);
        }
    }
    // WORKING
    public function loginUser($name, $pw)
    {
        $db = $this->linkDB();

        if ($stmt = $db->prepare("SELECT pw, salt FROM user WHERE name=?"))
        {
            $stmt->bind_param("s",$name);
            $stmt->execute();
            $stmt->store_result();

            if($stmt->num_rows == 1)
            {
                $stmt->bind_result($pwFromDb,$salt);
                $stmt->fetch();
                $stmt->free_result();
                return (password_verify($pw . $salt, $pwFromDb));
            }
        }
        else { var_dump($db->error . " <br><br> stmt_error:" . $stmt->error); }
        return false;
    }

    //WORKING
    public function getCurrentUserID($username = "")
    {
        $db = $this->linkDB();

        if($username == ""){
            $name = $this->Content->userName;
        }

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
            else { var_dump("Current/Entered User Name: "    . $name . " <br><br>"); }
        }
        return "Error in 'getCurrentUserID'";
    }

    public function getSidDescriptionByDimension($dimension)
    {
        $db = $this->linkDB();

        if ($stmt = $db->prepare("SELECT description FROM size WHERE dimension=?"))
        {
            $stmt->bind_param("i",$dimension);
            $stmt->execute();
            $stmt->store_result();
            if($stmt->num_rows == 1)
            {
                $stmt->bind_result($sid);
                $stmt->fetch();
                $stmt->free_result();
                return $sid;
            }
        }
        else {  var_dump($db->error . " <br><br> stmt_error:" . $stmt->error); }
        return null;
    }

    public function addCurrentUserBoard($board,$boardName,$boardDim,$score)
    {
        $bid    = $this->createGUID();
        $db     = $this->linkDB();
        $userId = $this->getCurrentUserID();
        $sid    = $this->getSidByDimension($boardDim);

        var_dump($bid, $userId, $sid);

        echo "<br><br>started to add User Board";

        if($stmt = $db->prepare("INSERT INTO board VALUES (?,?,?,?,?,?)")){

            $stmt->bind_param("sssssi",$bid, $board, $boardName, $sid, $userId,$score);
            ($stmt->execute());

            return true;
        }
        else { var_dump($db->error); return false; }
    }

    //TODO: ENFORCE UNIQUE NAMES PER PLAYER
    public function getBoardID($boardName){

        $uid    = $this->getCurrentUserID();
        $db     = $this->linkDB();

        if ($stmt = $db->prepare("SELECT bid FROM board WHERE uid=? AND name=?"))
        {
            $stmt->bind_param("ss",$uid,$boardName);
            $stmt->execute();
            $stmt->store_result();
            if($stmt->num_rows == 1)
            {
                $stmt->bind_result($bid);
                $stmt->fetch();
                $stmt->free_result();
                return $bid;
            }
        }
        else {  var_dump($db->error . " <br><br> stmt_error:" . $stmt->error); }
        return "Error in 'getBoardID'";
    }

    //TODO: get $bid properly
    public function setUserProgress($board,$boardDim,$score)
    {
        //Check for BID solution, for debug hardcoded!
        $bid    = "547156ae-0088-4537-ae6c-2589e83b6cce";
        $db     = $this->linkDB();

        if($stmt = $db->prepare("UPDATE board SET boardstate=?, score=? WHERE bid=?"))
        {
            $stmt->bind_param("sis",$board,$score,$bid);
            ($stmt->execute());
            return true;
        }
        else
        {
            var_dump($db->error);
            return false;
        }
    }

    //net schön aber wegweisend
    //TODO: Set OutputStyle the way demanded!!!
    public function getLeaderBoard()
    {
        $db = $this->linkDB();

        if ($stmt = $db->prepare("SELECT progress.score,user.name,size.description FROM progress, user, size WHERE progress.uid = user.uid AND progress.sid = size.sid ORDER BY score desc"))
        {
            $results_array = array();
            $stmt->execute();
            $result = $stmt->get_result();

            while ($row = $result->fetch_assoc()) {
                $results_array[] = $row;
            }

            $stmt->free_result();
            return $results_array;
        }
        // else {  var_dump($db->error . "<br><br> stmt_error:" . $stmt->error); }
        return "Error in 'getLeaderBoard()'". $stmt->error;
    }

    public function getUserProgress($uid = ""){

        if($uid == "") $uid = $this->getCurrentUserID();

        $db = $this->linkDB();

        if ($stmt = $db->prepare("SELECT score,sid FROM progress WHERE uid =?"))
        {
            $resultArr = array();

            $stmt->bind_param("s",$uid);
            $stmt->execute();
            $stmt->store_result();

            $stmt->bind_result($score,$sid);

            while ($stmt->fetch())
            {
                array_push($resultArr, $score, $sid);
            }

            $stmt->free_result();
            return $resultArr;
        }
        else {  var_dump($db->error . " <br><br> stmt_error:" . $stmt->error); }
        return "Error in 'getUserProgress'";
    }

    public function getSizes(){

        $uid = $this->getCurrentUserID();

        $db = $this->linkDB();

        if ($stmt = $db->prepare("select max(progress.score), size.description from progress, size WHERE progress.uid = ? AND progress.sid = size.sid"))
        {
            $resultArr = array();

            $stmt->bind_param("s",$uid);
            $stmt->execute();
            $stmt->store_result();

            $stmt->bind_result($score,$description);

            while ($stmt->fetch())
            {
                array_push($resultArr, $score, $description);
            }

            $stmt->free_result();
            return $resultArr;
        }
        else {  var_dump($db->error . " <br><br> stmt_error:" . $stmt->error); }
        return "Error in 'getUserProgress'";
    }

    public function getBoards(){

        $uid = $this->getCurrentUserID();

        $db = $this->linkDB();

        if ($stmt = $db->prepare("SELECT boardname FROM board WHERE uid =?"))
        {
            $resultArr = array();

            $stmt->bind_param("s",$uid);
            $stmt->execute();
            $stmt->store_result();

            $stmt->bind_result($score,$sid);

            while ($stmt->fetch())
            {
                array_push($resultArr, $score, $sid);
            }

            $stmt->free_result();
            return $resultArr;
        }
        else {  var_dump($db->error . " <br><br> stmt_error:" . $stmt->error); }
        return "Error in 'getUserProgress'";
    }

    public function getBoard($boardName){

        $uid = $this->getCurrentUserID();

        $db = $this->linkDB();

        if ($stmt = $db->prepare("SELECT board FROM board WHERE boardname =? AND uid = ?"))
        {

            $stmt->bind_param("ss",$boardName,$uid);
            $stmt->execute();
            $stmt->store_result();

            $stmt->bind_result($board);

            $stmt->fetch();
            $stmt->free_result();
            return $board;
        }
        else {  var_dump($db->error . " <br><br> stmt_error:" . $stmt->error); }
        return "Error in 'getBoard'";
    }
}
?>