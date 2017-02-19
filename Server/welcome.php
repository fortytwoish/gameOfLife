<HTML>
    <HEAD>
        <TITLE>Projekt: GameOfLife</TITLE>
    </HEAD>
    <BODY>
        <?php
			include 'content.php';
			$content = new Content();


			$errors = array();
			$values = array("do"                  => "",
                            "username"            => "",
                            "password"            => "",
                            "accountAction"       => "",
                            "boardSize"           => "",
                            "boardSelectOrCreate" => "",
                            "boardName"           => "",
                            "newBoardName"        => "");

//===========================================================================================//

			if($_SERVER["REQUEST_METHOD"] == "POST")
			{
				$do = _POST_SANITIZED("do");

				foreach($values as $key => $value)
				{
					if(_POST_SANITIZED($key) != "")
					{
						$values[$key] = _POST_SANITIZED($key);
					}
					else
					{
						$errors = array_merge($errors, array($key => $key . " fehlt"));
					}
				}
			}
			else
			{
				$do = _GET_SANITIZED("do");
			}

            echo("<script>alert(\"YO\")</script>");

            var_dump($_POST);

			switch($do)
			{
				case "showAccount":
                    if($content->userName == "")
                    {
                        $content->showLogin($values, array("username" => "", "password" => ""));
                    }
                    else
                    {
                        $content->showAccount();
                    }
					break;
                case "showGameSelection":
					$content->showGameSelection("");
					break;
				case "showGame":
                    {

                        $boardName = "Anonymous board";
                        $isNewBoard = false;

                        if(!isset($errors["boardSelectOrCreate"]) && isset($values["boardSelectOrCreate"]))
                        {
                            //------------------------------------------------------------------------------
                            //  Logged in User - Select existing Board
                            //------------------------------------------------------------------------------
                            if($values["boardSelectOrCreate"] == "Select")
                            {
                                if(isset($values["boardName"]))
                                {
                                    $boardName = $values["boardName"];
                                    $boardName = explode(" -", $boardName)[0];
                                    $board = $content->db->getBoard($boardName);
                                    $content->showGame(sqrt(strlen($board)), $boardName, $board, false);
                                    return;
                                }
                            }
                            //------------------------------------------------------------------------------
                            //  Logged in User - Create new Board
                            //------------------------------------------------------------------------------
                            else if($values["boardSelectOrCreate"] == "Create")
                            {
                                if(isset($values["newBoardName"]) && isset($values["boardSize"]))
                                {
                                    $boardName = $values["newBoardName"];
                                    $isNewBoard = true;
                                    if(!$content->db->addCurrentUserBoard("",
                                                                          $values["newBoardName"],
                                                                          $content->getDimFromSizeDescription(substr($values["boardSize"], 0, 2)),
                                                                          20))
                                    {
                                        $content->showGameSelection("Board already exists.");
                                        return;
                                    }
                                }
                            }
                            else
                            {
                                echo "Unknown Error: Board Selection Failed.";
                                $content->showGameSelection("");
                            }
                        }

                        switch(substr($values["boardSize"], 0, 2))
                        {
                            case "XS":
                                $content->showGame(15, $boardName, null, $isNewBoard);
                                break;
                            case "S ":
                                $content->showGame(50, $boardName, null, $isNewBoard);
                                break;
                            case "M ":
                                $content->showGame(100, $boardName, null, $isNewBoard);
                                break;
                            case "L ":
                                $content->showGame(200, $boardName, null, $isNewBoard);
                                break;
                            case "XL":
                                $content->showGame(500, $boardName, null, $isNewBoard);
                                break;
                            default:
                                echo 'Invalid boardsize.';
                                $content->showGameSelection("");
                                break;
                        }
                    }
					break;
				case "showLeaderboard":
					$content->showLeaderboard();
					break;
				case "testDb":
					$content->testDb();
					break;
                case "accountAction":
                    {
                        if($values["username"] != "" && strlen($values["username"]) < 6)
                        {
                            $errors["username"] = "Bitte einen Benutzernamen mit mindestens 6 Zeichen angeben.";
                        }

                        if($values["password"] != "" && strlen($values["password"]) < 6)
                        {
                            $errors["password"] = "Bitte ein Passwort mit mindestens 6 Zeichen angeben.";
                        }

                        if(    isset($errors["username"])
                            || isset($errors["password"])
                            || isset($errors["accountAction"]))
                        {
                            $content->showLogin($values, $errors);
                        }
                        else
                        {
                            if($values["accountAction"] == "Login")
                            {
                                $content->login($values["username"], $values["password"]);
                            }
                            else if($values["accountAction"] == "Create")
                            {
                                $content->create($values["username"], $values["password"]);
                            }
                            else
                            {
                                //Should never get here!
                                echo "CATASTROPHIC FAILURE. TOTALLY UNEXPECTED LOGIN DATA.";
                                return;
                            }
                        }
                        break;
                    }
                case "Logout":
                    {
                        $content->logout();
                    }
                    break;
                case "updateAchievements":
                    {
                        $db = $this->content->db;

                        $
                    }
					break;
				default:
					$content->showWelcome();
					break;

			}

//===========================================================================================//

			function _POST_SANITIZED($key)
			{
				if(isset($_POST[$key]))
				{
					return filter_input(INPUT_POST, $key, FILTER_SANITIZE_STRING);
				}
				else
				{
					return "";
				}
			}

			function _GET_SANITIZED($key)
			{
				if(isset($_GET[$key]))
				{
					return filter_input(INPUT_GET, $key, FILTER_SANITIZE_STRING);
				}
				else
				{
					return "";
				}
			}
        ?>
    </BODY>
</HTML>
