<?php
require_once( 'util/date.php' );
require_once( 'util/str.php' );
require_once( 'util/http.php' );
require_once( 'util/core.php' );
require_once( 'util/tw.php' );
class util{
	static public function apply( $m, $a ){
		$r = null;
		$m = '$r='.str_replace( '@', 'at', str_replace( '.', '::', $m ) ).'(';
		for( $i = 0, $j = count( $a ) ; $i < $j ; $i++ ) $m .= '$a['.$i.']'.( $i < $j - 1 ? ',' : '' );
		$m = $m.');';
		eval( $m );		
		return $r;
	}
	static function rand( $start, $end ){
		return mt_rand( $start, $end );
	}
	static public function forin( $arg ){
		foreach( $arg as $key => $value ){
			if( gettype( $value ) == 'array' ){				
				echo( '---- '.$key.' ---------------------<br>' );
				util::forin( $value );				
				echo( '++++ '.$key.' +++++++++++++++++++++<br>' );				
			}else{
				echo( $key .' -> '. $value.'<br>' );
			}
		}
	}
}
class ex{
	static public function at(){
		$arguments = func_get_args();
		$i = 1;
		$j = func_num_args();
		if( ( $t0 = $arguments[0] ) == '-' ) $t0 = -$arguments[$i++];
		while( $i < $j ){
			$op = $arguments[$i++];
			$t1 = $arguments[$i++];
			switch( $op ){
			case'+': $t0 += $t1; break;
			case'-': $t0 -= $t1; break;
			case'*': $t0 *= $t1; break;
			case'/': $t0 /= $t1; break;
			case'%': $t0 %= $t1; break;
			case'~': $t0 = rand( $t0, $t1 ); break;
			case'.': $t0 .= $t1; break;
			case'-.': $t0 = substr( $t0, $t1 ); break;//ex( abc, -., 1 ) -> bc
			case'.-': $t0 = substr( $t0, 0, -$t1 );//ex( abc, .-, 1 ) -> ab
			}
		}
		return $t0;
	}
}
class io{
	static function _fChk( $path, $make = false ){
		if( $make == true ){
			if( !is_dir( $path ) ) mkdir( $path );
			return true;
		}else{
			return is_dir( $path );
		}	
	}
	static public function curl( $url, $method, $postfields = NULL ){
		$ci = curl_init();
		curl_setopt( $ci, CURLOPT_HEADER, FALSE );
		curl_setopt( $ci, CURLOPT_URL, $url );
		curl_setopt( $ci, CURLOPT_RETURNTRANSFER, TRUE );
		switch( $method ){
		case'POST':
			curl_setopt( $ci, CURLOPT_POST, TRUE );
			if( !empty( $postfields ) ){
				curl_setopt( $ci, CURLOPT_POSTFIELDS, $postfields );
			}
			break;
		}
		$response = curl_exec( $ci );
		if( $response == false ){
			 echo 'Curl error: ' . curl_error($ci).'<br>';
		}
		curl_close( $ci );
		return $response;
	}
	//io.fWr( /p/voto/require/, test.php,'. $r .' )
	static public function fWr( $path, $filename, $contents ){
		$t0 = http::root() . $path;
		if( substr( $path, 0, 1) == '/' ) $path = substr( $path, 1 );
		if( substr( $path, -1 ) != '/' ) $path .= '/';
		if( !io::_fChk( $t0 ) ) exit( 'no folder' );
		$file = @fopen( $t0 . $filename , "w+" );
		if( !$file ) exit( 'fail' );
		@flock( $file, LOCK_EX );
		fwrite($file, pack("CCC",0xef,0xbb,0xbf));
		fwrite( $file, $contents );
		@flock( $file, LOCK_UN );
		@fclose( $file );
	}
}
?>