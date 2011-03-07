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

	// List by year
	foreach( $news_yr as $key => $list ) {
		$output_file = "../".$conf->user."_news_".$key.".html";
		$fp = fopen( $output_file, "w" );
		
		
		/*
			HTML header
		*/
		$head = preg_replace( 
			array( "/@keywords/", "/@title/" ),
			array( $conf->keywords.", news", "$conf->title - News archive" ),
			$conf->header );
		$head .= "<h1>".$conf->fullname." News entries for $key</h1>";

		$body = "<h2>Entries</h2>";
		$titles_by_month = array( );
		$anchor_list = array( );
		$box_highlight = 1;
		
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

			// match the h4's
			$title = $date;
			$hash = "d".$date_from_file;
			$title_list = array( );
			$hash_list = array( );

			if ( $count = ( preg_match_all( "/<h4>(.*?)<\/h4>/", $data_news, $matches, PREG_SET_ORDER ) ) ) {
				for ( $i = 0; $i < $count; $i++ ) {
					$subtitle = $matches[ $i ][ 1 ];
					$clean_subtitle = preg_replace( $clean_in, $clean_out, $subtitle );
					if ( !isset( $anchor_list[ $clean_subtitle ] ) ) 
						$anchor_list[ $clean_subtitle ] = 1;
					else
						$clean_subtitle .= "_".$anchor_list[ $clean_subtitle ]++;
					$data_news = str_replace( "<h4>$subtitle</h4>", "<h4 id=\"$clean_subtitle\"><a href=\"#$clean_subtitle\">$subtitle</a></h4>", $data_news );
					$title_list[] = ( $subtitle );
					$hash_list[] = ( $clean_subtitle );
				}
			}

			if ( $box_highlight ) {
				$h = " class=\"plan_alternate\"";
			} else {
				$h = " class=\"plan\"";
			}
			$box_highlight ^= 1;

			// add_to_timeline( $timestamp, $type, $title, $url )
			add_to_timeline( $date_from_file, "News", $title, $conf->user."_news_$key.html#$hash" );

			$body .= "<h3><a href=\"#$hash\"><img src=\"dot.png\" alt=\".\" /></a><a id=\"$hash\"></a>$date</h3>\n<div$h>\n".$data_news."</div>\n";

			// Make a list of titles by month
			$month = substr( $hash, 5, 2 );
			$titles_by_month[ $month ][] = array( "hash"=>$hash, "title"=>$title, "hash_list"=>$hash_list, "title_list"=>$title_list );
		}
		
		$body .= "<p style=\"padding: 5px\"></p>";
		
		// Build title list
		$titles = "<h2>Topics</h2>";
		ksort( $titles_by_month );
		foreach( $titles_by_month as $month => $title_list ) {
			$entries = count( $title_list );
			$entry_txt = "$entries entr";
			if ( $entries == 1 )
				$entry_txt .= "y";
			else
				$entry_txt .= "ies";
			
			$titles .= "<h3>".mon_to_fullstring( $month )." ($entry_txt)</h3><ul>\n";
			
			foreach ( $title_list as $idx => $title_items ) {
				$hash = $title_items[ "hash" ];
				$title = $title_items[ "title" ];
				$title_list = $title_items[ "title_list" ];
				$hash_list = $title_items[ "hash_list" ];
				
				$titles .= "\t<li><a href=\"#$hash\">".date_to_string( substr( $hash, 1, 20 ) )."</a>";
				$count = count( $title_list );
				if ( $count > 0 ) {
					$titles .= "<ul>";
					for ( $i = 0; $i < $count; $i++ )
						$titles .= "<li><a href=\"#$hash_list[$i]\">$title_list[$i]</a></li>\n";
					$titles .= "</ul>";
				}
				$titles .= "</li>\n";
			}

			$titles .= "</ul>\n\n";
		}
		$titles .= "\n\n";
		
		fputs( $fp, $head );
		fputs( $fp, $titles );
		fputs( $fp, $body );
		fputs( $fp, $conf->footer );
		fclose( $fp );		
		chmod( $output_file, 0666 );
	}
	
	// exec( "gzip -f ../$conf->user_????.html" );

?>