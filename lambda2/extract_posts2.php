<?
	function write_lambda2( $topic_id, $comment_id, $topic, $reply_topic, $date, $post ) {
		$link = "http://lambda-the-ultimate.org/classic/message$topic_id.html";
		$link_comment = "$link#$comment_id";
		
		$topic_id += 524288;
		$topic_id = str_pad( $topic_id, 7, "0", STR_PAD_LEFT );
		
		$name = "../lambda_files/tims_lambda2_".$date.".txt";
		echo "writing $name<br />\n";
		$fp = fopen( $name, "w+" );
		fwrite( $fp, "$link\n" );
		fwrite( $fp, "$link_comment\n" );
		fwrite( $fp, "$topic_id\n" );
		fwrite( $fp, "$topic\n" );
		fwrite( $fp, "$reply_topic\n" );
		fwrite( $fp, "$post" );
		fclose( $fp );
		chmod( $name, 0644 );
	}
	
	function lambda2_info( $file, $topic_id ) {
		$lines = file_get_contents( $file );
		
		// post topic
		preg_match( '/<font color="white"><b>(.+?)<\/b><\/font><br>/si', $lines, $matches );
		$topic = $matches[ 1 ];
		
		// match posts
		$count = preg_match_all( '/<td>\r\n<a href=\'http:\/\/lambda-the-ultimate\\.org\/\'>(.+?)<\/a> - (.+?) <a name="(\\d+?)">&nbsp;<\/a><a href=\'#\\3\' ><img src=\'leftArrow.gif\' height="9" width="11" border="0" alt="blueArrow"><\/a><br>\r\n<font size="-1">(.+?); (.+?) (AM|PM) \\(reads: \\d+?, responses: \\d+?\\)<\/font>\r\n<\/td>\r\n<\/tr><\/table>\r\n<\/td>\r\n<\/tr>\r\n<tr><td>\r\n<table width="100%" cellpadding="3" cellspacing="0" style="border-top: 1px solid navy;"><tr><td>\r\n(.+?)\r\n<p><\/p>\r\n<p><a href=\'#\\3\'><img src=\'skull\\.gif\' height="18" width="22" border="0"><\/a><\/p>/s', $lines, $matches, PREG_SET_ORDER );
		$indexed = 0;
		foreach ( $matches as $match ) {
			
			$user_name = $match[1];
			$reply_topic = $match[2];
			$comment_id = $match[3];
			$post = $match[7];

			if ( $user_name != "Tim Sweeney" )
				continue;
			$indexed++;
			
			// $match[4] == date, $match[5] == time, $match[6] == AM/PM
			echo "date = $match[4] - $match[5] - $match[6] <br />\n";
			preg_match( "/(\d\d?)\/(\d\d?)\/(\d\d\d\d)/", $match[4], $date_matches );
			preg_match( "/(\d\d?):(\d\d):(\d\d)/", $match[5], $time_matches );
			$date_matches[1] = str_pad( $date_matches[1], 2, "0", STR_PAD_LEFT );
			$date_matches[2] = str_pad( $date_matches[2], 2, "0", STR_PAD_LEFT );
			$time_matches[1] = str_pad( $time_matches[1], 2, "0", STR_PAD_LEFT );
			if ( $match[6] == "PM" )
				$time_matches[1] += 12;
			
			$date = "$date_matches[3]$date_matches[1]$date_matches[2]_$time_matches[1]$time_matches[2]";

			$format_in = array ( 
				"/<p>/",
				"/<pre>/",
				"/<\/pre>/",
				"/<blockquote>/",
				"/<\/blockquote>/",
			);

			$format_out = array ( 
				"</p><p>",
				"</p><pre>",
				"</pre><p>",
				"</p><blockquote><div>",
				"</div></blockquote><p>",
			);
			
			$post = preg_replace( $format_in, $format_out, $post );
			$post = "<p>$post</p>";

			write_lambda2( $topic_id, $comment_id, $topic, $reply_topic, $date, $post );
			// echo "link: <a href=\"$link\">$link</a>, topic $topic, user $user_name, date $date, post $post<br />\n";
		}
	}
	
	chdir( "../lambda_files" );
	exec( "rm tims_lambda2_*.txt" );
	chdir( "../lambda2" );
	
	$dir = "./";
	if ( $dh = opendir($dir) ) {
	   while ( ( $file = readdir( $dh ) ) ) {
	   	   if ( preg_match( "/message(\d+).html/", $file, $matches ) ) {
	   	   	   lambda2_info( $dir.$file, $matches[ 1 ] );
	   	   }
	   }
	   
	   closedir( $dh );
	}
?>