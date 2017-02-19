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

        $boardArr = $this->db->getBoardNamesAndSizes();

        $logoutFormHtml =  '<form action="welcome.php" method="POST">
                                <input type="submit" name="do" value="Logout" />
                            </form>';

    //----------------------------------------------------
        $boardListHtml = '<h3>Your Boards</h3>';
    //----------------------------------------------------

        if(count($boardArr) == 0 )
        {
            $boardListHtml .= 'You don\'t have any Boards yet. Start playing NOW!';
        }
        else
        {
            $boardListHtml .= '<ul>';

            foreach ($boardArr as $boardRow)
            {
                $boardListHtml .="<li>{$boardRow}</li>";
            }

            $boardListHtml .= "</ul>";
        }

    //----------------------------------------------------
        $progressHtml = '<h3>Your Progress</h3>';
    //----------------------------------------------------

        $progressHtml .= '<table>
                            <tr>
                                <td>Size</td>
                                <td>MaxScore</td>
                            </tr>';

        foreach($this->db->getUserProgress() as $size => $score)
        {
            $progressHtml .= "<tr>
                                  <td>{$size}</td>
                                  <td>{$score}</td>
                              </tr>";
        }

        $progressHtml.='</table>';

    //----------------------------------------------------
        $achievementsHtml = '<h3>Your Achievements</h3>';
    //----------------------------------------------------

        $achievementString = $this->db->getAchievements();
        $index             = 0;

        echo '<script>
                function setAchievementDisplay(text)
                {
                    document.getElementById("selectedAchievementParagraph").innerHTML=text;
                }
            </script>';

        foreach(str_split($achievementString) as $char)
        {
            $index++;

            $descr = $this->achievementIndexToDescription($index);

            if($char == '1')
            {
                $achievementsHtml.="<div class=\"unlockedAchievement\" onmouseover=\"setAchievementDisplay('<h3>(unlocked) Achievement #".$index."</h3>".$descr."')\">{$index}</div>";
            }
            else
            {
                $achievementsHtml.="<div class=\"lockedAchievement\" onmouseover=\"setAchievementDisplay('<h3>(locked) Achievement #".$index."</h3>".$descr."')\">{$index}</div>";
            }
        }

        $achievementsHtml.="<p id=\"selectedAchievementParagraph\"/>";

    //----------------------------------------------------
    //----------------------------------------------------

        echo $boardListHtml
             .$progressHtml
             .$achievementsHtml
             .$logoutFormHtml;

    }

    public function showGameSelection()
    {

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
            $existingBoards = "";

            foreach ($this->db->getSizes() as $key => $value)
            {
                $sizeOptions .= '<option>'.$value.'</option>';
            }

            foreach ($this->db->getBoardNamesAndSizes() as $key => $value)
            {
                $existingBoards .= '<option>'.$value.'</option>';
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

                        <h3>Resume an existing Board</h3>
                        <select name="boardName">
                            '.$existingBoards.'
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
            $this->showLogin("","");
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
        //$leaderBoard = getLeaderBoard()

        $leaderBoard[0][place]   = "rank";
        $leaderBoard[0][uid]   = "id";
        $leaderBoard[0][name]  = "name";
        $leaderBoard[0][score]  = "score";

        $leaderBoard[1][rank]   = "";
        $leaderBoard[1][uid]   = "12331231231";
        $leaderBoard[1][name]  = "Pratzner";
        $leaderBoard[1][score]  = "15000";

        $leaderBoard[2][rank]   = "";
        $leaderBoard[2][uid]   = "12312311231";
        $leaderBoard[2][name]  = "testheinz";
        $leaderBoard[2][score]  = "150";

        $leaderBoard[4][rank]   = "";
        $leaderBoard[4][uid]   = "262";
        $leaderBoard[4][name]  = "testjosef";
        $leaderBoard[4][score]  = "12312";

        $leaderBoard[5][rank]   = "";
        $leaderBoard[5][uid]   = "256";
        $leaderBoard[5][name]  = "testhurz";
        $leaderBoard[5][score]  = "802";

        $leaderBoard[6][rank]   = "";
        $leaderBoard[6][uid]   = "5262";
        $leaderBoard[6][name]  = "testdude";
        $leaderBoard[6][score]  = "9172319";

        $leaderBoard[7][rank]   = "";
        $leaderBoard[7][uid]   = "12353423231";
        $leaderBoard[7][name]  = "testgash";
        $leaderBoard[7][score]  = "51209123";

        $leaderBoard[8][rank]   = "";
        $leaderBoard[8][uid]   = "98765";
        $leaderBoard[8][name]  = "testtrash";
        $leaderBoard[8][score]  = "123";

        $this->array_sort_by_column($leaderBoard, 'score');

        $tableString .= "<center>";
        $tableString .= "<table style='width:50%' id='leaderTable'>";

        for($x = 0; $x < count($leaderBoard); $x++)
        {
            $tableString .= "<tr>";
            if($x >0)
                $leaderBoard[$x][rank] = $x . ".";
                foreach ($leaderBoard[$x] as $row)
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

            case 12: return "Unlocked preset 0.";
            case 13: return "Unlocked preset 1.";
            case 14: return "Unlocked preset 2.";
            case 15: return "Unlocked preset 3.";
            case 16: return "Unlocked preset 4.";
            case 17: return "Unlocked preset 5.";
            case 18: return "Unlocked preset 6.";
            case 19: return "Unlocked preset 7.";

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