<?
	require_once( "misc_tools.php" );

	$files = array( );

	// tims_lambda_20060711_2327.txt
	$dir = "../lambda_files/";
	if ( $dh = opendir($dir) ) {
	   while ( ( $file = readdir( $dh ) ) ) {
	   	   if ( preg_match( "/".$conf->user."_lambda2?_(\d+)_(\d+).txt/", $file, $matches ) ) {
	   	   	   $files[ $matches[1].$matches[2] ] = array( "file"=>$dir.$file, "date"=>$matches[ 1 ], "time"=>$matches[ 2 ] );
	   	   	   
	   	   }
	   }
	   
	   closedir( $dh );
	}

	$hex = explode( " ", "0 1 2 3 4 5 6 7 8 9 a b c d e f g h i j k l m n o p q r s t u v w x y z" );
	$topics_list = array( );
	$titles_list = array( );
	$link_ids = array( );
	$thread_list = array( ); // timestamps for each discussion thread
	
	ksort( $files );
	reset( $files );
	foreach( $files as $key=>$file_info ) {
		echo "got key: $key<br />\n";
		
		$file = $file_info[ "file" ];
		$date = $file_info[ "date" ];
		$time = $file_info[ "time" ];
		
		$fp = fopen( $file, "r" );
		$parent_link = rtrim( fgets( $fp ) );
		$comment_link = rtrim( fgets( $fp ) );
		$topic_id = rtrim( fgets( $fp ) );
		$pad_id = str_pad( $topic_id, 6, "0", STR_PAD_LEFT );
		$timestamp = $date.$time;
		$topic = rtrim( fgets( $fp ) );
		$reply_topic = rtrim( fgets( $fp ) );
		
		$post = "";
		while ( !feof( $fp ) )
			$post .= fgets( $fp );
		fclose( $fp );
		
		if ( !isset( $topics_list[ $pad_id ] ) ) {
			$titles_list[ $pad_id ] = array( "id"=>$topic_id, "article"=>$topic, "date"=>$date, "link"=>$parent_link );
			$topics_list[ $pad_id ] = array( );
			$link_ids[ $pad_id ] = 0;
		} else {
			$link_ids[ $pad_id ]++;
		}

		$thread_list[ $pad_id ] = "$timestamp $pad_id";
		$topics_list[ $pad_id ][] = array( "id"=>$topic_id, "link"=>$parent_link, "comment_link"=>$comment_link, "link_id"=>$pad_id.$hex[ $link_ids[ $pad_id ] ], "article"=>$topic, "title"=>$reply_topic, "post"=>$post, "datetime"=>"$date $time" );
	}
	
	$output_file = "../pdf/lambda.tex";
	$fp = fopen( $output_file, "w" );

	$head = starttex( "LtU Postings", "", "\\rightmark", "" );
	$head .= "\\begin{document}\n".title2tex( "LtU Postings" );
	$head .= read_file( "template/lambda.tex" );

	echo "count titles:".count($titles_list).", topics:".count($topics_list)."<br />\n";
	
	$titles = "";

	$body = "\chapter{Posts}\n";
	sort( $thread_list );
	foreach( $thread_list as $timestamp_padid ) {
		$items = explode( " ", $timestamp_padid );
		$pad_id = $items[ 1 ];

		$topics_info = $topics_list[ $pad_id ];
		$title_info = $titles_list[ $pad_id ];

		$body .= "\section{".html2tex($title_info[article])."}\n";

		ksort( $topics_info );
		foreach( $topics_info as $post_info ) {
			$body .= "\subsection{".html2tex($post_info[title])."}\n";
			$body .= html2tex( $post_info["post"] );
		}
		
		$body .= "\n";
	}
	
	$footer = "\end{document}";
	
	fputs( $fp, $head );
	fputs( $fp, $titles );
	fputs( $fp, $body );
	fputs( $fp, $footer );
	fclose( $fp );		
	chmod( $output_file, 0666 );

?>