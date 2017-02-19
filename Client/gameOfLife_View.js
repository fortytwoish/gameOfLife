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
var cursorCanvas;
var cursorCanvasCtx;
var activePreset;
var activePresetName;

//--------------------------
//  Other
//--------------------------
var sliderWidth = 0;
var notificationTimeout; //prevents overlapping notifications

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
        drawPixel( key.x, key.y, cellsize, ctx );
    } );

    ctx.fillStyle = offFillStyle;
    toKill.forEach( function ( key )
    {
        deletePixel( key.x, key.y, cellsize, ctx );
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

                drawPixel = deletePixel = function ( x, y, cellsize, ctx )
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

                drawPixel = deletePixel = function ( x, y, cellsize, ctx )
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

                drawPixel = deletePixel = function ( x, y, cellsize, ctx )
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

                drawPixel = function ( x, y, cellsize, ctx )
                {
                    ctx.beginPath();
                    ctx.arc( x * cellsize + cellsize / 2, y * cellsize + cellsize / 2, cellsize / 4, 0, 2 * Math.PI, false );
                    ctx.fill();
                    ctx.stroke();
                }
                deletePixel = function ( x, y, cellsize, ctx )
                {
                    ctx.fillRect( x * cellsize, y * cellsize, cellsize, cellsize );
                }
            }
            break;
    }

    if ( activePresetName != null )
    {
        presetSelected( activePresetName );
        if(activePreset != null)
        {
            previewCanvasClicked();
        }
    }


    display();
}

function displayNotification(text, upTimeMs)
{
    clearTimeout( notificationTimeout );

    var notificationBar = document.getElementById( "notificationBar" );
    
    //Preview setting of content to calculate accurate height
    notificationBar.style.height = "auto";
    notificationBar.style.visibility = "visible";
    notificationBar.innerHTML = "<span style=\"vertical-align: center;\">" + text + "</span>";
    var maxHeight = notificationBar.offsetHeight;
    console.log( maxHeight );
    notificationBar.innerHTML = "";

    var height = 0;

    var timerInterval = setInterval( function ()
    {
        height += maxHeight/70;
        notificationBar.style.height = height + "px";
        if ( height >= maxHeight )
        {
            notificationBar.innerHTML = "<span style=\"vertical-align: center;\">" + text + "</span>";
            clearInterval( timerInterval );
        }
    }, 10 );

    notificationTimeout = setTimeout( function ()
    {
        notificationBar.innerHTML = null;

        var timerInterval = setInterval( function ()
        {
            height -= maxHeight / 70;
            notificationBar.style.height = height + "px";
            if ( height <= 0 )
            {
                clearInterval( timerInterval );
                notificationBar.style.visibility = "hidden";
            }
        }, 10 );
    }, upTimeMs + 400 ); //400: time taken by popup

}

//====================================================================================================
//      User interaction
//====================================================================================================

//Maximizes the canvas' size while making sure that cells always are multiples of 1 pixel² in size. (Subpixels be ugly)
function canvasClicked(evt)
{
    var mousePos = getMousePos( canvas, evt );

    if ( activePreset != null ) //Preset setting
    {
        ctx.fillStyle = onFillStyle;

        //Iterate the preset's coordinates
        for ( var index in activePreset.coordinates )
        {
            var point = activePreset.coordinates[index];

            var x = Math.floor(mousePos.x + parseInt( point.substring( 1, point.indexOf( ":" ) ), 10 ) - 1);
            var y = Math.floor(mousePos.y + parseInt( point.substring( point.indexOf( ":" ) + 1, point.length - 1 ), 10 ) - 1);

            drawPixel( x, y, cellsize, ctx );

            board[x][y] = true;
        }
    }
    else //normal click
    {
        if ( board[mousePos.x][mousePos.y] )
        {
            board[mousePos.x][mousePos.y] = false;
            ctx.fillStyle = offFillStyle;
            deletePixel( mousePos.x, mousePos.y, cellsize, ctx );
            score--;
        }
        else
        {
            board[mousePos.x][mousePos.y] = true;
            ctx.fillStyle = onFillStyle;
            drawPixel( mousePos.x, mousePos.y, cellsize, ctx );
            score++;
        }

        updateScore();
    }
    
}

function previewCanvasClicked()
{
    //Set Preset
    var presetSelection = document.getElementById( "presetsSelect" );
    if ( presetSelection.value == "None" )
    {
        return;
    }
    var basename        = presetSelection.value.substring( 0, presetSelection.value.indexOf( "(" ) - 1 );
    activePreset        = presets.get( basename );

    //Store Preview canvas as image
    var image = new Image();

    //Draw border and arrow in top left
    cursorCanvasCtx.globalAlpha = 1.0;
    cursorCanvasCtx.fillStyle = "rgba(0,0,0,1.0)";
    cursorCanvasCtx.beginPath();
    cursorCanvasCtx.rect( 0, 0, 127, 127 );
    cursorCanvasCtx.stroke();
    cursorCanvasCtx.fillRect( 0, 0, 12, 6 );
    cursorCanvasCtx.fillRect( 0, 0, 6, 12 );
    cursorCanvasCtx.fillStyle = "rgba(255,255,255,1.0)";
    cursorCanvasCtx.fillRect( 2, 2, 8, 2 );
    cursorCanvasCtx.fillRect( 2, 2, 2, 8 );

    //Reset alpha
    cursorCanvasCtx.globalAlpha = 0.25;
    //Set the cursor
    document.documentElement.style.cursor = "url(" + cursorCanvas.toDataURL( "image/png" ) + "), auto";

    if ( window.localStorage.getItem("hasViewedPresetSettingTutorial") == null )
    {
        displayNotification( "Click the board to insert the Preset.<br/>Press Esc to exit.", 3500 );
        window.localStorage.setItem( "hasViewedPresetSettingTutorial", "x" );
    }
    
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
    activePresetName = name;

    var basename = name.substring( 0, name.indexOf( "(" ) - 1 );

    var preset = presets.get( basename );

    previewCtx.clearRect( 0, 0, 250, 250 );
    cursorCanvasCtx.clearRect( 0, 0, 128, 128 );

    if ( preset == null ) //"None" or unknown preset selected
    {
        return;
    }
    
    previewCtx.fillStyle      = onFillStyle;
    cursorCanvasCtx.fillStyle = onFillStyle;

    //Determine necessary preview space
    //(Subtract 1 to compensate for the 1-based coordinates)
    var xDim           = parseInt(Object.keys( preset.dimension )[0]);
    var maxDim         = Math.max( xDim - 1, parseInt(preset.dimension[xDim]) - 1 );
    var cellSize       = Math.floor( 250 / maxDim );
    var cursorCellSize = Math.floor( 128 / maxDim );

    //Iterate the preset's coordinates
    for ( var index in preset.coordinates )
    {
        var point = preset.coordinates[index];

        var x = parseInt( point.substring( 1, point.indexOf( ":" ) ), 10 ) - 1;
        var y = parseInt( point.substring( point.indexOf( ":" ) + 1, point.length - 1 ), 10 ) - 1;

        drawPixel( x, y, cellSize, previewCtx );
        drawPixel( x, y, cursorCellSize, cursorCanvasCtx );
    }

    if(window.localStorage.getItem("hasViewedPresetSelectionTutorial") == null)
    {
        displayNotification( "You selected a Preset! Click its preview to active it.", 3500 );
        window.localStorage.setItem( "hasViewedPresetSelectionTutorial", "x" );
    }
}

function keyPressed(evt)
{
    switch(evt.key)
    {
        case "Escape":
            cancelPreset();
            break;
    }
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

function cancelPreset()
{
    activePreset = null;
    document.documentElement.style.cursor = "auto";
}