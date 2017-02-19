function uploadBoard()
{
    //send amount of rows
    var http = new XMLHttpRequest();
    http.open( "POST", "./Upload.php", true );
    http.setRequestHeader( "Content-type", "application/x-www-form-urlencoded" );
    http.send( "!" + gameDim + ";" + boardName + ";" + money );

    var strRow = "";
    for ( var y = 0; y < gameDim; y++ )
    {
        for ( var x = 0; x < gameDim; x++ )
        {
            if ( board[x][y] == false ) strRow += "0";
            else strRow += "1";
        }

        var paddedY = "000".substring( 0, 3 - ( "" + y ).length ) + y;

        console.log( paddedY );

        sendArrayBuffer( paddedY + "=" + strRow );
        strRow = "";
    }
}

function sendArrayBuffer( $data )
{
    var http = new XMLHttpRequest();
    http.open( "POST", "./Upload.php", true );

    //Send the proper header information along with the request
    http.setRequestHeader( "Content-type", "application/x-www-form-urlencoded" );
   
   //Call a function when the state changes
   /*  http.onreadystatechange = function() {
	   if(http.readyState == 4 && http.status == 200) {
	      console.log(http.responseText);
	   }}*/
	
    http.send($data);
}