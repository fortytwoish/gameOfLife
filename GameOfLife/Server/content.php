<?php

session_start();

class Content
{
	private $db;
    private $gameDim    = 500; //Predefined for now //Should be odd always to guarantee middle
	private $isLoggedIn = false;

    public $userName = "";

	public function __construct()
	{
		include 'database.php';
		$this->db = new dataBase($this);
    if(isset($_SESSION["username"]))
    {
      $this->userName = $_SESSION["username"];
    }
	}

	public function showNavigation($selected)
	{
       //Load CSS and JS files
       echo '<link rel="stylesheet" href="../Client/gameOfLife.css" type="text/css">';
       echo '<script src="../Client/gameOfLife.js"></script>';
       echo '<script src="../Client/uploadGame.js"></script>';

		$loginText = $this->userName == null
				   ? 'Login'
				   : 'Profile of '.$this->userName;

		echo '	<nav>
					<ul>
						<li>
							<a href="?do=showAccount"     class="'.($selected == 0 ? "selectedNavItem" : "deselectedNavItem").'">'.$loginText.'</a>
						</li>
						<li>
							<a href="?do=showGame"        class="'.($selected == 1 ? "selectedNavItem" : "deselectedNavItem").'">Play</a>
						</li>
						<li>
							<a href="?do=showLeaderboard" class="'.($selected == 2 ? "selectedNavItem" : "deselectedNavItem").'">Leaderboard</a>
						</li>
					</ul>
				</nav>';
	}

	public function showWelcome()
	{
		$this->showNavigation(-1);
		echo '<h1>Welcome to Game Of Life</h1>';
        echo 'Created by <b>Paul Scheel</b> and <b>Marvin M&uuml;ller</b> in 2017';
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
                    <tr>
                        <td><input type="submit" name="accountAction" value="Login"/></td>
                        <td><input type="submit" name="accountAction" value="Create"/></td>
                    </tr>
                </table>
                <input type="hidden" name="do" value="accountAction"/>
              </form>';
	}

    public function showAccount()
    {
        $this->showNavigation(0);

        echo '<form action="welcome.php" method="POST">
                <input type="submit" name="do" value="Logout" />
                <p>showing account of user: '.$this->userName.'</p>
              </form>';
    }

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

	public function showGame()
	{
		$this->showNavigation(1);

		echo '<script type="text/javascript">
		        generateBoard('.$this->gameDim.');
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

    public function showLeaderboard()
    {
        $this->showNavigation(2);
    }

    public function testDb(){


        $tmp = $this->db->getCurrentUserID();

        echo 'Current User : ' . $tmp;
    }

}
?>