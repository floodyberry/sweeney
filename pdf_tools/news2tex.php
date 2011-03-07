<?
	require_once( "misc_tools.php" );

	$dir = "../news_files/";
	
	// Compile a list of news so we can sort them by date
	$news_yr = array( );
	if ( $dh = opendir($dir) ) {
		while ( ( $file = readdir( $dh ) ) !== false ) {
			if ( preg_match( "/".$conf->user."_news_(....)(..)(..)\.html/", $file, $matches ) ) {
				$year = $matches[ 1 ];
				$month = $matches[ 2 ];
				$day = $matches[ 3 ];

				$news_yr[ $year ][] = $file;
			}
		}

		closedir( $dh );
	}
	
	$clean_in = array( "/&amp;/", "/@/", "/[-()'.]/", "/[^a-zA-Z0-9]/", "/^(\d)/" );
	$clean_out = array( "and", "at", "", "_", "d\\1" );

	$output_file = "../pdf/news.tex";
	$fp = fopen( $output_file, "w" );
	
	// build header
	$body = ( starttex( "News", "", "\\rightmark", "" ) );
	$body .= "\\begin{document}\n".title2tex( "News" );
	
	$body .= "\\chapter{Prologue}\n";
	$body .= "This is a collection of (almost) all the news posts by Tim Sweeney from the original Unreal Technology archive. Posts not by Tim Sweeney or not really having anything engine-related may have been left out. The archive continues to 2003, but Tim Sweeney unfortunately stopped posting in early 2000.\n";

	// List by year
	ksort( $news_yr );
	foreach( $news_yr as $key => $list ) {
		$body .= "\\chapter{".$key." News}\n";

		// Sort by date
		asort( $list );
		foreach( $list as $file ) {
			// Read the file in, extract date
			$fp_news = fopen( $dir.$file, "r" );
			$data_news = ltrim( fread( $fp_news, filesize( $dir.$file ) ) );
			fclose( $fp_news );
			preg_match( "/(\d\d\d\d)(\d\d)(\d\d)/", $file, $matches );
			$date_from_file = $matches[ 1 ].$matches[ 2 ].$matches[ 3 ];
			$date = date_to_string( $date_from_file );

			$body .= "\\section{".$date."}\n";
			$body .= html2tex($data_news)."\n";
		}
	}
	
	$body .= "\end{document}";
	fputs( $fp, $body );
	fclose( $fp );		
	chmod( $output_file, 0666 );

	
	// exec( "gzip -f ../$conf->user_????.html" );

?>