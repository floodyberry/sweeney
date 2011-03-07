<?
	function write_lambda( $topic_id, $comment_id, $topic, $reply_topic, $date, $post ) {
		$link = "http://lambda-the-ultimate.org/node/$topic_id";
		$link_comment = "$link#comment-$comment_id";
		
		$topic_id += 1048576;
		
		$name = "../lambda_files/tims_lambda_".$date.".txt";
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
	
	function lambda_info( $file ) {
		$lines = file_get_contents( $file );
		
		// post topic
		preg_match( "/<title>(.+?) \| Lambda the Ultimate<\/title>/", $lines, $matches );
		$topic = $matches[ 1 ];
		
		// match posts
		$count = preg_match_all( '/ <h3 class="title"><a href="node\/(\\d+)#comment-(\\d+)" class="active">(.+?)<\/a><\/h3>.*? <div class="content">(.+?)<\/div>.*? <div class="links">By <a href="(.+?)" title="View user profile\\.">(.+?)<\/a> at ..., (.+?) \\|/s', $lines, $matches, PREG_SET_ORDER );
		$indexed = 0;
		foreach ( $matches as $match ) {
			
			$reply_topic = $match[3];
			$post = $match[4];
			$user_id = $match[5];
			$user_name = $match[6];
			
			if ( $user_id != "user/97" )
				continue;
			$indexed++;
			
			if ( preg_match( "/(\d\d)\/(\d\d)\/(\d\d\d\d) - (\d\d):(\d\d)/", $match[7], $date_matches ) ) {
				$date = "$date_matches[3]$date_matches[1]$date_matches[2]_$date_matches[4]$date_matches[5]";
			} else if ( preg_match( "/(\d\d\d\d)-(\d\d)-(\d\d) (\d\d):(\d\d)/", $match[7], $date_matches ) ) {
				$date = "$date_matches[1]$date_matches[2]$date_matches[3]_$date_matches[4]$date_matches[5]";
			}
			
			
			$clean_in = array( 
				"</p>", 
				"<p>", 
				"<P>", 
				"<p >", 
				"<pre>", 
				"</pre>",
				"<BR >",
				"<BR>",
				"<i >",
				"</i>",
				"&#039;", );
			
			$clean_out = array( 
				"", 
				"<br /><br />", 
				"<br /><br />", 
				"<br /><br />", 
				"</p><pre>", 
				"</pre><p>",
				"",
				"",
				"<span class=\"italic\">",
				"</span>",
				"'", );
			
			$format_in = array ( 
				"/^<br \/><br \/>/",
				"/<br \/><br \/>$/",
				"/<blockquote ><br \/><br \/>/",
				"/<\/blockquote>/",
			);

			$format_out = array ( 
				"",
				"",
				"</p><blockquote><div>",
				"</div></blockquote><p>",
			);
			
			$para_in = array (
				"/<br \/><br \/>/" );
			$para_out = array (
				"</p><p>" );
			
			$post = str_replace( $clean_in, $clean_out, $post );
			$post = preg_replace( $format_in, $format_out, $post );
			$post = preg_replace( $para_in, $para_out, $post );
			$post = "<p>$post</p>";

			write_lambda( $match[1], $match[2], $topic, $reply_topic, $date, $post );
			// echo "link: <a href=\"$link\">$link</a>, topic $topic, user $user_name, date $date, post $post<br />\n";
		}
	}
	
	chdir( "../lambda_files" );
	exec( "rm tims_lambda_*.txt" );
	chdir( "../lambda" );
	
	$dir = "./";
	if ( $dh = opendir($dir) ) {
	   while ( ( $file = readdir( $dh ) ) ) {
	   	   if ( preg_match( "/\d+/", $file ) ) {
	   	   	   lambda_info( $dir.$file );
	   	   }
	   }
	   
	   closedir( $dh );
	}
?>