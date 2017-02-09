<script type="text/javascript">
	
	const FRAMERATE = 60;
	
	const CELLSIZE = 5;
	
	var refreshInterval;
	var timerInterval;	
	var displayInterval;
	
	var gameDim;
	
	var iterations = 0;
	var currIterations = 0;
	var seconds = 0;
	
	var board;
	var changes = new Map();

	var ctx;
	
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
	
	//x_y presetDefinitions relative to middle & including 0,0
	var presets = {
		'Sauwastika'	: 	["-2:-3","-1:-3","0:-3","0:-2","0:-1","0:0","0:1","0:2","0:3","1:3","2:3","-1:0","-2:0","-3:0","-3:1","-3:2","1:0","2:0","3:0","3:-1","3:-2"],
		'SquareTest'  	: 	["-3:-3","-2:-3","-1:-3","0:-3","1:-3","2:-3","3:-3","-3:-2","0:-2","3:-2","-3:-1","0:-1","3:-1","-3:0","0:0","3:0","-3:1","0:1","3:1","-3:2",
						     "0:2","3:2","-3:3","-2:3","-1:3","0:3","1:3","2:3","3:3"]
	};
	
	function randomBoard()
	{
		iterations = 0;
		seconds = 0;
	
		for(var y = 0; y < gameDim; y++)
		{
			for(var x = 0; x < gameDim; x++)
			{
				if(Math.random() < 0.5)
				{
					ctx.fillStyle = "rgba(255,255,255,1)";
					drawPixel(x, y);
					board[x][y] = false;
				}
				else
				{
					ctx.fillStyle = "rgba(255,0,0,1)";
					drawPixel(x, y);
					board[x][y] = true;
				}
			}
		}
	}

	function drawPixel(x, y)
	{
		ctx.fillRect( x * CELLSIZE, y * CELLSIZE, CELLSIZE, CELLSIZE );
	}
	
	function generateBoard(_gameDim)
	{
		gameDim = _gameDim;		
		board = createArray(gameDim, gameDim);

		//Generate Slider
		document.write("<input type=\"range\" min=\"0\" max=\"1000\" value=\"0\" onchange=\"speedChanged(this.value)\"> <label id=\"speed\" style=\"vertical-align: top;\">0</label><br/>");
		document.write("<label id=\"generationsLabel\">Generations per Second | </label><br/><br/><br/>");
		document.write("<input type=\"button\" value=\"randomize\" onclick=\"randomBoard()\"/><br/><br/>");
		
		document.write('<label>Presets:    '+
						'<select name="presets" id="presets" size="1">      '+
						  '<option>Sauwastika</option> '+
						  '<option>SquareTest</option>'+
						  '<option>SquartTest1</option>'+
						  '<option>SquartTest3</option>'+
						  '<option>SquartTest2</option>'+
						'</select>'+
					  '</label>');
	
		//Generate canvas
		
		document.write("<br/><br/><canvas id=\"myCanvas\" width=\""+gameDim * CELLSIZE+"\" height=\""+gameDim * CELLSIZE+"\" moz-opaque></canvas>"); 

		var c = document.getElementById("myCanvas");
		ctx = c.getContext("2d");
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
		document.getElementById("generationsLabel").innerHTML = "Generations per Second | Last: " + currIterations + "\t Avg: " + (iterations/seconds).toFixed(2);
		currIterations = 0;
	}
	
	function tick()
	{
		iterations++;
		currIterations++;
		
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
	
	var changesCopy;
	
	function display()
	{
		
		changesCopy = changes;
		
		if(changesCopy.size == 0)
		{
			return;
		}
		
		//TODO: Group more efficiently than this
		var toKill = [];
		var toRevive = [];
		
		changesCopy.forEach(function(value, key)
		{
			if(value)
			{
				toRevive.push(key);
				
				
			}
			else
			{
				toKill.push(key);
			}
		});
		
		ctx.fillStyle = "rgba(255,0,0,1)";
		toRevive.forEach(function(key)
		{
			drawPixel(key.x, key.y);
		});
		
		ctx.fillStyle = "rgba(255,255,255,1)";
		toKill.forEach(function(key)
		{
			drawPixel(key.x, key.y);
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
		insertPreset('buddhistLuck');
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
		
		//console.log(presetValues)
		
		//TODO
		presetValues.forEach(function(item)
		{		
			var tmp = item.split(':');
			var middle = Math.floor(gameDim / 2);
			//console.log("x: "  +tmp[0] + " y: " +tmp[1]);
			getCellDiv(middle+ parseInt(tmp[0]), middle+ parseInt(tmp[1])).className = "aliveGameCell";			
		});
	}
	
	generateBoard(200);
	
</script>

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

label
{
	border: 1px solid green;
}

.deadGameCell
{
	padding: 0;
	box-sizing: border-box;
	display: inline-block;
	width: 5px;
	height: 5px;
	//border: 0.1px solid white;
	background-color: #DDD;
}

.aliveGameCell
{
	padding: 0;
	box-sizing: border-box;
	display: inline-block;
	width: 5px;
	height: 5px;
	//border: 0.1px solid white;
	background-color: #F00;
}

canvas
{
	border: 1px solid black;
}

</style>