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
    private $dbName   = "u373049832_gol";
    private $linkName = "mysql.hostinger.com";
    private $user     = "u373049832_mamue";
    private $pw       = "QTDxSWls1xjgXPpowU";

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
        $achievementString = "";

        for($i = 0; $i <200; $i++) $achievementString .= "0";

        if($stmt = $db->prepare("INSERT INTO user VALUES (?,?,?,?,?,?)"))
        {
            $stmt->bind_param("sssiss",$uid, $name, $hash, $score, $salt, $achievementString);
            $stmt->execute();
        }
        else
        {
            var_dump($db->error);
        }

        if(count($stmt->error_list) > 0){
            return false;
        }
        else{
            $this->setUserProgress("",15,0,$name);
            return true;
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
        else { var_dump($db->error . "  stmt_error:" . $stmt->error); }
        return false;
    }

    //WORKING
    public function getCurrentUserID($username = "")
    {
        $db = $this->linkDB();

        if($username == ""){
            $username = $_SESSION['username'];
        }

        if($stmt = $db->prepare("SELECT uid FROM user WHERE name=?"))
        {
            $stmt->bind_param("s",$username);
            $stmt->execute();
            $stmt->store_result();

            if($stmt->num_rows == 1)
            {
                $stmt->bind_result($currentUid);

                $stmt->fetch();
                return $currentUid;
            }
            else { /*var_dump("Current/Entered User Name: "    . $name . " ");*/ }
        }
        return "Error in 'getCurrentUserID'";
    }

    public function getSidByDimension($dimension)
    {
        $db = $this->linkDB();

        if ($stmt = $db->prepare("SELECT sid FROM size WHERE dimension=?"))
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
        else {  var_dump($db->error . "  stmt_error:" . $stmt->error); }
        return null;
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
        else {  var_dump($db->error . "  stmt_error:" . $stmt->error); }
        return null;
    }

    public function addCurrentUserBoard($board,$boardName,$boardDim,$money)
    {
        $bid    = $this->createGUID();
        $db     = $this->linkDB();
        $userId = $this->getCurrentUserID();
        $sid    = $this->getSidByDimension($boardDim);

        if($stmt = $db->prepare("INSERT INTO board VALUES (?,?,?,?,?,?)")){

            $stmt->bind_param("sssssi",$bid, $board, $boardName, $sid, $userId,$money);
            ($stmt->execute());

            if(count($stmt->error_list) > 0)
            {
                return false;
            }

            return true;
        }
        else { return false; }
    }

    public function updateUserBoard($boardName,$board,$money)
    {
        $db      = $this->linkDB();

        if($stmt = $db->prepare("UPDATE board SET boardstate=?, money=? WHERE boardname=?")){

            $stmt->bind_param("sss",$board,$money,$boardName);
            ($stmt->execute());

            if(count($stmt->error_list) > 0)
            {
                return false;
            }

            return true;
        }
        else { return false; }
    }

    public function getBoardID($boardName){

        $uid    = $this->getCurrentUserID($_SESSION['username']);
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
        else {  var_dump($db->error . "  stmt_error:" . $stmt->error); }
        return "Error in 'getBoardID'";
    }

    public function setUserProgress($board,$boardDim,$currentScore,$username="")
    {
        //Check for BID solution, for debug hardcoded!

        $db     = $this->linkDB();
        if($username != "")
            $currentUser = $this->getCurrentUserID($username);
        else
            $currentUser = $this->getCurrentUserID();

        $currentSid ="";
        $currentSizePoints = "";

        if($stmt1 = $db->prepare("SELECT sid from size where dimension=?")){

             $stmt1->bind_param("s",$boardDim);
             $stmt1->execute();

             $stmt1->store_result();
             $stmt1->bind_result($sid);
             $stmt1->fetch();
             $stmt1->free_result();
             $currentSid = $sid;
         }

        if($stmt = $db->prepare("SELECT score FROM progress WHERE sid=? AND uid=?")){

            $stmt->bind_param("ss",$currentSid, $currentUser);
            $stmt->execute();

            $stmt->store_result();
            $stmt->bind_result($score);
            $stmt->fetch();
            $stmt->free_result();

            $currentSizePoints = $score;
        }

        if($currentSizePoints == NULL){
            $startScore = 0;
            if($stmt = $db->prepare("INSERT INTO progress VALUES (?,?,?)"))
            {
                $stmt->bind_param("sis",$currentSid,$startScore,$currentUser);
                ($stmt->execute());
                return true;
            }
            else{
                echo "Progress Update FAILED";
                return false;
            }
        }
        else if($currentScore > $currentSizePoints){

            if($stmt = $db->prepare("UPDATE progress SET score=? WHERE uid=? and sid=?"))
            {
                $stmt->bind_param("iss",$currentScore,$currentUser,$currentSid);
                ($stmt->execute());
                return true;
            }
            else{
                echo "boardProgress Update FAILED";
                return false;
            }
        }

        echo 'Nothing to update';
        return false;
    }

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
        // else {  var_dump($db->error . " stmt_error:" . $stmt->error); }
        return "Error in 'getLeaderBoard()'". $stmt->error;
    }

    public function getUserProgress($uid = "")
    {
        if($uid == "") $uid = $this->getCurrentUserID();

        $db = $this->linkDB();

        if ($stmt = $db->prepare("SELECT p.score, s.description FROM progress p, size s WHERE uid =? AND p.sid = s.sid"))
        {
            $resultArr = array();

            $stmt->bind_param("s",$uid);
            $stmt->execute();
            $stmt->store_result();

            $stmt->bind_result($score,$descr);

            while ($stmt->fetch())
            {
                $resultArr[$descr] =$score;
            }

            $stmt->free_result();
            return $resultArr;
        }
        else {  var_dump($db->error . "  stmt_error:" . $stmt->error); }
        return "Error in 'getUserProgress'";
    }

    public function getAchievements()
    {
        $uid = $this->getCurrentUserID();

        $db = $this->linkDB();

        if ($stmt = $db->prepare("select achievements FROM user WHERE uid = ?"))
        {
            $stmt->bind_param("s",$uid);
            $stmt->execute();
            $stmt->store_result();

            $stmt->bind_result($achievement);

            $stmt->fetch();

            $stmt->free_result();

            return $achievement;
        }
        else {  var_dump($db->error . "  stmt_error:" . $stmt->error); }
        return "Error in 'getUserProgress'";
    }

    public function updateAchievements($achievementString)
    {
        $uid = $this->getCurrentUserID();

        $db = $this->linkDB();

        if($stmt = $db->prepare("UPDATE user SET achievements=? WHERE uid=?"))
        {
            $stmt->bind_param("ss",$achievementString,$uid);
            ($stmt->execute());
            return true;
        }
        else{
            echo "achievement Update FAILED";
            return false;
        }
    }

    public function getSizes(){

        $uid = $this->getCurrentUserID();

        $db = $this->linkDB();

        if ($stmt = $db->prepare("select progress.score, size.description from progress, size WHERE progress.uid = ? AND progress.sid = size.sid"))
        {
            $resultArr = array();

            $stmt->bind_param("s",$uid);
            $stmt->execute();
            $stmt->store_result();

            $stmt->bind_result($score,$description);

            while ($stmt->fetch())
            {
                array_push($resultArr, $description.' ('.$score.' max score)');
            }

            $stmt->free_result();
            return $resultArr;
        }
        else {  var_dump($db->error . "  stmt_error:" . $stmt->error); }
        return "Error in 'getUserProgress'";
    }

    public function getBoardNamesAndSizes(){

        $uid = $this->getCurrentUserID();

        $db = $this->linkDB();

        if ($stmt = $db->prepare("SELECT boardname, s.description FROM board b, size s WHERE uid =? AND b.sid = s.sid"))
        {
            $resultArr = array();

            $stmt->bind_param("s",$uid);
            $stmt->execute();
            $stmt->store_result();

            $stmt->bind_result($name, $size);

            while ($stmt->fetch())
            {
                array_push($resultArr, $name.' - '.$size);
            }

            $stmt->free_result();
            return $resultArr;
        }
        else {  var_dump($db->error . "  stmt_error:" . $stmt->error); }
        return "Error in 'getUserProgress'";
    }

    public function getBoard($boardName){

        $uid = $this->getCurrentUserID();

        $db = $this->linkDB();

        if ($stmt = $db->prepare("SELECT boardstate FROM board WHERE boardname=?"))
        {

            $stmt->bind_param("s",$boardName);
            $stmt->execute();
            $stmt->store_result();

            $stmt->bind_result($board);

            $stmt->fetch();
            $stmt->free_result();
            return $board;
        }
        else {  var_dump($db->error . "  stmt_error:" . $stmt->error); }
        return "Error in 'getBoard'";
    }
}
?>