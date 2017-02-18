const FRAMERATE = 60;
const FRAMERATE_MS = 1000 / FRAMERATE;
const SLIDER_MAX = 101;
var desiredSpeed = 0;
var desiredSpeed_ms = 0;
var waitForTick;
var sliderWidth = 0;

//Board variables
var gameDim;
var board;
var cellsize;
var changes = new Map();
var changesTick = new Map();
var isRunning = false;
var isSpeedLimited = true;

//Performance measurement variables
var iterations = 0;
var currIterations = 0;
var displayIterations = 0;
var currDisplayIterations = 0;
var seconds = 0;
var timerInterval;

//Canvas style variables
var onFillStyle = "rgba(0,200,50,1.0)";
var offFillStyle = "rgba(40,40,40,1.0)";
var ctx;

//Presets
var presets = new Map();
var previewCtx;

//====================================================================================================
//      Initialization
//====================================================================================================

function generateBoard( _gameDim, isFreePlay )
{
    gameDim = _gameDim;
    board = createArray( gameDim, gameDim );

    document.write( `<link type="text/css" rel="gameOfLife.css" />
                        <div id="flexContainer">
                            <div id="flexLeft">
                                <h3>Generations per Second:</h3>
                                <input type="range" min="0" max="${SLIDER_MAX}" value="0" onchange="speedChanged(this.value)" oninput="speedChanging(this.value)" id="speedRange"></input>
                                <label id="speed">0</label><br/>
                                <h3>Preset</h3>
                                <select id="presetsSelect" onchange="presetSelected(this.value)"></select>
                                <canvas id="previewCanvas" width="200" height="200" moz-opaque></canvas>
                                <button>Reset</button>
                            </div>
                            <div id="flexMiddle">
                                <center>
                                    <canvas id="myCanvas" width="${gameDim * cellsize}" height="${gameDim * cellsize}" style="min-width: ${gameDim}; min-height: ${gameDim}; background: black;" moz-opaque></canvas>
                                </center>
                            </div>
                            <div id="flexRight">
                                <table>
                                    <tr>
                                        <td>FPS</td>
                                        <td><label id="displaysLabel"></label></td>
                                    </tr>
                                    <tr>
                                        <td>GPS</td>
                                        <td><label id="generationsLabel"></label></td>
                                    </tr>
                                </table>
                                <input type="button" value="randomize (debug)" onclick="randomBoard()"/><br/ >
                                Score<br />
                                MaxScore<br />
                                Drei Makk Fuffich<br />
                            </div>
                        </div>`  );



    sliderWidth = document.getElementById( "speedRange" ).clientWidth; //TODO
    /*
    document.write(`<input type="button" name="resetButton" value="Test DB functions" onclick="testDb()"/><br/><br/>`);
    document.write(`<input type="submit" value="dataTransfer" onclick="fuckload2tmp()"/><br/><br/>` );
    */
    //Generate canvas

    document.write( "<br/><br/>" );

    var c = document.getElementById( "myCanvas" );
    ctx = c.getContext( "2d" );

    previewCtx = document.getElementById( "previewCanvas" ).getContext( "2d" );

    c.addEventListener( "mousedown", function ( evt )
    {
        var mousePos = getMousePos( c, evt );

        if ( board[mousePos.x][mousePos.y] )
        {
            board[mousePos.x][mousePos.y] = false;
            ctx.fillStyle = offFillStyle;
            drawPixel( mousePos.x, mousePos.y );
        }
        else
        {
            board[mousePos.x][mousePos.y] = true;
            ctx.fillStyle = onFillStyle;
            drawPixel( mousePos.x, mousePos.y );
        }
    }, false );


    ctx.fillStyle = offFillStyle;
    for ( var j = 0; j < board.length; j++ )
        for ( var i = 0; i < board.length; i++ )
        {
            board[i][j] = false;
            drawPixel( i, j );
        }

    window.onresize = maximizeCanvas;

    maximizeCanvas();
}

//Maximizes the canvas' size while making sure that cells always are 1 pixel² in size.
function maximizeCanvas()
{
    var canvas = document.getElementById( "myCanvas" );

    //Don't go by the canvas' width or the flex's middle column itself because that won't scale
    // back when the window is sized smaller.
    var canvasMaxWidth = window.innerWidth
                        - document.getElementById( "flexLeft" ).clientWidth
                        - document.getElementById( "flexRight" ).clientWidth
                        - canvas.style.marginLeft
                        - canvas.style.marginRight;

    var oldCellSize = cellsize;
    cellsize = Math.floor( canvasMaxWidth / gameDim );

    if ( oldCellSize != cellsize && cellsize > 0 ) //Only update if the cellsize changed for usability reasons
    {
        //Stop running simulation to avoid the browser being overloaded
        document.getElementById( "speedRange" ).value = 0;
        speedChanged( 0 );

        canvas.width = canvas.height = cellsize * gameDim;
        display();
    }

}

//====================================================================================================
//      Main Loop
//====================================================================================================

var lastDisplayTime;
var lastTickTime;
var nextTickTime;
var isInTickTimeout;

function loop()
{

    if ( !isRunning )
    {
        return;
    }

    if ( isSpeedLimited
        && Date.now() < nextTickTime ) //If the loop is running too fast, slow it down
    {
        isInTickTimeout = true;
        waitForTick = setTimeout( tickAndDisplay, ( nextTickTime - Date.now() ) ); //Sleep until next tick can be done
    }
    else
    {
        tickAndDisplay();
    }

}

function tickAndDisplay()
{
    if ( isInTickTimeout )
    {
        isInTickTimeout = false;
    }

    nextTickTime = Date.now() + desiredSpeed_ms; //Next tick can be done after desiredSpeed_ms ms have passed

    tick();

    if ( Date.now() - lastDisplayTime >= FRAMERATE_MS ) //If enough time has passed since the last display, display again
    {
        display();
        lastDisplayTime = Date.now();
        setTimeout( loop ); //Give the display thread time to react
    }
    else
    {
        loop();
    }
}

//====================================================================================================
//      
//====================================================================================================

//Can create multidimensional arrays
function createArray( length )
{
    var arr = new Array( length || 0 ),
        i = length;

    if ( arguments.length > 1 )
    {
        var args = Array.prototype.slice.call( arguments, 1 );
        while ( i-- ) arr[length - 1 - i] = createArray.apply( this, args );
    }

    return arr;
}

function randomBoard()
{
    iterations = 0;
    seconds = 0;

    for ( var y = 0; y < gameDim; y++ )
    {
        for ( var x = 0; x < gameDim; x++ )
        {
            if ( Math.random() < 0.5 )
            {
                ctx.fillStyle = offFillStyle;
                drawPixel( x, y );
                board[x][y] = false;
            }
            else
            {
                ctx.fillStyle = onFillStyle;
                drawPixel( x, y );
                board[x][y] = true;
            }
        }
    }
}

function drawPixel( x, y )
{
    ctx.fillRect( x * cellsize * 1.01, y * cellsize * 1.01, cellsize, cellsize );
}

function getMousePos( canvas, evt )
{
    var rect = canvas.getBoundingClientRect();
    return {
        x: Math.floor(( evt.clientX - rect.left ) / cellsize ),
        y: Math.floor(( evt.clientY - rect.top ) / cellsize )
    };
}

function speedChanging( newValue )
{
    if ( newValue == SLIDER_MAX )
    {
        document.getElementById( "speed" ).innerHTML = "&infin;";
    }
    else
    {
        document.getElementById( "speed" ).innerHTML = `${newValue}/${SLIDER_MAX - 1}`;
    }

    document.getElementById( "speed" ).style = `color: #AAA; margin-left:${( newValue / SLIDER_MAX ) * sliderWidth}px;`;
}

function speedChanged( newValue )
{
    desiredSpeed = newValue;

    if ( desiredSpeed == SLIDER_MAX )
    {
        document.getElementById( "speed" ).innerHTML = "&infin;";
        isSpeedLimited = false;
    }
    else
    {
        document.getElementById( "speed" ).innerHTML = `${desiredSpeed}/${SLIDER_MAX - 1}`;
        isSpeedLimited = true;
    }

    document.getElementById( "speed" ).style = `color: black; margin-left:${( desiredSpeed / SLIDER_MAX ) * sliderWidth}px;`;

    document.getElementById( "generationsLabel" ).innerHTML = "GPS";
    document.getElementById( "displaysLabel" ).innerHTML = "FPS";

    //TODO

    if ( timerInterval != null )
    {
        clearInterval( timerInterval );
        iterations = 0;
        currIterations = 0;
        currDisplayIterations = 0;
        seconds = 0;
    }

    // If a loop is in timeout, kill it
    if ( isInTickTimeout )
    {
        clearTimeout( waitForTick );
    }

    if ( !isRunning )
    {
        if ( desiredSpeed != 0 ) //Start loop
        {
            desiredSpeed_ms = 1000 / desiredSpeed;

            lastDisplayTime = Date.now();
            lastTickTime = Date.now();

            timerInterval = setInterval( timer, 1000 );

            isRunning = true;

            loop();
        }
    }
    else
    {
        if ( desiredSpeed == 0 ) //Stop loop
        {
            isRunning = false;
        }
        else //change loop speed
        {
            desiredSpeed_ms = 1000 / desiredSpeed;

            lastDisplayTime = Date.now();
            lastTickTime = Date.now();

            timerInterval = setInterval( timer, 1000 );

            if ( isInTickTimeout )
            {   //Loop has been killed
                loop();
            }
        }
    }

}

function timer()
{
    seconds++;
    document.getElementById( "generationsLabel" ).innerHTML = `<span >Last Second: ${currIterations} \t Avg: ${( iterations / seconds ).toFixed( 2 )} </span>`;
    document.getElementById( "displaysLabel" ).innerHTML = `<span >Last Second: ${currDisplayIterations} \t Avg: ${( displayIterations / seconds ).toFixed( 2 )} </span>`;
    currIterations = 0;
    currDisplayIterations = 0;
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
    var toBeKilled = [];

    for ( var y = 0; y < gameDim; y++ )
        for ( var x = 0; x < gameDim; x++ )
        {
            neighboringCells = 0;

            //Iterate through neighbors
            for ( var j = -1; j <= 1; j++ )
                for ( var i = -1; i <= 1; i++ )
                {
                    //Don't count the cell itself
                    if ( i != 0 || j != 0 )
                    {
                        neighborX = modulo( x + i );
                        neighborY = modulo( y + j );

                        if ( board[neighborX][neighborY] == true )
                        {
                            neighboringCells++;
                        }
                    }
                }

            if ( board[x][y] == false )
            {
                if ( neighboringCells == 3 )
                {
                    toBeRevived.push( new Point( x, y ) );
                }
            }
            else if ( neighboringCells < 2 || neighboringCells > 3 )
            {
                toBeKilled.push( new Point( x, y ) );
            }

        }

    toBeRevived.forEach( function ( cell )
    {
        board[cell.x][cell.y] = true;
    } );

    toBeKilled.forEach( function ( cell )
    {
        board[cell.x][cell.y] = false;
    } );
}

function display()
{
    displayIterations++;
    currDisplayIterations++;

    //TODO: Group more efficiently than this
    var toKill = [];
    var toRevive = [];

    for ( var j = 0; j < board.length; j++ )
        for ( var i = 0; i < board.length; i++ )
        {
            if ( board[i][j] )
            {
                toRevive.push( new Point( i, j ) );
            }
            else
            {
                toKill.push( new Point( i, j ) );
            }
        }

    ctx.fillStyle = onFillStyle;
    toRevive.forEach( function ( key )
    {
        drawPixel( key.x, key.y );
    } );

    ctx.fillStyle = offFillStyle;
    toKill.forEach( function ( key )
    {
        drawPixel( key.x, key.y );
    } );
}

function modulo( value )
{
    if ( value >= gameDim )
    {
        //console.log(value + " -> " + (value - gameDim) );
        return value - gameDim;
    }
    else if ( value < 0 )
    {
        //console.log(value + " -> " + (value + gameDim) );
        return value + gameDim;
    }
    else
    {
        return value;
    }
}

function cellClick( x, y )
{
    insertPreset( 'buddhistLuck' );
    if ( getCellDiv( x, y ).className == "aliveGameCell" )
    {
        getCellDiv( x, y ).className = "deadGameCell";
        board[x][y] = false;
        //console.log(board[x][y]);
    }
    else
    {
        getCellDiv( x, y ).className = "aliveGameCell";
        board[x][y] = true;
        //console.log(board[x][y]);
    }

}

function Point( x, y )
{
    this.x = x;
    this.y = y;

}

function insertPreset( $presetName )
{
    var presetValues = new Array( 200 );
    var strUser = "";
    var optionBox = document.getElementById( "presets" );

    if ( optionBox != null )
        strUser = optionBox.options[optionBox.selectedIndex].text;

    //console.log(strUser);

    switch ( strUser )
    {
        case 'Sauwastika':
            presetValues = presets['Sauwastika'];
            break;
        case 'SquareTest':
            presetValues = presets['SquareTest'];
            break;
        default:
            break;
    }

    //console.log(presetValues)

    //TODO
    presetValues.forEach( function ( item )
    {
        var tmp = item.split( ':' );
        var middle = Math.floor( gameDim / 2 );
        //console.log("x: "  +tmp[0] + " y: " +tmp[1]);
        getCellDiv( middle + parseInt( tmp[0] ), middle + parseInt( tmp[1] ) ).className = "aliveGameCell";
    } );
}

function setPresets( _jsonContent )
{
    json = JSON.parse( _jsonContent );

    var shapes = json["shapes"];

    var select = document.getElementById( "presetsSelect" );

    for ( var shape in shapes )
    {
        var opt = document.createElement( 'option' );
        opt.innerHTML = shape + "\t(" + shapes[shape]["count"] + ")";
        select.appendChild( opt );

        presets.set( shape, shapes[shape]["coordinates"] );
    }
}

function presetSelected(name)
{
    var basename = name.substring( 0, name.indexOf( "(" ) - 1 );

    var preset = presets.get( basename );

    previewCtx.clearRect( 0, 0, previewCtx.width, previewCtx.height );

    for(var index in preset)
    {
        var point = preset[index];

        var x = parseInt(point.substring( 1, point.indexOf( ":" )), 10);
        var y = parseInt( point.substring( point.indexOf( ":" ) + 1, point.length - 1 ), 10 );

        previewCtx.fillStyle = "rgba(255,0,0,1.0)";
        previewCtx.fillRect( 100 + x, 100 + y, 1, 1 );

        board[gameDim / 2 + x][gameDim / 2 + y] = true;
    }

    display();
}