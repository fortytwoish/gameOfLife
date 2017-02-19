<?php

session_start();

class Content
{
    //====================================================================================================
    //      Variables
    //====================================================================================================
	private $db;
    private $gameDim;
	private $isLoggedIn = false;

    public $userName     = null;
    public $currentBoard = array();

    //====================================================================================================
    //      Constructor
    //====================================================================================================

	public function __construct()
	{
		include 'database.php';

		$this->db = new dataBase($this);
        if(isset($_SESSION["username"]))
        {
          $this->userName = $_SESSION["username"];
        }
    }

    //====================================================================================================
    //      Page Navigation
    //====================================================================================================

	public function showNavigation($selected)
	{
       //Load CSS and JS files
       echo '<link rel="stylesheet" href="../Client/gameOfLife.css" type="text/css">';
       echo '<script src="../Client/gameOfLife_Logic.js"></script>';
       echo '<script src="../Client/gameOfLife_View.js"></script>';
       echo '<script src="../Client/uploadGame.js"></script>';

		$loginText = $this->userName == null
				   ? 'Login'
				   : 'Profile of '.$this->userName;

        $playText  = $this->userName == null
				   ? 'Free Play'
				   : 'Play';

        echo '	<div id="notificationBar" style="position:absolute; left: 0; top: 0; background: #6DD; height: auto; width: 90%; margin-left: 5%; visibility: hidden; text-align: center; padding: 10px; font-size: 40pt; color: white; box-shadow: 5px 5px 5px #444; border-bottom-left-radius: 25px; border-bottom-right-radius: 25px;"></div>
                <nav>
					<ul>
						<li>
							<a href="?do=showAccount"       class="'.($selected == 0
                                                                      ? "selectedNavItem"
                                                                      : "deselectedNavItem").'">'.$loginText.'</a>
						</li>
						<li>
							<a href="?do=showGameSelection" class="'.($selected == 1
                                                                      ? "selectedNavItem"
                                                                      : "deselectedNavItem").'">'.$playText.'</a>
						</li>
						<li>
							<a href="?do=showLeaderboard"   class="'.($selected == 2
                                                                      ? "selectedNavItem"
                                                                      : "deselectedNavItem").'">Leaderboard</a>
						</li>
					</ul>
				</nav>';
	}

	public function showWelcome()
	{
		$this->showNavigation(-1);
		echo '<h1>Welcome to Game Of Life</h1>';

        if(rand(0,1)==1)
            echo 'Created by <b>Paul Scheel</b> and <b>Marvin M&uuml;ller</b> in 2017';
        else
            echo 'Created by <b>Marvin M&uuml;ller</b> and <b>Paul Scheel</b> in 2017';
	}

	public function showLogin($values, $errors)
	{
		$this->showNavigation(0);

        $username = $values["username"];
        $password = $values["password"];

        $usernameErr = $errors["username"];
        $passwordErr = $errors["password"];

        echo '<form action="welcome.php" method="POST">
                <table>
                    <tr>
                        <td><b>Username</b></td>
                        <td><input type="text" name="username" value="'.$username.'"/> <span class="loginError">'.$usernameErr.'</span></td>
                    </tr>
                    <tr>
                        <td><b>Password</b></td>
                        <td><input type="password" name="password" value="'.$password.'"/> <span class="loginError">'.$passwordErr.'</span></td>
                    </tr>
                </table>
                <p>
                    <input type="submit" name="accountAction" value="Login"/>
                    <input type="submit" name="accountAction" value="Create"/>
                </p>
                <input type="hidden" name="do" value="accountAction"/>
              </form>';
	}

    public function showAccount()
    {
        $this->showNavigation(0);

        $boardArr = $this->db->getBoards();

        $logoutForm =  '<form action="welcome.php" method="POST">
                            <input type="submit" name="do" value="Logout" />
                            <p>showing account of user: '.$this->userName.'</p>
                        </form>';

        if(count($boardArr) == 0 ){
        $boardListHtml =     '<center><table>
                                <tr>
                                <th><h2>You don\'t have any Boards yet. Start playing NOW! </h2></th>
                                </tr>';
        }
        else
        $boardListHtml =  '<center><table>
                                <tr>
                                <th><h2>Your Current Boards:</h2></th>
                                </tr>';

        foreach ($boardArr as $boardRow)
        {
            $boardListHtml .="<tr><td>".$boardRow."</td></tr>";
        }
        $boardListHtml .= "</table>".$logoutForm."</center>";

        echo $boardListHtml;

    }

    public function showGameSelection()
    {

        var_dump($this->getUnlockedAchievements());


        $this->showNavigation(1);

        if($this->userName == null)
        { //Free Play - allow all sizes
            echo '  <b>Select a Board Size:</b>
                    <form action="welcome.php" method="POST">
                        <select name="boardSize">
                            <option value="XS">XS (15x15)</option>
                            <option value="S ">S  (50x50)</option>
                            <option value="M ">M  (100x100)</option>
                            <option value="L ">L  (200x200)</option>
                            <option value="XL">XL (500x500)</option>
                        </select>

                        <input type="submit" value="Play"/>
                        <input type="hidden" name="do" value="showGame"/>
                    </form>';
        }
        else
        { //Logged in User - allow only unlocked Sizes and display existing boards

            $sizeOptions = "";

            foreach ($this->db->getSizes() as $key => $value)
            {
                $sizeOptions .= '<option>'.$value.'</option>';
            }

            foreach ($this->db->getBoards() as $key => $value)
            {
                echo $key.' - '.$value.'<br>'; //TODO
                //$sizeOptions .= '<option>'.$value.'</option>';
            }

            echo '  <form action="welcome.php" method="POST">
                        <h3>Create a new Board</h3>
                        <table>
                            <tr>
                                <td>
                                    <b>Board Size:</b>
                                </td>
                                <td>
                                    <select name="boardSize" style="width:100%">
                                        '.$sizeOptions.'
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <b>Board Name:</b>
                                </td>
                                <td>
                                    <input type="text" name="newBoardName"/>
                                </td>
                            </tr>
                        </table>

                        <input type="submit" name="boardSelectOrCreate" value="Create"/>

                        <br/>

                        <h3>Choose an existing Board</h3>
                        <select name="existingBoardName">

                        </select>
                        <input type="submit" name="boardSelectOrCreate" value="Play"/>

                        <br/>

                        <input type="hidden" name="do" value="showGame"/>
                    </form>';
        }
    }

	public function showGame($boardSize)
	{
        $this->gameDim = $boardSize;

        $isFreePlay = ($this->userName == null);

        $result = file_get_contents("../cellPresetCoordinates_lowToHigh.json");


        if(!$isFreePlay) //If not in Free Play mode, filter by which sizes have been unlocked by the user
        {
            // 1. Get Unlocked Preset indices from the achievements
            $i                     = 0;
            $unlockedAchievements  = $this->getUnlockedAchievements();
            $cellpresets           = json_decode($result)->shapes;
            $unlockedPresetIndices = array();

            for($i = 12; $i < sizeof(get_object_vars($cellpresets)); $i++)
            {
                if(isset($unlockedAchievements[$i]))
                {
                    array_push($unlockedPresetIndices, $i - 12);
                }
            }


            // 2. Get Unlocked Presets from their indices
            $i               = 0;
            $unlockedPresets = array();

            foreach($cellpresets as $key => $value)
            {
                if(array_search($i, $unlockedPresetIndices) > -1)
                {
                    $unlockedPresets["shapes"][$key] = $value;
                }

                $i++;
            }

            $result = json_encode($unlockedPresets);
            var_dump($result);
        }

		$this->showNavigation(1);

		echo '<script type="text/javascript">
		        generateBoard('.$this->gameDim.', '.($isFreePlay ? "true" : "false").');
                setPresets('.$result.');
			  </script>';

		echo '<form action="welcome.php" method="POST">
                <table>
                    <tr>
                        <td><input type="submit" name="testDb" value="testDB"/></td>
                    </tr>
                </table>
                <input type="hidden" name="do" value="testDb"/>
              </form>';
    }

    //====================================================================================================
    //      Functions
    //====================================================================================================

    //--------------------------
    //  Account Page
    //--------------------------

    private function setUser($username)
    {
        $this->userName = $username;
        $_SESSION["username"] = $username;
        $_SESSION["token"] = rand();
    }

    public function login($username, $password)
    {
        //TODO: Database
        if($this->db->loginUser($username,$password))
        {
            $this->setUser($username);

            $this->showAccount();
        }
        else
            echo "<h2>Not logged in. Try Again!</h2>";
    }

    public function create($username, $password)
    {
        //echo $username.' your account has been created';
        if($this->db->createUser($username, $password))
        {
            $this->setUser($username);
            $this->showAccount();
        }
        else
        {
            $loginData = array( "username" => $username);
            $otherErrors = array("username"=>"Username already in use");
            $this->showLogin($loginData,$otherErrors);
        }
    }

    public function logout()
    {
        $this->setUser("");
        $this->showWelcome();
    }

    //--------------------------
    //  Game Page
    //--------------------------

    public function updateBoardtoDb(){

        $arr = "";
        $score = "1339";
        $handle = fopen("tmpfile.txt", "rb");

        if ($handle) {
            while (($line = fgets($handle)) !== false) {

                $line = substr($line,strrpos($line," "));
                $line = str_replace(" ", "",$line);
                $line = str_replace("/r/n", "",$line);
                $arr .=$line ."|";
            }
            file_put_contents("tmpfile.txt", "");
            fclose($handle);
        } else {
            var_dump("error in boardUPdate");
        }
   }

    public function sendBoardToDB(){

        $arr = "";
        $score = "1337";
        $handle = fopen("tmpfile.txt", "rb");

        //buildUp FileString
        if ($handle) {
            while (($line = fgets($handle)) !== false)
            {
                $line = substr($line,strrpos($line," "));
                $line = str_replace(" ", "",$line);
                $line = str_replace("/r/n", "",$line);
                $arr .=$line ."|";
            }
            file_put_contents("tmpfile.txt", "");
            fclose($handle);
        } else {
            // error opening the file.
        }

        //SET VALUES
        $tmp = $this->db->addCurrentUserBoard($arr,"tollesBoard",50,$score);
        echo 'Success? : ' . $tmp;
    }

    //--------------------------
    //  Leaderboard Page
    //--------------------------

    public function showLeaderboard()
    {
        $this->showNavigation(2);
        $this->getLeaderBoardArray();
    }

    public function getLeaderBoardArray()
     {
        $tableString = "";

        //dummy data to be replaced by:
        $leaderBoard = $this->db->getLeaderBoard();

        /*$leaderBoard[8][rank]   = "";
        $leaderBoard[8][uid]   = "98765";
        $leaderBoard[8][name]  = "testtrash";
        $leaderBoard[8][score]  = "123";*/

        $this->array_sort_by_column($leaderBoard, 'score');

        $leaderArray = array();

        $leaderArray[0]['Rank'] = "Rank";
        $leaderArray[0]['name'] = "Name";
        $leaderArray[0]['description'] = "BoardSize";
        $leaderArray[0]['score'] = "Score";

        $leaderCounter = 1;

        foreach($leaderBoard as $row){
            $leaderArray[$leaderCounter]['Rank']        = $leaderCounter . ".";
            $leaderArray[$leaderCounter]['name']        = $row['name'];
            $leaderArray[$leaderCounter]['description'] = $row['description'];
            $leaderArray[$leaderCounter]['score']       = $row['score'];

            $leaderCounter++;
        }

        $tableString .= "<center>";
        $tableString .= "<table style='width:50%' id='leaderTable'>";

        for($x = 0; $x < count($leaderArray); $x++)
        {
            $tableString .= "<tr>";
            foreach ($leaderArray[$x] as $row)
            {
                if($x>0)
                    $tableString .=  "<td>".$row."</td>";
                else
            	    $tableString .=  "<th>".$row."</th>";
            }
            $tableString .= "</tr>";
        }

        $tableString .= "</table>";
        $tableString .= "</center>";

        echo $tableString;
     }

    //====================================================================================================
    //      Helper functions
    //====================================================================================================
    function array_sort_by_column(&$arr, $col, $dir = SORT_DESC) {
        $sort_col = array();
        foreach ($arr as $key=> $row) {
            $sort_col[$key] = $row[$col];
        }

        array_multisort($sort_col, $dir, $arr);
    }

	public function testDb(){

        $contents = file_get_contents("../cellPresetCoordinates.json");
        var_dump($contents);
        $contents = utf8_encode($contents);
        var_dump($contents);
        $results = json_encode($contents);
        var_dump($results);
	}

    public function getUnlockedAchievements()
    {
        $achievementString = $this->db->getAchievements();

        $unlockedAchievements = array();

        $index = 0;

        foreach(str_split($achievementString) as $char)
        {
            $index++;

            if($char == '1')
            {
                $unlockedAchievements[$index] = $this->achievementIndexToDescription($index);
            }
        }

        return $unlockedAchievements;
    }

    //====================================================================================================
    //      Resource Dictionaries
    //====================================================================================================

    private function achievementIndexToDescription($ind)
    {
        switch($ind)
        {
            case 0:  return "XS: max score of 20% reached.";
            case 1:  return "S : max score of 20% reached.";
            case 2:  return "M : max score of 20% reached.";
            case 3:  return "L : max score of 20% reached.";
            case 4:  return "XL: max score of 20% reached.";
            case 5:  return "All max scores of 20% reached.";

            case 6:  return "XS: max score of 30% reached.";
            case 7:  return "S : max score of 30% reached.";
            case 8:  return "M : max score of 30% reached.";
            case 9:  return "L : max score of 30% reached.";
            case 10: return "XL: max score of 30% reached.";
            case 11: return "All max scores of 30% reached.";

            case 12:  return "Unlocked preset  12";
            case 13:  return "Unlocked preset  13";
            case 14:  return "Unlocked preset  14";
            case 15:  return "Unlocked preset  15";
            case 16:  return "Unlocked preset  16";
            case 17:  return "Unlocked preset  17";
            case 18:  return "Unlocked preset  18";
            case 19:  return "Unlocked preset  19";
            case 20:  return "Unlocked preset  20";
            case 21:  return "Unlocked preset  21";
            case 22:  return "Unlocked preset  22";
            case 23:  return "Unlocked preset  23";
            case 24:  return "Unlocked preset  24";
            case 25:  return "Unlocked preset  25";
            case 26:  return "Unlocked preset  26";
            case 27:  return "Unlocked preset  27";
            case 28:  return "Unlocked preset  28";
            case 29:  return "Unlocked preset  29";
            case 30:  return "Unlocked preset  30";
            case 31:  return "Unlocked preset  31";
            case 32:  return "Unlocked preset  32";
            case 33:  return "Unlocked preset  33";
            case 34:  return "Unlocked preset  34";
            case 35:  return "Unlocked preset  35";
            case 36:  return "Unlocked preset  36";
            case 37:  return "Unlocked preset  37";
            case 38:  return "Unlocked preset  38";
            case 39:  return "Unlocked preset  39";
            case 40:  return "Unlocked preset  40";
            case 41:  return "Unlocked preset  41";
            case 42:  return "Unlocked preset  42";
            case 43:  return "Unlocked preset  43";
            case 44:  return "Unlocked preset  44";
            case 45:  return "Unlocked preset  45";
            case 46:  return "Unlocked preset  46";
            case 47:  return "Unlocked preset  47";
            case 48:  return "Unlocked preset  48";
            case 49:  return "Unlocked preset  49";
            case 50:  return "Unlocked preset  50";
            case 51:  return "Unlocked preset  51";
            case 52:  return "Unlocked preset  52";
            case 53:  return "Unlocked preset  53";
            case 54:  return "Unlocked preset  54";
            case 55:  return "Unlocked preset  55";
            case 56:  return "Unlocked preset  56";
            case 57:  return "Unlocked preset  57";
            case 58:  return "Unlocked preset  58";
            case 59:  return "Unlocked preset  59";
            case 60:  return "Unlocked preset  60";
            case 61:  return "Unlocked preset  61";
            case 62:  return "Unlocked preset  62";
            case 63:  return "Unlocked preset  63";
            case 64:  return "Unlocked preset  64";
            case 65:  return "Unlocked preset  65";
            case 66:  return "Unlocked preset  66";
            case 67:  return "Unlocked preset  67";
            case 68:  return "Unlocked preset  68";
            case 69:  return "Unlocked preset  69";
            case 70:  return "Unlocked preset  70";
            case 71:  return "Unlocked preset  71";
            case 72:  return "Unlocked preset  72";
            case 73:  return "Unlocked preset  73";
            case 74:  return "Unlocked preset  74";
            case 75:  return "Unlocked preset  75";
            case 76:  return "Unlocked preset  76";
            case 77:  return "Unlocked preset  77";
            case 78:  return "Unlocked preset  78";
            case 79:  return "Unlocked preset  79";
            case 80:  return "Unlocked preset  80";
            case 81:  return "Unlocked preset  81";
            case 82:  return "Unlocked preset  82";
            case 83:  return "Unlocked preset  83";
            case 84:  return "Unlocked preset  84";
            case 85:  return "Unlocked preset  85";
            case 86:  return "Unlocked preset  86";
            case 87:  return "Unlocked preset  87";
            case 88:  return "Unlocked preset  88";
            case 89:  return "Unlocked preset  89";
            case 90:  return "Unlocked preset  90";
            case 91:  return "Unlocked preset  91";
            case 92:  return "Unlocked preset  92";
            case 93:  return "Unlocked preset  93";
            case 94:  return "Unlocked preset  94";
            case 95:  return "Unlocked preset  95";
            case 96:  return "Unlocked preset  96";
            case 97:  return "Unlocked preset  97";
            case 98:  return "Unlocked preset  98";
            case 99:  return "Unlocked preset  99";
            case 100: return "Unlocked preset  100";
            case 101:  return "Unlocked preset 101";
            case 102:  return "Unlocked preset 102";
            case 103:  return "Unlocked preset 103";
            case 104:  return "Unlocked preset 104";
            case 105:  return "Unlocked preset 105";
            case 106:  return "Unlocked preset 106";
            case 107:  return "Unlocked preset 107";
            case 108:  return "Unlocked preset 108";
            case 109:  return "Unlocked preset 109";
            case 110:  return "Unlocked preset 110";
            case 120:  return "Unlocked preset 120";
            case 121:  return "Unlocked preset 121";
            case 122:  return "Unlocked preset 122";
            case 123:  return "Unlocked preset 123";
            case 124:  return "Unlocked preset 124";
            case 125:  return "Unlocked preset 125";
            case 126:  return "Unlocked preset 126";
            case 127:  return "Unlocked preset 127";
            case 128:  return "Unlocked preset 128";
            case 129:  return "Unlocked preset 129";
            case 130:  return "Unlocked preset 130";
            case 131:  return "Unlocked preset 131";
            case 132:  return "Unlocked preset 132";
            case 133:  return "Unlocked preset 133";
            case 134:  return "Unlocked preset 134";
            case 135:  return "Unlocked preset 135";
            case 136:  return "Unlocked preset 136";
            case 137:  return "Unlocked preset 137";
            case 138:  return "Unlocked preset 138";
            case 139:  return "Unlocked preset 139";
            case 140:  return "Unlocked preset 140";
            case 141:  return "Unlocked preset 141";
            case 142:  return "Unlocked preset 142";
            case 143:  return "Unlocked preset 143";
            case 144:  return "Unlocked preset 144";
            case 145:  return "Unlocked preset 145";
            case 146:  return "Unlocked preset 146";
            case 147:  return "Unlocked preset 147";
            case 148:  return "Unlocked preset 148";
            case 149:  return "Unlocked preset 149";
            case 150:  return "Unlocked preset 150";
            case 151:  return "Unlocked preset 151";
            case 152:  return "Unlocked preset 152";
            case 153:  return "Unlocked preset 153";
            case 154:  return "Unlocked preset 154";
            case 155:  return "Unlocked preset 155";
            case 156:  return "Unlocked preset 156";
            case 157:  return "Unlocked preset 157";
            case 158:  return "Unlocked preset 158";
            case 159:  return "Unlocked preset 159";
            case 160:  return "Unlocked preset 160";
            case 161:  return "Unlocked preset 161";
            case 162:  return "Unlocked preset 162";
            case 163:  return "Unlocked preset 163";
            case 164:  return "Unlocked preset 164";
            case 165:  return "Unlocked preset 165";
            case 166:  return "Unlocked preset 166";
            case 167:  return "Unlocked preset 167";
            case 168:  return "Unlocked preset 168";
            case 169:  return "Unlocked preset 169";
            case 170:  return "Unlocked preset 170";
            case 171:  return "Unlocked preset 171";
            case 172:  return "Unlocked preset 172";
            case 173:  return "Unlocked preset 173";
            case 174:  return "Unlocked preset 174";
            case 175:  return "Unlocked preset 175";
            case 176:  return "Unlocked preset 176";
            case 177:  return "Unlocked preset 177";
            case 178:  return "Unlocked preset 178";
            case 179:  return "Unlocked preset 179";
            case 180:  return "Unlocked preset 180";
            case 181:  return "Unlocked preset 181";
            case 182:  return "Unlocked preset 182";
            case 183:  return "Unlocked preset 183";
            case 184:  return "Unlocked preset 184";
            case 185:  return "Unlocked preset 185";
            case 186:  return "Unlocked preset 186";
            case 187:  return "Unlocked preset 187";
            case 188:  return "Unlocked preset 188";
            default: return "Unknown Achievement";
        }
    }

}


/*FOR DB QUery TESTING PURPOSE
 * public function testDB(){

$result = $this->db->getLeaderboard();

var_dump($result);

return $result;

}

    */
?>