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

		echo '	<nav>
					<ul>
						<li>
							<a href="?do=showAccount"       class="'.($selected == 0 ? "selectedNavItem" : "deselectedNavItem").'">'.$loginText.'</a>
						</li>
						<li>
							<a href="?do=showGameSelection" class="'.($selected == 1 ? "selectedNavItem" : "deselectedNavItem").'">'.$playText.'</a>
						</li>
						<li>
							<a href="?do=showLeaderboard"   class="'.($selected == 2 ? "selectedNavItem" : "deselectedNavItem").'">Leaderboard</a>
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
                <center>
                    <input type="submit" name="accountAction" value="Login"/>
                    <input type="submit" name="accountAction" value="Create"/>
                </center>
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

    public function showFreePlay()
	{
		$contents = file_get_contents("../cellPresetCoordinates.json");
		$contents = utf8_encode($contents);
		$result = json_encode($contents);
		$this->showNavigation(3);

		echo '<script type="text/javascript">
		        generateBoard('.$this->freePlayGameDim.');
				setPresets('.$result.');
			  </script>';

	}

    public function showGameSelection()
    {
        $this->showNavigation(1);

        if($this->userName == null)
        { //Free Play - allow all sizes
            echo '  <b>Select a Board Size:</b>
                    <form action="welcome.php" method="POST">
                        <select name="boardSize">
                            <option value="0">XS (15x15)</option>
                            <option value="1">S  (50x50)</option>
                            <option value="2">M  (100x100)</option>
                            <option value="3">L  (200x200)</option>
                            <option value="4">XL (500x500)</option>
                        </select>

                        <input type="submit" value="Play"/>
                        <input type="hidden" name="do" value="showGame"/>
                    </form>';
        }
    }

	public function showGame($boardSize)
	{
        $this->gameDim = $boardSize;

		$this->showNavigation(1);

		echo '<script type="text/javascript">
		        generateBoard('.$this->gameDim.', '.($this->userName == null).');
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
            echo "<h2>Not logged in T_T</h2>";
    }

    public function create($username, $password)
    {
        echo $username.' your account has been created';

        $this->db->createUser($username, $password);

        echo ' creation done';

        $this->setUser($username);

        $this->showAccount();
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
            // error opening the file.
        }

        $tmp = $this->db->setUserProgress($arr,50,$score);

        echo 'Success? : ' . $tmp;
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

}


/*FOR DB QUery TESTING PURPOSE
 * public function testDB(){

$result = $this->db->getLeaderboard();

var_dump($result);

return $result;

}

    */
?>