//===========================
//      Variables
//===========================

//--------------------------
//  Constants
//--------------------------
const FRAMERATE = 60;
const FRAMERATE_MS = 1000 / FRAMERATE;

//--------------------------
//  Performance measurement
//--------------------------
var displayIterations = 0;
var currDisplayIterations = 0;

//--------------------------
//  Canvas
//--------------------------
var canvas;
var ctx;

//--------------------------
//  Presets
//--------------------------
var presets = new Map();
var previewCtx;

//--------------------------
//  Other
//--------------------------
var sliderWidth = 0;

//====================================================================================================
//      Display Board
//====================================================================================================

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
        deletePixel( key.x, key.y );
    } );

    updateScore();
}

function setDisplayStyle( style )
{
    ctx.clearRect( 0, 0, canvas.width, canvas.height );

    //Stop running simulation to avoid the browser being overloaded
    document.getElementById( "speedRange" ).value = 0;
    speedChanged( 0 );

    switch ( style )
    {
        case "Plain":
            {
                canvas.style.background = "white";
                onFillStyle = "rgba(0,0,0,1.0)";
                offFillStyle = "rgba(255,255,255,1.0)";

                drawPixel = deletePixel = function ( x, y )
                {
                    ctx.fillRect( x * cellsize, y * cellsize, cellsize, cellsize );
                }
            }
            break;
        case "Tech_Lo":
            {
                canvas.style.background = "gray";
                onFillStyle = "rgba(0,200,50,1.0)";
                offFillStyle = "rgba(40,40,40,1.0)";

                drawPixel = deletePixel = function ( x, y )
                {
                    ctx.fillRect( x * cellsize + 0.75, y * cellsize + 0.75, cellsize - 2 * 0.75, cellsize - 2 * 0.75 );
                }
            }
            break;
        case "Tech_Hi":
            {
                canvas.style.background = "gray";
                onFillStyle = "rgba(0,200,50,0.9)";
                offFillStyle = "rgba(40,40,40,0.5)";

                drawPixel = deletePixel = function ( x, y )
                {
                    ctx.fillRect( x * cellsize + 1.0 / 4, y * cellsize + 1.0 / 4, cellsize - 1.0 / 2, cellsize - 1.0 / 2 );
                }
            }
            break;
        case "Dots":
            {
                canvas.style.background = "white";
                onFillStyle = "rgba(200,225,255,1.0)";
                offFillStyle = "rgba(255,255,255,1.0)";
                ctx.strokeStyle = "rgba(0,0,0,1.0)";

                drawPixel = function ( x, y )
                {
                    ctx.beginPath();
                    ctx.arc( x * cellsize + cellsize / 2, y * cellsize + cellsize / 2, cellsize / 4, 0, 2 * Math.PI, false );
                    ctx.fill();
                    ctx.stroke();
                }
                deletePixel = function ( x, y )
                {
                    ctx.fillRect( x * cellsize, y * cellsize, cellsize, cellsize );
                }
            }
            break;
    }

    display();
}

//====================================================================================================
//      User interaction
//====================================================================================================

//Maximizes the canvas' size while making sure that cells always are multiples of 1 pixel² in size. (Subpixels look ugly)
function canvasClicked(evt)
{
    var mousePos = getMousePos( canvas, evt );

    if ( board[mousePos.x][mousePos.y] )
    {
        board[mousePos.x][mousePos.y] = false;
        ctx.fillStyle = offFillStyle;
        drawPixel( mousePos.x, mousePos.y );
        score--;
    }
    else
    {
        board[mousePos.x][mousePos.y] = true;
        ctx.fillStyle = onFillStyle;
        drawPixel( mousePos.x, mousePos.y );
        score++;
    }

    updateScore();
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

            timerInterval = setInterval( oneSecondTimer, 1000 );

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

            timerInterval = setInterval( oneSecondTimer, 1000 );

            if ( isInTickTimeout )
            {   //Loop has been killed
                loop();
            }
        }
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

//====================================================================================================
//      Game Logic interaction
//====================================================================================================

function maximizeCanvas()
{
    var canvas = document.getElementById( "myCanvas" );

    //Don't go by the canvas' width or the flex's middle column itself because that won't scale
    // back when the window is sized smaller.
    var canvasMaxWidth = window.innerWidth
                       - document.getElementById( "flexLeft" ).clientWidth
                       - document.getElementById( "flexRight" ).clientWidth
                       - canvas.style.marginLeft
                       - canvas.style.marginRight
                       - 150;

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

function oneSecondTimer()
{
    seconds++;
    document.getElementById( "generationsLabel" ).innerHTML = `<span >Last Second: ${currIterations} \t Avg: ${( iterations / seconds ).toFixed( 2 )} </span>`;
    document.getElementById( "displaysLabel" ).innerHTML = `<span >Last Second: ${currDisplayIterations} \t Avg: ${( displayIterations / seconds ).toFixed( 2 )} </span>`;
    currIterations = 0;
    currDisplayIterations = 0;
}

function updateScore()
{
    if ( score > maxScore )
    {
        maxScore = score;
    }

    document.getElementById( "scoreLabel" ).innerHTML    = `${score} / ${gameDimSq}  (${(score/gameDimSq*100).toFixed(2)}%)`;
    document.getElementById( "maxScoreLabel" ).innerHTML = `${maxScore} / ${gameDimSq}  (${( maxScore / gameDimSq * 100 ).toFixed( 2 )}%)`;
}

//====================================================================================================
//      Helper functions
//====================================================================================================

function getMousePos( canvas, evt )
{
    var rect = canvas.getBoundingClientRect();
    return {
        x: Math.floor(( evt.clientX - rect.left ) / cellsize ),
        y: Math.floor(( evt.clientY - rect.top ) / cellsize )
    };
}