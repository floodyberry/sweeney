<?
	$lines = file_get_contents( "track.htm" );
	$fp = fopen( "lambda_out.txt", "w+" );
	$count = preg_match_all( "/node\/(\d+)#comment-(\d+)/", $lines, $matches, PREG_SET_ORDER );
	$nodes = array( );
	foreach( $matches as $match ) {
		if ( !isset( $node[ $match[1] ] ) ) {
			fwrite( $fp, "http://lambda-the-ultimate.org/node/$match[1]\n" );
			$node[ $match[1] ] = true;
		}
	}
	fclose( $fp );
	chmod( "lambda_out.txt", 0777 );
?>