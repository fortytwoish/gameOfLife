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

        sendArrayBuffer(y + "=" + strRow );
        strRow = "";
    }
}

function sendArrayBuffer( $data )
{
    var http = new XMLHttpRequest();
    http.open( "POST", "http://pbs2h15ash.webpb.pb.bib.de/GameOfLife/Server/Upload.php", true );
   
    //Send the proper header information along with the request
    http.setRequestHeader( "Content-type", "application/x-www-form-urlencoded" );
   
   //Call a function when the state changes
   /*  http.onreadystatechange = function() {
	   if(http.readyState == 4 && http.status == 200) {
	      console.log(http.responseText);
	   }}*/
	
    http.send($data);
}