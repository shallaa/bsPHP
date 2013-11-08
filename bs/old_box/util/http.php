<?php
class http{
	static private $outBuffer = '';

	static function out( $val ){
		$arguments = func_get_args();
		for( $i = 0, $j = func_num_args() ; $i < $j ; $i++ ) self::$outBuffer .= $arguments[$i];
	}
	static function flush(){
		echo( self::$outBuffer );
		self::$outBuffer = '';
	}
	static function at( $val ){
		$arguments = func_get_args();
		for( $i = 1, $j = func_num_args() ; $i < $j ; $i++ ) $val .= $arguments[$i];
		echo( $val );
	}
	static function end( $val = null ){
		bs( 'd:', 'close' );
		echo( $val );
		exit();
	}
	static function go( $val, $isTop = 0 ){
		echo( "<script type='text/javascript'>". ( $isTop ? 'top.' : '' ) ."location.href='". $val ."';</script>" );
	}
	static function reload(){
		echo( "<script type='text/javascript'>window.location.reload();</script>" );
	}
	static function back(){
		echo( "<script type='text/javascript'>history.back();</script>" );
	}
	
	static function script( $val ){
		echo( '<script type="text/javascript">'. $val .'</script>' );
	}
	static function alert( $val ){
		http::script( "alert('". $val ."');" );
	}
	static function alertB( $val, $url = null, $isTop = 0 ){
		http::alert( $val );
		$url ? http::go( $url, $isTop ) : http::back();
	}
	
	static function domain(){
		return $_SERVER['HTTP_HOST'];
	}
	static function root(){
		return $_SERVER['DOCUMENT_ROOT'].'/';
	}
	static function ip(){
		return $_SERVER['REMOTE_ADDR'];
	}
	static function url(){
		return $_SERVER['SCRIPT_NAME'];
	}
	static function encode( $val ){
		return urldecode( trim( $val ) );
	}
	static function decode( $val ){
		return urldecode( trim( $val ) );
	}
	static function get( $val, $default = false ){
		return @$_GET[$val] ? trim( $_GET[$val] ) : $default;
	}
	static function post( $val, $default = false ){
		return @$_POST[$val] ? trim( $_POST[$val] ) : $default;
	}
	static function in( $val, $default = false ){
		$r = http::get( $val, $default );
		if( $r ) return $r;
		return http::post( $val, $default );
	}
	static function ck(){		
		$arguments = func_get_args();
		$i = func_num_args();
		$key = $arguments[0];
		if( $i == 1 ){
			$t0 = @$_COOKIE[$key];
			if( $t0 ){
				if( gettype( $t0 ) == 'integer' ){
					return (int)$t0;
				}else{
					return $t0 .'';
				}
			}else{
				return null;
			}
		}else if( $arguments[1] === null ){
			self::ck( $key, '' );
			//unset( $_COOKIE[$key] );
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
		if( count( $t0 ) == 2 ){
			$to = "=?". $charset ."?B?".base64_encode( trim( $t0[0] ) ) ."?= <". trim( $t0[1] ) .">" ;
		}		
		$t0 = explode( ',', $from );
		if( count( $t0 ) == 2 ){
			$from = "=?". $charset ."?B?".base64_encode( trim( $t0[0] ) )."?= <". trim( $t0[1] ) .">";
		}
		$headers =
			"From: ". $from ."\r\n". "Reply-To: ". $from ."\r\n".
			"Content-type: text/html; charset=". $charset ."\r\n".
			"Content-Transfer-Encoding: 8bit\r\n";
		if( $cc ) $headers .= "cc: ". $cc ."\r\n";
		if( $bcc ) $headers .= "bcc: ". $bcc;
		return mail( $to, $encoded_subject, $contents, $headers );
	}
	static private $upath = '';
	static private $path = '';
	static function up(){
		$arguments = func_get_args();
		$i = func_num_args() ;
		$fld = $i < 1 ? 'upfile' : $arguments[0];
		$path = $i < 2 ? NULL : $arguments[1];
		$type = $i < 3 ? 'img' : $arguments[2];
		$max = $i < 4 ? NULL : $arguments[3];
		
		$fname = self::uCheck_( $fld, $path, $type, $max );
		if( $fname && self::uMove_( $fld, $fname ) ){
			return self::$path . $fname;
		}else{
			return false;
		}
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
	
	static private function  uCheck_(){
		$arguments = func_get_args();
		
		$files = $_FILES[$arguments[0]];
		self::uPath_( $arguments[1] );
		
		//파일체크
		if( !is_uploaded_file( $files['tmp_name'] ) ){
			return false;
			echo( '업로드된 파일이 아닙니다' );
		}
		
		//이름변환
		$t0 = explode( '.', $files['name'] );
		$name = date( 'Ymd_his_' ) . self::ip();
		$ext = strtolower( $t0[1] );
		$path = $name .'.'. $ext;		
		
		//확장자필터링
		$t0 = $arguments[2];
		if( $t0 == 'all' ){
			$t1 = array( 'doc', 'docx', 'ppt', 'pptx', 'pdf', 'hwp', 'zip', 'jpg', 'gif', 'png' );
		}else if( $t0 == 'img' ){
			$t1 = array( 'jpg', 'gif', 'png' );
		}else{
			$t1 = explode( ',', $t0 );
		}
		$t2 = false;
		for( $i = 0, $j = count( $t1 ) ; $i < $j ; $i++ ){
			if( $ext == $t1[$i] ){
				$t2 = true;
				break;
			}
		}
		if( $t2 == false ){
			return false;
			echo( '업로드 불가능한 확장자입니다.' );
		}
		
		//사이즈필터링
		$t0 = $arguments[3];
		if( $t0 && $t0 < $files['size'] ){
			return false;
			echo( '사이즈가 초과되었습니다' );
		}		
		
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
		}else{
			$r = $path;
		}
		return $r;
	}
	static private function uPath_( $path ){
		if( $path == NULL ) $path = 'up/';
		self::$path = '/'. $path;
		self::$upath = self::root() . $path;
	}
	static private function uMove_( $val, $name ){
		if( !move_uploaded_file( $_FILES[$val]['tmp_name'], self::$upath.$name ) ){
			return false;
			echo( $_FILES[$val]['tmp_name'] .' : 복사중 에러발생 : '. self::$upath.$name );
		}else{
			return true;
		}
	}	
}
?>