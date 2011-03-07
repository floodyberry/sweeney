<?
	require_once( "misc_tools.php" );

	$replace_anchors = array( );
	function replacement_anchor( $in_text, $anchor_text, $replace_with ) {
		global $replace_anchors;

		$anchor_idx = "ANCHOR".str_pad( count( $replace_anchors ), 5, "0", STR_PAD_LEFT );
		$dummy = str_replace( $anchor_text, $anchor_idx, $in_text );
		$replace_anchors[] = array( "anchor_index"=>$anchor_idx, "replacement"=>$replace_with );
		return ( $dummy );
	}


	$files = array( );
	$dir = "../posting_files/";
	if ( $dh = opendir($dir) ) {
	   while ( ( $file = readdir( $dh ) ) ) {
	   	   if ( preg_match( "/".$conf->user."_posting_(\d+)_(\d+).txt/", $file, $matches ) ) {
	   	   	   $files[] = array( "file"=>$dir.$file, "date"=>$matches[ 1 ], "time"=>$matches[ 2 ] );
	   	   	   
	   	   }
	   }
	   
	   closedir( $dh );
	}

	$topics_list = array( );
	$link_ids = array( );
	sort( $files );
	foreach( $files as $file_info ) {
		$file = $file_info[ "file" ];
		$date = $file_info[ "date" ];
		$time = $file_info[ "time" ];
		
		$fp = fopen( $file, "r" );
		$parent_link = rtrim( fgets( $fp ) );
		$parent_link = str_replace( array( "&amp;", "&" ), array( "&", "&amp;" ), $parent_link );
		$topic = rtrim( fgets( $fp ) );
		
		$post = "";
		while ( !feof( $fp ) )
			$post .= fgets( $fp );
		fclose( $fp );
		
		$link_id = "$date$time";
		if ( isset( $link_ids[ $link_id ] ) ) {
			$link_ids[ $link_id ]++;
			$link_id .= $link_ids[ $link_id ];
		} else {
			$link_ids[ $link_id ] = 0;
			$link_id .= "0";
		}

		$topics_list[ $link_id ] = array( "id"=>$format_id++, "link"=>$parent_link, "topic"=>$topic, "post"=>$post, "datetime"=>"$date $time" );
	}
	
	$output_file = "../pdf/posting.tex";
	$fp = fopen( $output_file, "w" );
		
	// build header
	$head = ( starttex( "Postings", "", "\\rightmark", "" ) );
	$head .= "\\begin{document}\n".title2tex( "Postings" );
	$body = "\\chapter{Prologue}\nTim Sweeney's postings to various discussion lists and such. Dates are removed so the section titles are not as cluttered.\n";
	$body .= "\\chapter{Posts}\n";

	ksort( $topics_list );
	foreach( $topics_list as $pad_id => $post_info ) {
		$post = $post_info[ "post" ];
		$topic = $post_info[ "topic" ];


		// Pre-parse the code out
		$match_cnt = preg_match_all( "/\[code\](.*?)\[\/code\]/s", $post, $tags_out, PREG_SET_ORDER );
		for ( $i = 0; $i < $match_cnt; $i++ ) {
			$tag_data = $tags_out[ $i ][ 1 ];
			$replace = "\\begin{Verbatim}[fontsize=\\small]\n$tag_data\n\end{Verbatim}\n";
			$post = replacement_anchor( $post, $tags_out[ $i ][ 0 ], $replace );
		}

		// Problem characters
		$post = str_replace( array( "&", "<", ">" ), array( "&amp;", "&lt;", "&gt;" ), $post );
		$topic = str_replace( array( "&", "<", ">" ), array( "&amp;", "&lt;", "&gt;" ), $topic );

		// url
		$post = preg_replace( 
			array (
				"/((http|ftp):\/\/[^ \n,]*)/S", // links
				"/([^<>\"\/])((www|ftp)\.[a-zA-Z]*\....?)([^<>\"\/])/S", // websites
 			),
			
			array (
				"<a href=\"\\1\">\\1</a>",
				"\\1<a href=\"http://\\2\">\\2</a>\\4",
			),
			$post
		);

		$post = html2tex( $post );

		$post = preg_replace( 
			array (	"/(\n\n|\xd\xa\xd\xa)(\n|\xd\xa)*/" ),
			array (	"\\par " ),
			$post
		);

		
		// Put the code tags back in
		for ( $i = 0; $i < count( $replace_anchors ); $i++ ) {
			$post = str_replace( $replace_anchors[ $i ][ "anchor_index" ], $replace_anchors[ $i ][ "replacement" ], $post );
		}

		$body .= "\\section{".html2tex($topic)."}\n";
		$body .= $post."\n\n";
	}
	
	$body .= "\end{document}";
	
	fputs( $fp, $head );
	fputs( $fp, $body );
	fclose( $fp );		
	chmod( $output_file, 0666 );
?>