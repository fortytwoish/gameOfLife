//TODO: EIN Loop! Wenn 1/FPS sec vorbei, render zusätzlich anstoßen
const FRAMERATE = 60;

const CELLSIZE = 10;

var refreshInterval;
var timerInterval;
var displayInterval;

var gameDim;

var iterations 	   = 0;
var currIterations = 0;
var seconds 	   = 0;

var board;
var changes = new Map();
var changesTick = new Map();

var onFillStyle  = "rgba(255,0,0,0.01)";
var offFillStyle = "rgba(10,10,10,0.01)";

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
                ctx.fillStyle = onFillStyle;
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
    var slideWidth = _gameDim * CELLSIZE;
    document.write("<input type=\"range\" min=\"0\" max=\"250\" style=\"width:"+slideWidth+"px;\" value=\"0\" onchange=\"speedChanged(this.value)\"> <label id=\"speed\" style=\"vertical-align: top;\">0</label><br/>");
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

    c.addEventListener('mousedown', function(evt) {
        var mousePos = getMousePos(c, evt);

        if(board[mousePos.x][mousePos.y])
        {
            board[mousePos.x][mousePos.y] = false;
            ctx.fillStyle = offFillStyle;
            drawPixel(mousePos.x, mousePos.y);
        }
        else
        {
            board[mousePos.x][mousePos.y] = true;
            ctx.fillStyle = onFillStyle;
            drawPixel(mousePos.x, mousePos.y);
        }
    }, false);


    ctx.fillStyle = offFillStyle;
    for(var j = 0; j < board.length; j++)
        for(var i = 0; i < board.length; i++)
        {
            board[i][j] = false;
            drawPixel(i, j);
        }


}

function getMousePos(canvas, evt) {
    var rect = canvas.getBoundingClientRect();
    return {
        x: Math.floor((evt.clientX - rect.left) / CELLSIZE),
        y: Math.floor((evt.clientY - rect.top) / CELLSIZE)
    };
}

var stopped = true;

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
        //refreshInterval = setInterval( tick, (1.0/newValue) * 1000 );
        timerInterval   = setInterval(timer, 1000);
        //displayInterval = setInterval(display, 1/FRAMERATE * 1000);
        stopped = false;
        tick();
    }
    else
    {
        stopped = true;
    }

}

function setOnFillStyle()
{
    ctx.fillStyle = onFillStyle;
}

function setOffFillStyle()
{
    ctx.fillStyle = offFillStyle;
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

            //ctx.fillStyle = "rgba(255,255,255,1)";
            //drawPixel(x, y);

            //ctx.fillStyle = "rgba(0,0,0,1)";
            //ctx.font = CELLSIZE+"px Arial";


            if(board[x][y] == false)
            {
                if(neighboringCells == 3)
                {
                    //ctx.fillStyle = "rgba(0,255,0,1)";
                    //console.log("To be revived: " + x + ", " + y);
                    toBeRevived.push(new Point(x,y));
                }
            }
            else if(neighboringCells < 2 || neighboringCells > 3)
            {
                //ctx.fillStyle = "rgba(0,0,255,1)";
                //console.log("To be killed: " + x + ", " + y);
                toBeKilled.push(new Point(x, y));
            }

            //ctx.fillText(neighboringCells, x * CELLSIZE, (y + 1) * CELLSIZE, CELLSIZE);

        }


    setTickResults(toBeRevived, toBeKilled);
    /*
    var boardStr = "";
    for(var j = 0; j < board.length; j++)
    {
        for(var i = 0; i < board.length; i++)
        {
            if(board[i][j]) boardStr += "1";
            else boardStr += "0";
        }
        boardStr += "\n";
    }

    console.log(boardStr);
    */

    if(!stopped)
        setTimeout(tick, 10);
}

//Needs to run synchronously with getChangesToDisplay()
function setTickResults(toBeRevived, toBeKilled)
{
    //changesTick = new Map(changes);

    setOnFillStyle();

    toBeRevived.forEach( function(cell)
    {
        board[cell.x][cell.y] = true;

        drawPixel(cell.x, cell.y);

        /*
        if(!(changesTick.has(cell)))	//Change ist noch nicht bekannt -> Hinzufügen
        {
            changesTick.set(cell, true);
        }
        else if(!changesTick[cell])		//Umgekehrter Change war bereits angeordnet -> Überflüssig, wieder raus
        {
            changesTick.delete(cell);
        }
        */
    });

    setOffFillStyle();

    toBeKilled.forEach ( function(cell)
    {
        board[cell.x][cell.y] = false;

        drawPixel(cell.x, cell.y);
        /*
        if(!(changesTick.has(cell)))	//Change ist noch nicht bekannt -> Hinzufügen
        {
            changesTick.set(cell, false);
        }
        else if(changesTick[cell])		//Umgekehrter Change war bereits angeordnet -> Überflüssig, wieder raus
        {
            changesTick.delete(cell);
        }
        */
    } );

    //
    //changes = changesTick;
}

//Needs to run synchronously with setTickResults
function getChangesToDisplay()
{
    var changesCopy = changes;

    changes.clear();

    return changesCopy;
}

function sleep(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
}

function display()
{
    /*
    for(var j = 0; j < board.length; j++)
        for(var i = 0; i < board.length; i++)
        {
            if(board[i][j]) ctx.fillStyle = onFillStyle;
            else ctx.fillStyle = offFillStyle;

            drawPixel(i, j);
        }
    */

    var changesCopy = getChangesToDisplay();


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

    ctx.fillStyle = onFillStyle;
    toRevive.forEach(function(key)
    {
        drawPixel(key.x, key.y);
    });

    ctx.fillStyle = offFillStyle;
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

//generateBoard(100);
