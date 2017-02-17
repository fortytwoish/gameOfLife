<HTML>
    <HEAD>
        <TITLE>Projekt: GameOfLife</TITLE>
    </HEAD>
    <BODY>
        <?php
/*        	include 'config.php';
			$config = new Config(); */

			include 'content.php';
			$content = new Content();


			$errors = array();
			$values = array("do" => "",
                            "username" => "",
                            "password" => "",
                            "accountAction" => "");

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
				case "freePlay": 
					$content->showFreePlay();
					break;
				case "showGame":
					$content->showGame();
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
