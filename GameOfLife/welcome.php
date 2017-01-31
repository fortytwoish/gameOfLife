<HTML>
    <HEAD>
        <TITLE>Projekt: GameOfLife</TITLE>
    </HEAD>
    <BODY>
		<a href="../index.php">Zurück zu pbs2h15amu Home</a><br><br>
		<?php	
		
			include 'content.php';
			$content = new Content();
		
			$errors = array();
			$values = array("do"      => "",
							"gameBtn" => ""); //TODO: Fill with all possible form data
		
//===========================================================================================//
		
			if($_SERVER["REQUEST_METHOD"] == "POST") 
			{
				$do = _POST_SANITIZED("do");	
				
				foreach($values as $key => $value)
				{
					if(_POST_SANITIZED($key) != "")
					{
						$values[$key] = _POST_SANITIZED($key);
						echo $key.': '.$values[$key].' <br>';
					}
					else	
					{
						echo $key.' fehlt.<br>';
						$errors = array_merge($errors, array($key => $key . " fehlt"));
					}
				}
			}
			else
			{
				$do = _GET_SANITIZED("do");
				echo '$do: '.$do;
			}
			
			switch($do)
			{
				case "showLogin":
					$content->showLogin();
					break;
				case "showSPGame":
					$content->showSPGame($values["gameBtn"]);
					break;
				case "showMPGame":
					$content->showMPGame($values["gameBtn"]);
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
					//echo $key . ' ist gesetzt: ('. $_POST[$key] . ') (POST)<br>';
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
					//echo $key . ' ist gesetzt (GET)<br>';
					return filter_input(INPUT_GET, $key, FILTER_SANITIZE_STRING);
				}
				else
				{
					//echo $key . ' ist nicht gesetzt (GET)<br>';
					return "";
				}
			}
		?>
    </BODY>
</HTML>
