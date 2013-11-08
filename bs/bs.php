<?php
class bs{
	//db----------------------------------------------------------------------
	static function DB(){
	}
	static function dbClose(){
	}
	//sql----------------------------------------------------------------------
	static function SQL(){
	}
	//session-------------------------------------------------------------------
	
	//util----------------------------------------------------------------------
	static function tmpl( $val, $arr ){
		if( gettype( $arr ) == 'array' ){
			$i = count( $arr ) - 1; $j = -1;
		}else{
			$i = func_num_args() - 1; $j = 0;
			$arr = func_get_args();
		}
		for( ; $i > $j ; --$i ){
			$t0 = $arr[$i];
			if( $t0[0] == '@' ) $t0 = "'". substr( $t0, 1 ) ."'";
			$val = str_replace( '@@'.$i, $t0, $val );
		}
		return $val;
	}
	static function split( $val, $sep ){return explode( $sep, $val );}
	static function replace( $val, $search, $replace = '' ){return str_replace( $search, $replace, $val );}
	static function stripBr( $val ){return preg_replace( '/[\s]*[\s]/', ' ', preg_replace( '/(<br \/\>)|(<br>)/', ' ', $val ) );}
	static function lineBr( $val ){return preg_replace( '/[\n]/', '<br>', $val );}
	static private $SALT = 'bsidesoft13&%)(*7';
	static function en( $val ){
		return trim( base64_encode( mcrypt_encrypt(
			MCRYPT_RIJNDAEL_256, self::$SALT, $val, MCRYPT_MODE_ECB,
			mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB),MCRYPT_RAND)
		) ) );
	}
	static function de( $val ){ 
		return trim( mcrypt_decrypt(
			MCRYPT_RIJNDAEL_256, self::$SALT, base64_decode( $val ), MCRYPT_MODE_ECB,
			mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND)
		) );
	}
	static function isnum( $data ){
		for( $i = 0, $j = strlen( $data ) ; $i < $j ; $i++ ){
			$t0 = ord( $data[$i] );
			if( $t0 < 48 || $t0 > 57 ) return false;
		}
		return true;
	}
	static function xml( $data ){
		if( $data[0] == '@' ) $data = self::fileR( substr( $data, 1 ) );
		return simplexml_load_string( $data );
	}
	static function xmlGet( $xml, $val ){
		$val = explode( '.', $val );
		for( $i = 0, $j = count( $val ) ; $i < $j ; $i++ ){
			$t0 = $val[$i];
			if( $t0[0] == '@') $xml = $xml[substr( $t0, 1 )];
			else if( str::isnum( $t0 ) ) $xml = $xml[(int)$t0];
			else $xml = $xml->{$t0};
		}
		return $xml;
	}	
	static function apply( $method ){
		$r = null; $method = '$r = '.$method.'(';
		for( $i = 1, $j = count( func_num_args() ), $arg = func_get_args() ; $i < $j ; $i++ ) $method .= '$arg['.$i.']'.( $i < $j - 1 ? ',' : ');' );
		eval( $method );
		return $r;
	}
	static function rand( $start, $end ){return mt_rand( $start, $end );}
	static function len( $data, $add = 0 ){return gettype( $data ) == 'string' ? strlen( $data ) : count( $data ) + $add;}
	//http----------------------------------------------------------------------
	static function get( $url ){
		$t0 = curl_init();
		curl_setopt( $t0, CURLOPT_HEADER, FALSE );
		curl_setopt( $t0, CURLOPT_RETURNTRANSFER, TRUE );
		curl_setopt( $t0, CURLOPT_POST, TRUE );
		if( ( $j = func_num_args() ) > 1 ){
			$i = 0; $t1 = func_get_args(); $url .= "?";
			while( $i < $j ) $url .= $t1[$i++].'='.$t1[$i++].($i < $j - 1 ? '&' : '');
		}
		curl_setopt( $t0, CURLOPT_URL, $url );
		$t1 = curl_exec( $t0 );
		curl_close( $t0 );
		return $t0 === FALSE ? curl_error( $t0 ) : $t1;
	}
	static function post( $url ){
		$t0 = curl_init();
		curl_setopt( $t0, CURLOPT_HEADER, FALSE );
		curl_setopt( $t0, CURLOPT_RETURNTRANSFER, TRUE );
		curl_setopt( $t0, CURLOPT_POST, TRUE );
		curl_setopt( $t0, CURLOPT_URL, $url );
		if( func_num_args() > 1 ) curl_setopt( $t0, CURLOPT_POSTFIELDS, array_shift( func_get_args() ) );
		$t1 = curl_exec( $t0 );
		curl_close( $t0 );
		return $t0 === FALSE ? curl_error( $t0 ) : $t1;
	}
	static private $outBuffer = '';
	static function buffer(){
		if( ( $j = func_num_args() ) > 0 ){
			for( $i = 0, $arg = func_get_args() ; $i < $j ; $i++ ) self::$outBuffer .= $arg[$i];
		}else{
			echo( self::$outBuffer );self::$outBuffer = '';
		}
	}
	static function out(){
		for( $t0 = '', $i = 0, $j = func_num_args(), $arg = func_get_args() ; $i < $j ; $i++ ) $t0 .= $arg[$i];
		echo( $t0 );
	}
	static function end( $val = null ){self::dbClose();exit($val);}
	static function script( $val ){echo('<script>'.$val.'</script>');}
	static function go( $val, $isTop = 0 ){self::script( ( $isTop ? 'top.' : '' )."location.href='".$val."';" );}
	static function reload( $isTop = 0 ){self::script( ( $isTop ? 'top.' : '' ).".location.reload();" );}
	static function back(){self::script( "history.back();" );}
	static function alert( $val ){self::script( "alert('". $val ."');" );}
	static function alertB( $val, $url = null, $isTop = 0 ){self::alert( $val ); $url ? self::go( $url, $isTop ) : self::back();}
	static function domain(){return $_SERVER['HTTP_HOST'];}
	static function root(){return $_SERVER['DOCUMENT_ROOT'].'/';}
	static function ip(){return $_SERVER['REMOTE_ADDR'];}
	static function url(){return $_SERVER['SCRIPT_NAME'];}
	static function encode( $val ){return urldecode( trim( $val ) );}
	static function decode( $val ){return urldecode( trim( $val ) );}
	static function g( $val, $default = false ){return @$_GET[$val] ? trim( $_GET[$val] ) : $default;}
	static function p( $val, $default = false ){return @$_POST[$val] ? trim( $_POST[$val] ) : $default;}
	static function in( $val, $default = false ){
		$r = self::get( $val, $default );
		if( $r ) return $r;
		return self::post( $val, $default );
	}
	static function ck(){		
		$arguments = func_get_args();
		$i = func_num_args();
		$key = $arguments[0];
		if( $i == 1 ){
			$t0 = @$_COOKIE[$key];
			if( $t0 )return gettype( $t0 ) == 'integer' ? (int)$t0 : $t0 .'';
			else return null;
		}else if( $arguments[1] === null ){
			self::ck( $key, '' );
			setcookie( $key, '', time()-3600, '/' );
		}else{
			$expires = $i < 3 ? 0 : $arguments[2];
			$domain = $i < 4 ? null : $arguments[3];
			setcookie( $key, $arguments[1], $expires, '/', $domain );
		}
	}
	static function mail( $from, $to, $subject, $contents, $cc = NULL, $bcc = NULL ){
		$charset = 'utf-8';
		$encoded_subject = "=?". $charset ."?B?". base64_encode( $subject ) ."?=";
		$t0 = explode( ',', $to );
		if( count( $t0 ) == 2 ) $to = "=?". $charset ."?B?".base64_encode( trim( $t0[0] ) ) ."?= <". trim( $t0[1] ) .">" ;
		$t0 = explode( ',', $from );
		if( count( $t0 ) == 2 ) $from = "=?". $charset ."?B?".base64_encode( trim( $t0[0] ) )."?= <". trim( $t0[1] ) .">";
		$headers =
			"From: ". $from ."\r\n". "Reply-To: ". $from ."\r\n".
			"Content-type: text/html; charset=". $charset ."\r\n".
			"Content-Transfer-Encoding: 8bit\r\n";
		if( $cc ) $headers .= "cc: ". $cc ."\r\n";
		if( $bcc ) $headers .= "bcc: ". $bcc;
		return mail( $to, $encoded_subject, $contents, $headers );
	}
	//upload----------------------------------------------------------------------
	static private $upath = '';
	static private $path = '';
	static function upPath( $path ){
		if( $path == NULL ) $path = 'up/';
		self::$path = '/'. $path;
		self::$upath = self::root() . $path;
	}
	static function up(){
		$arguments = func_get_args();
		$i = func_num_args() ;
		$fld = $i < 1 ? 'upfile' : $arguments[0];
		$path = $i < 2 ? NULL : $arguments[1];
		$type = $i < 3 ? 'img' : $arguments[2];
		$max = $i < 4 ? NULL : $arguments[3];
		$fname = self::uCheck( $fld, $path, $type, $max );
		if( $fname && self::uMove( $fld, $fname ) ) return self::$path . $fname;
		else return false;
	}
	static private function uCheck(){
		$arguments = func_get_args();
		$files = $_FILES[$arguments[0]];
		self::uPath( $arguments[1] );
		//파일체크
		if( !is_uploaded_file( $files['tmp_name'] ) )return false;
		//이름변환
		$t0 = explode( '.', $files['name'] );
		$name = date( 'Ymd_his_' ) . self::ip();
		$ext = strtolower( $t0[1] );
		$path = $name .'.'. $ext;		
		//확장자필터링
		$t0 = $arguments[2];
		if( $t0 == 'all' ) $t1 = array( 'doc', 'docx', 'ppt', 'pptx', 'pdf', 'hwp', 'zip', 'jpg', 'gif', 'png' );
		else if( $t0 == 'img' ) $t1 = array( 'jpg', 'gif', 'png' );
		else $t1 = explode( ',', $t0 );
		$t2 = false;
		for( $i = 0, $j = count( $t1 ) ; $i < $j ; $i++ ){
			if( $ext == $t1[$i] ){
				$t2 = true;
				break;
			}
		}
		if( $t2 == false ) return false;
		//사이즈필터링
		$t0 = $arguments[3];
		if( $t0 && $t0 < $files['size'] ) return false;
		//업로드폴더확인
		$t0 = self::root();
		$t1 = explode( '/', str_replace( $t0, '', self::$upath ) );
		for( $i = 0, $j = count( $t1 ) ; $i < $j ; ++$i ){
			if( $t1[$i] != '' ){
				$t0 .= $t1[$i] .'/';
				if( !is_dir( $t0 ) ) mkdir( $t0 );
			}
		}
		//중복이름확인
		if( file_exists( self::$upath . $path ) ){
			for( $i = 1 ; ; $i++ ){
				$t0 = $name .'_'. $i .'.'. $ext;
				if( !file_exists( self::$upath . $t0 ) ){
					$r = $t0;
					break;
				}
			}
		}else $r = $path;
		return $r;
	}
	static private function uMove( $val, $name ){
		if( !move_uploaded_file( $_FILES[$val]['tmp_name'], self::$upath.$name ) ) return false;
		else return true;
	}
	/*function bsUpresize( $f = 'upfile', $path = NULL, $w = 50, $h = 50, $ftype = 'img', $maxsize = 0 ){
		global $_bsUp;
		$fileName = _bsUpfileCheck( $f, $path, $ftype, $maxsize );
		if( $fileName ){		
			$ext = substr( $fileName, -3 );
			switch( $ext ){
			case'gif': $img = imagecreatefromgif( $_FILES[$f]['tmp_name'] ); break;
			case'jpg': $img = imagecreatefromjpeg( $_FILES[$f]['tmp_name'] ); break;
			case'png': $img = imagecreatefrompng( $_FILES[$f]['tmp_name'] ); break;
			}		
			$imgx = imagesx( $img ); $imgy = imagesy( $img );
			if( $w < $imgx || $h < $imgy ){
				if( $w / $h <= $imgx / $imgy ){//가로기준 축소
					$width = $w;
					$height = (int)( $imgy * $w / $imgx);
					$px = 0;
					$py = (int)( ( $h - $height ) / 2 );
				}else{
					$width = (int)( $imgx * $h / $imgy );
					$height = $h;
					$px = (int)( ( $w - $width ) / 2 );
					$py = 0;
				}
				$source = imagecreatetruecolor( $w, $h );
				$copy = imagecreatetruecolor( $width, $height );
				if( $ext == 'png' ){
					$image = imagecreatefromgif( $_bsUp['noneImg'] );
					imagealphablending( $source, false );
					imagesavealpha( $source, true );
					imagecopyresampled( $source, $image, 0, 0, 0, 0, $w, $h, 1, 1 );
					
					imagealphablending( $copy, false );
					imagesavealpha( $copy, true );
					imagecopyresampled( $copy, $img, 0, 0, 0, 0, $width, $height, $imgx, $imgy );
				}else{
					$back = imagecolorallocate( $source, 255, 255, 255 );
					imagefilledrectangle( $source, 0, 0, $w, $h, $back );//--이거 안하면 배경이 검정색이 됨...
					imagecopyresized( $copy, $img, 0, 0, 0, 0, $width, $height, $imgx, $imgy );
				}
				imagecopy( $source, $copy, $px, $py, 0, 0, $width, $height );
				switch( $ext ){
				case'gif': imagegif( $source, $_bsUp['upPath'].$fileName ); break;
				case'jpg': imagejpeg( $source, $_bsUp['upPath'].$fileName ); break;
				case'png': imagepng( $source, $_bsUp['upPath'].$fileName ); break;
				}
				imagedestroy( $source );
				imagedestroy( $copy );
			}else{
				if( _bsUpfileMove( $f, $fileName ) ){		
					return $fileName;
				}else{
					return NULL;
				}
			}
			return $fileName;
		}else{
			return NULL;
		}
	}*/
	//file----------------------------------------------------------------------
	static function fileChk( $path, $make = false ){
		if( $make == true ){
			if( !is_dir( $path ) ) mkdir( $path );
			return true;
		}else{
			return is_dir( $path );
		}	
	}
	static function fileR( $file ){
		$root = self::root();
		if( substr( $file, 0, 5 ) != substr( $root, 0, 5 ) ) $file = $root.( $file[0] == '/' ? '' : '/' ).$file;
		if( !( $file = fopen( $file, "r" ) ) ) die( "could not open file" );
		$t0 = '';
		while( $t1 = fread( $file, 4096 ) ) $t0 .= $t1;
		return $t0;
	}
	static public function fileW( $file, $contents ){
		$t0 = self::root().$path;
		if( substr( $path, 0, 1) == '/' ) $path = substr( $path, 1 );
		if( substr( $path, -1 ) != '/' ) $path .= '/';
		if( !self::fileChk( $t0 ) ) exit( 'no folder' );
		$file = @fopen( $t0 . $filename , "w+" );
		if( !$file ) exit( 'fail' );
		@flock( $file, LOCK_EX );
		fwrite($file, pack("CCC",0xef,0xbb,0xbf));
		fwrite( $file, $contents );
		@flock( $file, LOCK_UN );
		@fclose( $file );
	}
	//date----------------------------------------------------------------------
	static function datePart( $part, $date = null ){
		$time = self::dateGet( $date );
		if( strpos( $part, 'w' ) ) $part = str_replace( 'w', self::datePart_( 'w', $time ), $part );
		else if( strpos( $part, 'a' ) ) $part = str_replace( 'a', self::datePart_( 'a', $time ), $part );
		return date( $part, $time );
	}
	static private function datePart_( $part, $date = null ){
		switch( $part ){
		case'a':
			switch( date( 'a', $date ) ){
			case'am': return '오전';
			case'pm': return '오후';
			}
			break;
		case'w':
			switch( date( 'w', $date ) ){
			case 0: return '일';
			case 1: return '월';
			case 2: return '화';
			case 3: return '수';
			case 4: return '목';
			case 5: return '금';
			case 6: return '토';
			}
			break;
		}
	}
	static function dateAdd( $interval, $number, $date = null, $part = 'Y-m-d H:i:s' ){
		$time = self::dateGet( $date );
		switch( strtolower( $interval ) ){
		case'y':$time = strtotime( ($number).' year', $time ); break;//year
		case'd':$time = strtotime( ($number).' day', $time ); break;//day
		case'h':$time = strtotime( ($number).' hour', $time ); break;//hour
		case'i':$time = strtotime( ($number).' minute', $time ); break;//minute
		case's':$time = strtotime( ($number).' second', $time ); break;//second
		case'm'://month
			$time = strtotime( self::datePart( 'Y-m',$time ) ).'-01';
			$time = strtotime( ($number).' month', $time ); break;
		default:return null;
		}
		return self::datePart( $part, $time );
	}
	static function dateDiff( $interval, $dateOld, $dataNew = NULL ){
		$date1 = self::dateGet( $dateOld );$date2 = self::dateGet( $dataNew );
		switch( strtolower( $interval ) ){
		case'h':return (int)( ( $date2 - $date1 ) / 3600 );
		case'i':return (int)( ( $date2 - $date1 ) / 60 );
		case's':return $date2 - $date1;
		case'y':return self::datePart( 'y', $date2 ) - self::datePart( 'y', $date1 );
		case'm':return ( self::datePart( 'y', $date2 ) - self::datePart( 'y', $date1 ) ) * 12 + self::datePart( 'm', $date2 ) - self::datePart( 'm', $date1 );
		case'd':
			if( $date2 > $date1 )$order = 1;
			else{
				$order = -1;
				$date1 = self::dateGet( $dataNew );
				$date2 = self::dateGet( $dateOld );
			}
			$d1_year = self::datePart( 'Y', $date1 );
			$d1_month = self::datePart( 'n', $date1 );
			$d1_date = self::datePart( 'j', $date1 );
			$d2_year = self::datePart( 'Y', $date2 );
			$d2_month = self::datePart( 'n', $date2 );
			$d2_date = self::datePart( 'j', $date2 );
			$j = $d2_year - $d1_year;
			$d = 0;
			if( $j > 0 ){
				$d += self::diff( 'd', self::mktime( $d1_year, $d1_month, $d1_date ), self::mktime( $d1_year, 12, 31 ) );
				$d += self::diff( 'd', self::mktime( $d2_year, 1, 1 ), self::mktime( $d2_year, $d2_month, $d2_date ) );
				$year = $d1_year + 2;
				for( $i = 2 ; $i < $j - 1 ; $i++ ){
					$d += self::leapYear( $year )?366:365;
					$year++;
				}
			}else{
				$temp = array( null, 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31 );
				if( self::leapYear( $d1_year ) ) $temp[2]++;
				$j = $d2_month - $d1_month;
				if( $j > 0 ){
					$d += self::dateDiff( 'd', self::mktime( $d1_year, $d1_month, $d1_date ), self::mktime( $d1_year, $d1_month, $temp[$d1_month] ) ) + 1;
					$d += self::dateDiff( 'd', self::mktime( $d2_year, $d2_month, 1 ), self::mktime( $d2_year, $d2_month, $d2_date ) );
					$month = $d1_month + 1;
					for( $i = 1 ; $i < $j ; $i++ ) $d += $temp[$month++];
				}else $d += $d2_date - $d1_date;
			}
			return $d * $order;
		}
		return NULL;
	}
	static private function leapYear( $year ){return ( $year % 4 == 0 && $year % 100 != 0 ) || $year % 400 == 0;}
	static function mktime( $y, $m, $d, $h = 0, $i = 0, $s = 0 ){return mktime( $h, $i, $s, $m, $d, $y );}
	static private function dateGet( $date = NULL ){
		if( gettype( $date ) == 'integer' ) return $date;
		else if( $date ){
			if( strpos( $date, '-' ) === false ) return (int)$date;
			else{
				$i = explode( '-', $date ); $h = $m = $s = 0;
				if( strpos( $i[2], ' ' ) ){
					$temp = explode( ' ', $i[2] );
					$i[2] = $temp[0];
					$temp = explode( ':', $temp[1] );
					$h = (int)$temp[0]; $m = (int)$temp[1]; $s = (int)$temp[2];
				}
				return self::mktime( (int)$i[0], (int)$i[1], (int)$i[2], $h, $m, $s );
			}
		}else return time();
	}
}
?>