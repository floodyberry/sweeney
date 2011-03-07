<?
	require_once( "misc_tools.php" );

	$replace_anchors = array( );
	function replacement_anchor( $in_text, $anchor_text, $replace_with ) {
		global $replace_anchors;

		$anchor_idx = "%anchor".str_pad( count( $replace_anchors ), 5, "0", STR_PAD_LEFT )."%";
		$dummy = str_replace( $anchor_text, $anchor_idx, $in_text );
		$replace_anchors[] = array( "anchor_index"=>$anchor_idx, "replacement"=>$replace_with );
		return ( $dummy );
	}


	$files = array( );

	// tims_lambda_20060711_2327.txt
	$dir = "../posting_files/";
	if ( $dh = opendir($dir) ) {
	   while ( ( $file = readdir( $dh ) ) ) {
	   	   if ( preg_match( "/".$conf->user."_posting_(\d+)_(\d+).txt/", $file, $matches ) ) {
	   	   	   $files[] = array( "file"=>$dir.$file, "date"=>$matches[ 1 ], "time"=>$matches[ 2 ] );
	   	   	   
	   	   }
	   }
	   
	   closedir( $dh );
	}


	// Text formatting, this really kills us
	$reformat_in = array (
		"/((http|ftp):\/\/[^ \n,()]*)/S", // links
		"/([^<>\"\/])((www|ftp)\.[a-zA-Z]*\....?)([^<>\"\/])/S", // websites
		"/\n/",
	);
	
	$reformat_out = array (
		"<a href=\"\\1\">\\1</a>",
		"\\1<a href=\"http://\\2\">\\2</a>\\4",
		"<br />\n",
	);
	
	$char_in = array( "&", "<", ">", "[blockquote]", "[/blockquote]", "[ul]", "[/ul]", "[li]", "[/li]", "[italic]", "[/italic]", "[bold]", "[/bold]" );
	$char_out = array( "&amp;", "&lt;", "&gt;", "<blockquote><div>", "</div></blockquote>", "<ul>", "</ul>", "<li>", "</li>", "<span class=\"italic\">", "</span>", "<span class=\"strong\">", "</span>" );



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
		$topic = str_replace( $char_in, $char_out, rtrim( fgets( $fp ) ) );
		
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
	
	$output_file = "../posting.html";
	$fp = fopen( $output_file, "w" );
		
	$head = preg_replace( 
		array( "/@keywords/", "/@title/" ),
		array( $conf->keywords.", programming, language, theory, unrealscript, garbage, collection", "$conf->title - Postings" ),
		$conf->header );
	$head .= read_file( "template/posting.html" );

	$titles = "<h2>Subject List</h2>\n<ul>\n";
	ksort( $topics_list );
	foreach( $topics_list as $pad_id => $post_info )
		$titles .= "\t<li><span class=\"strong\">".date_to_string( $post_info[ "datetime" ] )."</span> - <a href=\"#s$pad_id\">$post_info[topic]</a></li>\n";
	$titles .= "</ul>\n\n";


	$body = "<h2>Posts</h2>\n";
	ksort( $topics_list );
	foreach( $topics_list as $pad_id => $post_info ) {
		add_to_timeline( substr( $post_info[ "datetime" ], 0, 8 ), "Posting", "$post_info[topic]", "posting.html#s$pad_id" );

		$post = $post_info[ "post" ];
		
		$post = str_replace( $char_in, $char_out, $post );

		// code tags out
		$match_cnt = preg_match_all( "/\[code\](.*?)\[\/code\]/s", $post, $tags_out, PREG_SET_ORDER );
		for ( $i = 0; $i < $match_cnt; $i++ ) {
			$tag_data = $tags_out[ $i ][ 1 ];
			$replace = "<pre class=\"code_box\">$tag_data</pre>";
			$post = replacement_anchor( $post, $tags_out[ $i ][ 0 ], $replace );
		}

		$post = preg_replace( $reformat_in, $reformat_out, $post );
		
		// Put the code tags back in
		for ( $i = 0; $i < count( $replace_anchors ); $i++ ) {
			$post = str_replace( $replace_anchors[ $i ][ "anchor_index" ], $replace_anchors[ $i ][ "replacement" ], $post );
		}

		$body .= "\t<h3><a href=\"#s$pad_id\"><img src=\"dot.png\" alt=\".\" /></a>".date_to_string( $post_info[ "datetime" ] )." - <a id=\"s$pad_id\" href=\"$post_info[link]\">$post_info[topic]</a></h3><div class=\"plan_alternate\">\n";
		$body .= "$post\n";
		$body .= "\t</div>\n";
	}
	
	fputs( $fp, $head );
	fputs( $fp, $titles );
	fputs( $fp, $body );
	fputs( $fp, $conf->footer );
	fclose( $fp );		
	chmod( $output_file, 0666 );
?>