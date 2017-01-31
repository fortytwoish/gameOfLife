<?php

/**
 * Description of db
 *
 * @author Benjamin Reichelt
 */
class dataBase {
    //Einstellungen für XAMPP
    private $dbName = "pbs2h15amu";
    private $linkName = "mysqlpb.pb.bib.de";
    private $user = "pbs2h15amu";
    private $pw = "hZtNe7Pe";
    
    public function __construct()
	{
		
    }
    
    public function selectFromDB($sql) {
        $db = $this->linkDB();
        $result = mysqli_query($db, $sql);
		$fehler = mysqli_error($db);
    	$db->close();
        
        if ($fehler != '') {
            echo "Fehler: " .$fehler;
        }
        
        return $result;
    }
    
    public function insertIntoDB($sql) {
        $db = $this->linkDB();
        mysqli_query($db, $sql);
		$fehler = mysqli_error($db);
    	$db->close();
        
        return $fehler;
    }

    public function linkDB() {
        $db = new \mysqli($this->linkName, $this->user, $this->pw);
 
        if ($db->connect_error) {
            die('Connect Error (' . $db->connect_errno . ') ' . $db->connect_error);
        }

        mysqli_select_db($db, $this->dbName);
            
        return $db;
    }
    
    public function createGUID() {
        $data = openssl_random_pseudo_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
