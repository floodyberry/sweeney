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
	$dir = "../list_files/";
	if ( $dh = opendir($dir) ) {
	   while ( ( $file = readdir( $dh ) ) ) {
	   	   if ( preg_match( "/".$conf->user."_list_(\d+)_(\d+).txt/", $file, $matches ) ) {
	   	   	   $files[] = array( "file"=>$dir.$file, "date"=>$matches[ 1 ], "time"=>$matches[ 2 ] );
	   	   	   
	   	   }
	   }
	   
	   closedir( $dh );
	}

	$topics_list = array( );
	$link_id = 73611495;
	foreach( $files as $file_info ) {
		$file = $file_info[ "file" ];
		$date = $file_info[ "date" ];
		$time = $file_info[ "time" ];
		
		$fp = fopen( $file, "r" );
		$parent_link = rtrim( fgets( $fp ) );
		$topic = rtrim( fgets( $fp ) );
		
		$post = "";
		while ( !feof( $fp ) )
			$post .= fgets( $fp );
		fclose( $fp );
		
		$format_id = str_pad( $link_id++, 6, "0", STR_PAD_LEFT );
		$topics_list[ $format_id ] = array( "id"=>$format_id++, "link"=>$parent_link, "topic"=>$topic, "post"=>$post, "datetime"=>"$date $time" );
	}
	
	$output_file = "../list.html";
	$fp = fopen( $output_file, "w" );
		
	$head = preg_replace( 
		array( "/@keywords/", "/@title/" ),
		array( $conf->keywords.", programming, language, theory, unrealscript, garbage, collection", "$conf->title - Discussion Lists" ),
		$conf->header );
	$head .= read_file( "template/list.html" );

	$titles = "<h2>Subject List</h2>\n<ul>\n";
	ksort( $topics_list );
	foreach( $topics_list as $pad_id => $post_info )
		$titles .= "\t<li><span class=\"strong\">".date_to_string( $post_info[ "datetime" ] )."</span> - <a href=\"#s$pad_id\">$post_info[topic]</a></li>\n";
	$titles .= "</ul>\n\n";


	$body = "<h2>Posts</h2>\n";
	ksort( $topics_list );
	foreach( $topics_list as $pad_id => $post_info ) {
		add_to_timeline( substr( $post_info[ "datetime" ], 0, 8 ), "List", "$post_info[topic]", $post_info["comment_link"] );


		$post = $post_info[ "post" ];
		
		$char_in = array( "&", "<", ">", "[blockquote]", "[/blockquote]", "[ul]", "[/ul]", "[li]", "[/li]", "[italic]", "[/italic]", "[bold]", "[/bold]" );
		$char_out = array( "&amp;", "&lt;", "&gt;", "<blockquote><div>", "</div></blockquote>", "<ul>", "</ul>", "<li>", "</li>", "<span class=\"italic\">", "</span>", "<span class=\"strong\">", "</span>" );
		$post = str_replace( $char_in, $char_out, $post );

		// code tags out
		$match_cnt = preg_match_all( "/\[code\](.*?)\[\/code\]/s", $post, $tags_out, PREG_SET_ORDER );
		for ( $i = 0; $i < $match_cnt; $i++ ) {
			$tag_data = $tags_out[ $i ][ 1 ];
			$replace = "<pre class=\"code_box\">$tag_data</pre>";
			$post = replacement_anchor( $post, $tags_out[ $i ][ 0 ], $replace );
		}

		$post = str_replace( "\n", "<br />\n", $post );
		
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