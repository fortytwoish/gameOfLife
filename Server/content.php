<?php
 class Content
 {
 	private $db;

 	private $isLoggedIn = false;
 	private $userName   = '';
 	private $isStopped  = true;

 	private $gameDim = 101; //Predefined for now //Should be odd always to guarantee middle

 	public function __construct()
 	{
 		include 'database.php';
 		$this->db = new database();
 	}

 	public function showNavigation($selected)
 	{
        //Load CSS and JS files
        echo '<link rel="stylesheet" href="../Client/gameOfLife.css" type="text/css">';
        echo '<script src="../Client/gameOfLife.js" ></script>';

 		$loginText = $this->userName == null
 				   ? 'Login'
 				   : 'Profile of '.$this->userName;

 		echo '	<nav>
 					<ul>
 						<li>
 							<a href="?do=showLogin" style="color:'.($selected == 0 ? "#000" : "#FFF").';">'.$loginText.'</a>
 						</li>
 						<li>
 							<a href="?do=showSPGame" style="color:'.($selected == 1 ? "#000" : "#FFF").';">Singleplayer</a>
 						</li>
 						<li>
 							<a href="?do=showMPGame" style="color:'.($selected == 2 ? "#000" : "#FFF").';">Multiplayer</a>
 						</li>
 					</ul>
 				</nav>';
 	}


 	public function showWelcome()
 	{
 		$this->showNavigation(-1);
 		echo '<h1>Welcome to Game Of Life</h1>';
 	}

 	public function showLogin()
 	{
 		$this->showNavigation(0);
 	}

 	private function showGameControls()
 	{
 		if($this->isStopped)
 		{
 			echo '<input type="submit" name="gameBtn" value="Start"/>';
 		}
 		else
 		{
 			echo '<input type="submit" name="gameBtn" value="Pause"/>';
 		}

 		echo ' <input type="submit" name="gameBtn" value="Reset"/>';
 		//TODO differenciate
 		//echo ' <input type="hidden" name="do" value="showGame"/>';
 	}

 	public function showSPGame($gameBtn)
 	{
 		$this->showNavigation(1);

 		echo '
 			<script type="text/javascript">
 				generateBoard('.$this->gameDim.');
 			</script>';

 		echo '<input type="submit" name="resetButton" value="Reset"/>';
 	}

 	public function showMPGame($gameBtn)
 	{
 		$this->showNavigation(2);

 		if($gameBtn == "Start")
 		{
 			$this->isStopped = false;

 			//
 		}
 		else if($gameBtn == "Reset")
 		{

 		}

 		echo '<form action="welcome.php" method="POST">';
 			$this->showGameControls();


 		echo '<table cellspacing="0">';

 		for($i = 0; $i < $this->gameDim; $i++)
 		{
 			echo '<tr>';
 			for($j = 0; $j < $this->gameDim; $j++)
 			{
 				echo '<td>
 						<div class="deadGameCell"/>
 					  </td>';
 			}
 			echo '</tr>';
 		}

 		echo '</table>';

 		echo '</form>';
 	}


 }
?>