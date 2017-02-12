function fuckload2tmp()
{
    var strRow = "";
    for ( var y = 0; y < gameDim; y++ )
    {
        for ( var x = 0; x < gameDim; x++ )
        {
            if ( board[x][y] == false ) strRow += "0";
            else strRow += "1";
        }
        sendArrayBuffer( "row" + y + " || " + strRow );
        strRow = "";
    }
}

/*
function fuckload(){
	var counter = 0;
		for(var x = 0; x < gameDim; x++){
			sendArrayBuffer("row: "+ (counter++) + "||" +board[x]);
		}
}
*/

function sendArrayBuffer( data )
{

    var http = new XMLHttpRequest();
    var postdata = data;
    http.open( "POST", "https://p0wl.eu/upload.php", true );
    //Send the proper header information along with the request
    http.setRequestHeader( "Content-type", "application/x-www-form-urlencoded" );
    //	http.setRequestHeader("Content-length", postdata.length);
    /*http.onreadystatechange = function() {//Call a function when the state changes.
	   if(http.readyState == 4 && http.status == 200) {
	      alert(http.responseText);
	   }
	}*/
    http.send( postdata );
}