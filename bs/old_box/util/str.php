<?php
class str{
	static function plus(){
		$arguments = func_get_args();
		$t0 = '';
		$i = 0;
		$j = func_num_args() ;
		while( $i < $j ) $t0 .= $arguments[$i++];
		return $t0;
	}
	static function at( $val, $arr ){
		switch( gettype( $arr ) ){
		case'string':
			$arr = bs( '{'.$arr.'}' );
		case'array':
			if( gettype( $arr[0] ) == 'array' ){
				$r = '';
				for( $i = 0, $j = count( $arr ) ; $i < $j ; $i++ ) $r .= str_replace( '$i', $i + 1, self::template_( $val, count( $arr[$i] ) - 1, -1, $arr[$i] ) );
				return $r;
			}else{
				return self::template_( $val, count( $arr ) - 1, -1, $arr );
			}
		default:
			return self::template_( $val, func_num_args() - 1, 0, func_get_args() );
		}
	}
	static private function template_( $val, $i, $j, $arr ){
		for( ; $i > $j ; --$i ){
			$t0 = $arr[$i];
			if( $t0 && $t0[0] == '@' ) $t0 = "'". substr( $t0, 1 ) ."'";
			$val = str_replace( '@@'.$i, $t0, $val );
		}
		return $val;
	}
	static function split( $val, $sep ){
		return explode( $sep, $val );
	}
	static function replace( $val, $search, $replace = '' ){
		return str_replace( $search, $replace, $val );
	}
	static function stripBr( $val ){
		return preg_replace( '/[\s]*[\s]/', ' ', preg_replace( '/(<br \/\>)|(<br>)/', ' ', $val ) );
	}
	static function lineBr( $val ){
		return preg_replace( '/[\n]/', '<br>', $val );
	}
	
	static function bsval( $val ){
		if( $val[0] == '<' && substr( $val, -1 ) == '>' ){
			$val = explode( ' ', substr( $val, 1, -1 ) );
			$t0 = $val[0] . ',' . $val[1];
			for( $i = 2, $j = count( $val ) ; $i < $j ; $i++ ) $t0 .= '.'.$val[$i];
			return bs( $t0 );
		}else{
			return $val;
		}
	}
	
	static private $SALT = 'Time-Spot)(*7';
	static function en( $val ){
		return trim(
			base64_encode(
				mcrypt_encrypt(
					MCRYPT_RIJNDAEL_256, self::$SALT, $val, MCRYPT_MODE_ECB,
					mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB),MCRYPT_RAND)
				)
			)
		);
	}
	static function de( $val ){ 
		return trim(
			mcrypt_decrypt(
				MCRYPT_RIJNDAEL_256, self::$SALT, base64_decode( $val ), MCRYPT_MODE_ECB,
				mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND)
			)
		);
	}
	static function isnum( $data ){
		for( $i = 0, $j = strlen( $data ) ; $i < $j ; $i++ ){
			$t0 = ord( $data[$i] );
			if( $t0 < 48 || $t0 > 57 ) return false;
		}
		return true;
	}
	static function xml( $data ){
		if( $data[0] == '@' ) $data = core::fr( substr( $data, 1 ) );
		return simplexml_load_string( $data );
	}
	static function xmlget( $xml, $val ){
		$val = explode( '.', $val );
		for( $i = 0, $j = count( $val ) ; $i < $j ; $i++ ){
			$t0 = $val[$i];
			if( $t0[0] == '@'){
				$xml = $xml[substr( $t0, 1 )];
			}else if( str::isnum( $t0 ) ){
				$xml = $xml[(int)$t0];
			}else{
				$xml = $xml->{$t0};
			}
		}
		return $xml;
	}
	static function timelag( $data ){
		$ct = date("d M Y H:i:s", time() );
		$r = strtotime($ct) - ( strtotime( $data ) + 9*60*60 );
		if( (int)($r/86400) == 0 ){
			if( (int)($r/3600) == 0 ){
				$r = $r%3600;
				$r = (int)($r/60);
				$r = array( 'm' => $r );
			}else{
				$r = (int)($r/3600);
				$r = array( 'h' => $r );
			}	
		}else{
			$r = (int)($r/86400);
			$r = array( 'd' => $r );
		}
		//print_r( $r );
		//echo( '<Br>' );
		return $r;
	}
	static function datediff( $date, $tdate, $sep ){
		$t0 = explode( $sep, $date );
		$t1 = explode( $sep, $tdate );
		$t0 = mktime( 0, 0, 0, $t0[1], $t0[2], $t0[0] );
		$t1 = mktime( 0, 0, 0, $t1[1], $t1[2], $t1[0] );
		
		$r = ($t0 - $t1)/86400;
		return $r;
	}
}
?>