//===========================
//      Variables
//===========================

//--------------------------
//  Board
//--------------------------
var gameDim;
var gameDimSq;
var board;
var cellsize;
var isRunning = false;
var isSpeedLimited = true;

//--------------------------
//  Performance measurement
//--------------------------
var iterations = 0;
var currIterations = 0;
var seconds = 0;
var timerInterval;

//--------------------------
//  Loop
//--------------------------
const SLIDER_MAX = 101;
var desiredSpeed = 0;
var desiredSpeed_ms = 0;
var waitForTick;
var lastDisplayTime;
var lastTickTime;
var nextTickTime;
var isInTickTimeout;

//--------------------------
//  Game
//--------------------------
var score    = 0;
var maxScore = 0;

//====================================================================================================
//      Initialization
//====================================================================================================

function generateBoard( _gameDim, isFreePlay )
{
    gameDim = _gameDim;
    gameDimSq = gameDim * gameDim;
    board = createArray( gameDim, gameDim );

    for ( var i = 0; i < gameDim; i++ )
        for ( var j = 0; j < gameDim; j++ )
        {
            board[i][j] = false;
        }

    document.write( `<link type="text/css" rel="gameOfLife.css" />
                        <div id="flexContainer">
                            <div id="flexLeft">
                                <h3>Generations per Second:</h3>
                                <input type="range" min="0" max="${SLIDER_MAX}" value="0" onchange="speedChanged(this.value)" oninput="speedChanging(this.value)" id="speedRange"></input>
                                <label id="speed">0</label><br/>
                                <h3>Preset</h3>
                                <select id="presetsSelect" onchange="presetSelected(this.value)"></select>
                                <canvas id="previewCanvas" width="250" height="250" onclick="previewCanvasClicked()"></canvas>
                                <canvas id="cursorCanvas" style="visibility:hidden;" width="128" height="128"></canvas>
                                <h3>Style</h3>
                                <select onchange="setDisplayStyle(this.value)">
                                    <option>Plain</option>
                                    <option>Tech_Lo</option>
                                    <option>Tech_Hi</option>
                                    <option>Dots</option>
                                </select>
                                <button>Reset</button>
                            </div>
                            <div id="flexMiddle">
                                <center>
                                    <canvas id="myCanvas" width="${gameDim * cellsize}" height="${gameDim * cellsize}" style="min-width: ${gameDim}; min-height: ${gameDim};" moz-opaque></canvas>
                                </center>
                            </div>
                            <div id="flexRight">
                                <h3>Performance</h3>
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
                                <h3>Score Information</h3>
                                <table>
                                    <tr>
                                        <td>Score</td>
                                        <td><label id="scoreLabel"></label></td>
                                    </tr>
                                    <tr>
                                        <td>Max Score</td>
                                        <td><label id="maxScoreLabel"></label></td>
                                    </tr>
                                    <tr>
                                        <td>Money</td>
                                        <td><label id="moneyLabel"></label></td>
                                    </tr>
                                </table>
                                <h3>Debug</h3>
                                <input type="button" value="randomize" onclick="randomBoard()"/><br/ >
                            </div>
                        </div>`  );

    sliderWidth = document.getElementById( "speedRange" ).clientWidth;

    document.write( "<br/><br/>" );

    canvas          = document.getElementById( "myCanvas" );
    ctx             = canvas.getContext( "2d" );
    previewCtx      = document.getElementById( "previewCanvas" ).getContext( "2d" );
    cursorCanvas    = document.getElementById("cursorCanvas");
    cursorCanvasCtx = cursorCanvas.getContext( "2d" );
    cursorCanvasCtx.globalAlpha = 0.25;

    canvas.addEventListener( "mousedown", canvasClicked, false );

    document.addEventListener( "keydown", keyPressed, false );

    setDisplayStyle( "Plain" );

    window.onresize = maximizeCanvas;

    maximizeCanvas();
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
                deletePixel( x, y, cellsize, ctx );
                board[x][y] = false;
            }
            else
            {
                ctx.fillStyle = onFillStyle;
                drawPixel( x, y, cellsize, ctx );
                board[x][y] = true;
            }
        }
    }
}

//====================================================================================================
//      Main Loop
//====================================================================================================

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

function tick()
{
    iterations++;
    currIterations++;

    score = 0;

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
            else
            {

                score++;

                if ( neighboringCells < 2 || neighboringCells > 3 )
                {
                    toBeKilled.push( new Point( x, y ) );
                }
            }

        }

    toBeRevived.forEach( function ( cell )
    {
        board[cell.x][cell.y] = true;
        score++;
    } );

    toBeKilled.forEach( function ( cell )
    {
        board[cell.x][cell.y] = false;
        score--;
    } );
}

//====================================================================================================
//      Helper functions
//====================================================================================================

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

function Point( x, y )
{
    this.x = x;
    this.y = y;

}

//Creates multidimensional arrays
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

//====================================================================================================
//      Database interaction
//====================================================================================================

function setPresets( loadedPresets )
{
    var shapes = loadedPresets["shapes"];

    var select = document.getElementById( "presetsSelect" );

    //Reset dropdown
    select.innerHTML = "<option>None</option>";

    for ( var shape in shapes )
    {
        var opt = document.createElement( 'option' );

        //1. get the largest dimension of the preset
        var xDim = Object.keys( shapes[shape]["dimension"] )[0];
        var maxDim = Math.max( xDim, shapes[shape]["dimension"][xDim] );

        //2. if it exceeds the gameDim, disable the option

        if ( gameDim < maxDim )
        {
            opt.disabled = true;
            opt.innerHTML = shape + " (" + ( Object.keys( shapes[shape]["count"] )[0] - 1 ) + ") - dimensions exceed board";
        }
        else
        {
            opt.innerHTML = shape + " (" + ( Object.keys( shapes[shape]["count"] )[0] - 1 ) + ")";
        }

        select.appendChild( opt );
        
        presets.set( shape, shapes[shape]);
    }
}