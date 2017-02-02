<script type="text/javascript">
	
	const FRAMERATE = 60;
	
	var refreshInterval;
	var timerInterval;	
	var displayInterval;
	
	var gameDim;
	
	var iterations = 0;
	var seconds = 0;
	
	var board;
	var changes = new Map();
	
	//Can create multidimensional arrays
	function createArray(length) {
		var arr = new Array(length || 0),
			i = length;

		if (arguments.length > 1) {
			var args = Array.prototype.slice.call(arguments, 1);
			while(i--) arr[length-1 - i] = createArray.apply(this, args);
		}

		return arr;
	}
	
	function randomBoard()
	{
		for(var y = 0; y < gameDim; y++)
		{
			for(var x = 0; x < gameDim; x++)
			{
				if(Math.random() < 0.5)
				{
					getCellDiv(x,y).className = "deadGameCell";
					board[x][y] = false;
				}
				else
				{
					getCellDiv(x,y).className = "aliveGameCell";
					board[x][y] = true;
				}
			}
		}
	}
	
	function generateBoard(_gameDim)
	{		
		
		gameDim = _gameDim;
		
		board = createArray(gameDim, gameDim);
	
		//Generate Slider
		document.write("<input type=\"range\" min=\"0\" max=\"100\" value=\"0\" onchange=\"speedChanged(this.value)\"> <label id=\"speed\" style=\"vertical-align: top;\">0</label>");
		document.write("<input type=\"button\" value=\"randomize\" onclick=\"randomBoard()\"/> ");
		document.write("<label id=\"generationsLabel\"></label>");
	
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
				board[j][i] = false;
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
			clearInterval(timerInterval);
			clearInterval(displayInterval);
			iterations = 0;
			seconds    = 0;
		}
		
		if(newValue != 0)
		{
			refreshInterval = setInterval( tick, (1.0/newValue) * 1000 );
			timerInterval   = setInterval(timer, 1000);
			displayInterval = setInterval(display, 1/FRAMERATE * 1000);
		}
		

	}
	
	function timer()
	{
		seconds++;
		document.getElementById("generationsLabel").innerHTML = (iterations/seconds).toFixed(2) + " generations / sec";
	}
	
	function tick()
	{
		iterations++;
		
		var cell;
		var neighboringCells;
		var neighborX;
		var neighborY;
		
		var toBeRevived = [];
		var toBeKilled  = [];
		
		//console.log(board);
		
		for(var y = 0; y < gameDim; y++)
			for(var x = 0; x < gameDim; x++)
			{
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
							
							if(board[neighborX][neighborY] == true)
							{
								//console.log("Neighbors: " + cell.id + ", " + getCellDiv(neighborX, neighborY).id);
								neighboringCells++;
							}
						}
					}

				if(board[x][y] == false)
				{
					if(neighboringCells == 3)
					{
						//console.log("To be revived: " + x + ", " + y);
						toBeRevived.push(new Point(x,y));
					}
				}
				else if(neighboringCells < 2 || neighboringCells > 3)
				{
					//console.log("To be killed: " + x + ", " + y);
					toBeKilled.push(new Point(x, y));
				}
					
			}
			
		toBeRevived.forEach( function(cell)
		{
			board[cell.x][cell.y] = true;
			
			if(!(changes.has(cell)))	//Change ist noch nicht bekannt -> Hinzufügen
			{
				changes.set(cell, true);
			}
			else if(!changes[cell])		//Umgekehrter Change war bereits angeordnet -> Überflüssig, wieder raus
			{
				changes.delete(cell);
			}
		});
		toBeKilled.forEach ( function(cell)
		{
			board[cell.x][cell.y] = false;
			
			if(!(changes.has(cell)))	//Change ist noch nicht bekannt -> Hinzufügen
			{
				changes.set(cell, false);
			}
			else if(changes[cell])		//Umgekehrter Change war bereits angeordnet -> Überflüssig, wieder raus
			{
				changes.delete(cell);
			}
		} );
	}
	
	function display()
	{
		
		var changesCopy = changes;
		
		if(changesCopy.size == 0)
		{
			return;
		}
		
		var key;
		changesCopy.forEach(function(value, key)
		{
			if(value)
			{
				getCellDiv(key.x, key.y).className = "aliveGameCell";
			}
			else
			{
				getCellDiv(key.x, key.y).className = "deadGameCell";
			}
		});
		
		changes.clear();
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
		if(getCellDiv(x, y).className == "aliveGameCell")
		{
			getCellDiv(x, y).className = "deadGameCell";	
			board[x][y] = false;
			//console.log(board[x][y]);
		}
		else
		{
			getCellDiv(x, y).className = "aliveGameCell";
			board[x][y] = true;
			//console.log(board[x][y]);
		}

	}
	
	function Point(x, y) {
	  this.x = x;
	  this.y = y;
	}
</script>

<?php	
class Content
{
	private $db;
	
	private $isLoggedIn = false;
	private $userName   = '';
	private $isStopped  = true;
	
	private $gameDim = 200; //Predefined for now
	
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
			//$this->
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
	width: 1px;
	height: 1px;
	//border: 0.1px solid white;
	background-color: #DDD;
}

.aliveGameCell
{
	padding: 0;
	box-sizing: border-box;
	display: inline-block;
	width: 1px;
	height: 1px;
	//border: 0.1px solid white;
	background-color: #F00;
}
</style>