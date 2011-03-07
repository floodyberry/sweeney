<?
	class conf {
		public $header, $footer;
		public $user, $fullname;
		public $title, $keywords;
		public $created_on, $compiled_on;
		public $now, $last_utime;
		
		function conf( ) {
			$this->now = date("YmdHi");
			$this->last_utime = utime( );
			$this->created_on = "created_on_$this->now";
			$this->compiled_on = date("l dS \of F Y h:i:s A");
			$this->user = "tims";
			$this->keywords = "Tim, Sweeney, archive, compilation, unreal, epic";
			$this->title = "The Tim Sweeney Archive";
			$this->fullname = "Tim Sweeney";
			
			$this->header = read_file( "template/header.html" );
			$this->footer = read_file( "template/footer.html" );
			$this->footer = str_replace( "@footer", "<span class=\"italic\">Compiled on $this->compiled_on.</span>\n", $this->footer );
		}
	}
	
	$conf = new conf( );
?>