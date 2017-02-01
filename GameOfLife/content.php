<script type="text/javascript">
	
	var refreshInterval;
	
	var gameDim;
	
	//x_y presetDefinitions relative to middle & including 0,0
	var presets = {
		'Sauwastika'	: 	["-2:-3","-1:-3","0:-3","0:-2","0:-1","0:0","0:1","0:2","0:3","1:3","2:3","-1:0","-2:0","-3:0","-3:1","-3:2","1:0","2:0","3:0","3:-1","3:-2"],
		'SquareTest'  	: 	["-3:-3","-2:-3","-1:-3","0:-3","1:-3","2:-3","3:-3","-3:-2","0:-2","3:-2","-3:-1","0:-1","3:-1","-3:0","0:0","3:0","-3:1","0:1","3:1","-3:2",
						     "0:2","3:2","-3:3","-2:3","-1:3","0:3","1:3","2:3","3:3"]
	};
	
	function randomBoard()
	{
		for(var y = 0; y < gameDim; y++)
		{
			for(var x = 0; x < gameDim; x++)
			{
				if(Math.random() < 0.5)
				{
					getCellDiv(x,y).className = "deadGameCell";
				}
				else
				{
					getCellDiv(x,y).className = "aliveGameCell";
				}
			}
		}
	}
	
	function generateBoard(_gameDim)
	{
		gameDim = _gameDim;
		//Generate Slider
		document.write("<input type=\"range\" min=\"0\" max=\"100\" value=\"0\" onchange=\"speedChanged(this.value)\"> <label id=\"speed\" style=\"vertical-align: top;\">0</label>");
		document.write("<input type=\"button\" value=\"randomize\" onclick=\"randomBoard()\"/>");
		
		document.write('<label>Presets:    '+
						'<select name="presets" id="presets" size="1">      '+
						  '<option>Sauwastika</option> '+
						  '<option>SquareTest</option>'+
						  '<option>SquartTest1</option>'+
						  '<option>SquartTest3</option>'+
						  '<option>SquartTest2</option>'+
						'</select>'+
					  '</label>');
	
		//Generate table
	
		document.write("<table cellspacing=\"0\">");
		
		for(var i = 0; i < gameDim; i++)
		{
			document.write("<tr>");
			for(var j = 0; j < gameDim; j++)
			{
				document.write("<td>");
				document.write("	<div class=\"deadGameCell\" id=\"cell_"+j+"_"+i+"\" onclick=\"cellClick("+j+", "+i+")\"/>");
				document.write("</td>");
			}
			document.write("</tr>");
		}
		document.write("</table>");
	}
	
	function speedChanged(newValue)
	{
		document.getElementById("speed").innerHTML = newValue;
		
		if(refreshInterval != null)
		{
			clearInterval(refreshInterval);
		}
		
		if(newValue != 0)
		{
			refreshInterval = setInterval( tick, (1.0/newValue) * 1000 );
		}
	}
	
	function tick()
	{
		var cell;
		var neighboringCells;
		var neighborX;
		var neighborY;
		
		var toBeRevived = [];
		var toBeKilled  = [];
		
		for(var y = 0; y < gameDim; y++)
			for(var x = 0; x < gameDim; x++)
			{
				cell = getCellDiv(x, y);
				neighboringCells = 0;
				
				//Iterate through neighbors
				for(var j = -1; j <= 1; j++)
					for(var i = -1; i <= 1; i++)
					{
						//Don't count the cell itself
						if(i != 0 || j != 0)
						{
							neighborX = modulo(x + i);
							neighborY = modulo(y + j);
							
							//console.log("neighbor: " + neighborX + ", " + neighborY);
							
							if(!isDead(getCellDiv(neighborX, neighborY)))
							{
								console.log("Neighbors: " + cell.id + ", " + getCellDiv(neighborX, neighborY).id);
								neighboringCells++;
							}
						}
					}

				if(isDead(cell))
				{
					if(neighboringCells == 3)
					{
						console.log("To be revived: " + cell.id);
						toBeRevived.push(cell);
					}
				}
				else if(neighboringCells < 2 || neighboringCells > 3)
				{
					console.log("To be killed: " + cell.id);
					toBeKilled.push(cell);
				}
					
			}
			
		toBeRevived.forEach( function(cell) { cell.className = "aliveGameCell" } );
		toBeKilled.forEach ( function(cell) { cell.className = "deadGameCell"  } );
	}
	
	function isDead(gameCellDiv)
	{
		return gameCellDiv.className == "deadGameCell";
	}
	
	function modulo(value)
	{
		if(value >= gameDim)
		{
			//console.log(value + " -> " + (value - gameDim) );
			return value - gameDim;
		}
		else if(value < 0)
		{
			//console.log(value + " -> " + (value + gameDim) );
			return value + gameDim;
		}
		else
		{
			return value;
		}
	}
	
	function getCellDiv(x, y)
	{
		//console.log("getCell " + x + ", " + y);
		return document.getElementById("cell_"+x+"_"+y);
	}
	
	function cellClick(x, y)
	{
		insertPreset('buddhistLuck');
		if(getCellDiv(x, y).className == "aliveGameCell")
		{
			getCellDiv(x, y).className = "deadGameCell";			
		}
		else
		{
			getCellDiv(x, y).className = "aliveGameCell";
		}
	}
			
	function insertPreset($presetName)
	{
		var presetValues = new Array(200);
		var strUser = ""; 
		var optionBox = document.getElementById("presets");
		
		if(optionBox != null)
			strUser = optionBox.options[optionBox.selectedIndex].text;
		
		console.log(strUser);
		
		switch(strUser){
				case 'Sauwastika':
					presetValues =  presets['Sauwastika'];
					break;
				case 'SquareTest':	
					presetValues =  presets['SquareTest'];
					break;
				default:
					break;
		}
		
		console.log(presetValues)
		
		presetValues.forEach(function(item)
		{		
			var tmp = item.split(':');
			var middle = Math.floor(gameDim / 2);
			console.log("x : "  +tmp[0] + " y: " +tmp[1]);
			getCellDiv(middle+ parseInt(tmp[0]), middle+ parseInt(tmp[1])).className = "aliveGameCell";			
		});
	}
	
</script>

<?php	
class Content
{
	private $db;
	
	private $isLoggedIn = false;
	private $userName   = '';
	private $isStopped  = true;
	
	private $gameDim = 31; //Predefined for now //Should be odd always to guarantee middle
	
	public function __construct()
	{
		include 'database.php';
		$this->db = new database();
	}
	
	public function showNavigation($selected)
	{			
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


<style type="text/css">
nav
{
	background-color:#AAA;
}

nav ul
{
	padding: 0;
}

nav li
{
	display:inline;
	font-size:20pt;
	border-right: solid #FFF;
	padding:5px;
}

nav li a
{
	text-decoration: none;
}

p
{
	margin-bottom: 10px;
}

table td
{
	padding: 0;
}

.deadGameCell
{
	padding: 0;
	box-sizing: border-box;
	display: inline-block;
	width: 20px;
	height: 20px;
	//border: 0.1px solid white;
	background-color: #DDD;
}

.aliveGameCell
{
	padding: 0;
	box-sizing: border-box;
	display: inline-block;
	width: 20px;
	height: 20px;
	//border: 0.1px solid white;
	background-color: #F00;
}
</style>