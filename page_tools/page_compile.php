<?
	ini_set( "max_execution_time", 60 * 60 * 60 );

	include( "misc_tools.php" );

	$static_dir = "../static_files/";
	$root_dir = "../";

	// clean root
	if ( $dh = opendir( $root_dir ) ) {
	   while ( ( $file = readdir( $dh ) ) !== false ) {
	   	   if ( strlen( $file ) > 2 ) {
	   	   	   if ( is_file( $root_dir.$file ) ) 
	   	   	   	   unlink( $root_dir.$file );
	   	   }
	   }
	   closedir( $dh );
	}

	reset_timeline( );
	include( "interview2html.php" );
	include( "news2html.php" );
	include( "lambda2html.php" );
	include( "posting2html.php" );

	write_template( "news.html", ", news", " - News Archive" );
	write_template( "pdfs.html", ", pdf", " - .pdfs" );
	
	// build latest postings for index
	krsort( $timeline );
	$text = "<h2>Recent Items</h2>\n<ul>";
	$items = 0;
	foreach( $timeline as $timestamp => $link_array ) {
		rsort( $link_array ); // assume added in ascending date order
		foreach( $link_array as $idx => $link_info ) {
			if ( $items++ > 20 )
				break;
			$text .= "<li><span class=\"strong\">".date_to_string( $timestamp )."</span> - ($link_info[type]) <a href=\"$link_info[url]\">$link_info[title]</a></li>\n";
		}
	}
	$text .= "</ul>";
	write_template( "index.html", "", "", $text );

	write_timeline( );


	// copy static in
	if ( $dh = opendir( $static_dir ) ) {
	   while ( ( $file = readdir( $dh ) ) !== false ) {
	   	   if ( strlen( $file ) > 2 ) {
	   	   	   copy( $static_dir.$file, $root_dir.$file );
	   	   	   chmod( $root_dir.$file, 0777 ); // :|
	   	   }
	   }
	   closedir( $dh );
	}
	
	include( "page_compress.php" );
?>
