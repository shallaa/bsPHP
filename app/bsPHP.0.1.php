<?php
/* bsPHP v0.1
 * Copyright (c) 2013 by ProjectBS Committe and contributors. 
 * http://www.bsplugin.com All rights reserved.
 * Licensed under the BSD license. See http://opensource.org/licenses/BSD-3-Clause
 */
define( 'ROOT', realpath('').'/' );
define( 'APP', ROOT.'app/' );
//info
define( 'EXT', '.php' );
define( 'CONTROLLER_CLASS', 'Controller' );
define( 'DEFAULT_CONTROLLER', 'index'.EXT );
define( 'DEFAULT_METHOD', 'index' );
//path
define( 'DB', APP.'db/' );
define( 'SITE', APP.'sites/'.ID.'/' );
define( 'CONFIG', SITE.'config'.EXT );
define( 'CONTROLLER', SITE.'controller/' );
define( 'VIEW', SITE.'view/' );

//application
define( 'APPLICATION', 'local' );
define( 'APPLICATION_TABLE', 'application' );
define( 'APPLICATION_MAX', 20000 );
define( 'APPLICATION_NEW', 'CREATE TABLE IF NOT EXISTS '.APPLICATION_TABLE.'(k varchar(255)NOT NULL,v varchar('.APPLICATION_MAX.')NOT NULL,PRIMARY KEY(k))ENGINE=MEMORY DEFAULT CHARSET=utf8' );
define( 'APPLICATION_GET', "select v from ".APPLICATION_TABLE." where k='@k@'" );
define( 'APPLICATION_SET', "insert into ".APPLICATION_TABLE."(k,v)values('@k@','@v@')on duplicate key update v='@v@'" );
define( 'APPLICATION_DEL', "delete from ".APPLICATION_TABLE." where k='@k@'" );

class bs{
	static private $controller;
	static function route(){
		if( !isset($_SERVER['REQUEST_URI']) || !isset($_SERVER['SCRIPT_NAME']) ) return;
		$uri = $_SERVER['REQUEST_URI'];
		$script = $_SERVER['SCRIPT_NAME'];
		$uri = substr( $uri, strlen( strpos( $uri, $script ) === 0 ? $script : dirname($script) ) );
		if( strncmp( $uri, '?/', 2 ) === 0 ) $uri = substr( $uri, 2 );
		$i = strpos( $uri, '?' );
		if( $i !== FALSE ) $uri = substr( $uri, 0, $i );
		$i = strpos( $uri, '&' );
		if( $i !== FALSE ) $uri = substr( $uri, 0, $i );
		$parts = preg_split( '#\?#i', $uri, 2 );
		$uri = $parts[0];
		if( isset($parts[1]) ){
			$_SERVER['QUERY_STRING'] = $parts[1];
			parse_str( $_SERVER['QUERY_STRING'], $_GET );
		}else{
			$_SERVER['QUERY_STRING'] = '';
			$_GET = array();
		}
		$method = DEFAULT_METHOD;
		if( $uri == '/' || empty($uri) ){
			if( file_exists(CONTROLLER.DEFAULT_CONTROLLER) ){
				require_once CONTROLLER.DEFAULT_CONTROLLER;
				$uri = array();
			}
		}else{
			$uri = explode( '/', str_replace( array( '//', '../' ), '/', trim( parse_url( $uri, PHP_URL_PATH ), '/' ) ) );
			for( $dir = substr( CONTROLLER, 0, -1 ), $i = 0, $j = count($uri) ; $i < $j && is_dir( $dir.'/'.$uri[$i] ) ; ) $dir .= '/'.$uri[$i++];
			array_splice( $uri, 0, $i );
			if( $j - $i > 0 && file_exists( $dir.'/'.$uri[0].EXT ) ){
				require_once $dir.'/'.$uri[0].EXT;
				array_shift($uri);
				$i++;
			}else if( file_exists( $dir.'/'.DEFAULT_CONTROLLER ) ){
				require_once $dir.'/'.DEFAULT_CONTROLLER;
			}
			if( $j - $i > 0 ) $method = false;
		}
		if( class_exists(CONTROLLER_CLASS) ){
			if( file_exists(CONFIG) ) require_once CONFIG;
			$controller = CONTROLLER_CLASS;
			self::$controller = $controller = new $controller();
			if( !$method ){
				if( method_exists( $controller, $uri[0] ) ){
					$method = $uri[0];
					array_shift($uri);
				}else $method = DEFAULT_METHOD;
			}
			header('Content-Type: text/html; charset=utf-8');
			ob_start();
			self::apply( $controller, $method, $uri );
			self::dbClose();
			ob_end_flush();
		}
		else echo( '404' );
	}
	//view
	static function view( $key, $cache = TRUE ){
		eval( '?>'.self::appFile( '@BS@'.ID.'.view:'.$key, VIEW.$key.EXT, $cache ).'<?php ' );
	}
	//err
	static private $debugMode = false;
	static function debug(){
		if( func_num_args() > 0 ){
			$arg = func_get_args();
			self::$debugMode = $arg[0] ? TRUE : FALSE;
		}
		return self::$debugMode;
	}
	static function err( $code, $data = '' ){
		$t0 = $code.'::'.$data;
		if( self::$debugMode ) die($t0);
		else echo($t0);
	}
	static function end( $v = NULL ){
		self::dbClose();
		exit($v);
	}
	//data
	static private $data = array();
	static function data(){
		for( $arg = func_get_args(), $i = 0, $j = count($arg) ; $i < $j ; ){
			$k = $arg[$i++];
			if( $i < $j ){
				$v = $arg[$i++];
				if( $v === NULL ) unset(self::$data[$k]);
				else self::$data[$k] = $v;
			}else return isset(self::$data[$k]) ? self::$data[$k] : FALSE;
		}
		return $v;
	}
	//file
	static function file(){//10
		$arg = func_get_args();
		if( $arg[0] == '/' ) $arg = substr( $arg[0], 1 );
		$base = $arg[0];
		if( strpos( $base, ROOT ) === 0 ) $base = substr( $base, strlen(ROOT) );
		if( func_num_args() == 1 ){
			$path = str_replace( '//', '/', ROOT.$base );
			if( file_exists($path) ){
				$f = fopen( $path, "r" );
				if( !$f ) self::err( 10, $arg[0] );
				$t0 = '';
				while( $t1 = fread( $f, 4096 ) ) $t0 .= $t1;
			}else $t0 = FALSE;
			return $t0;
		}else{
			for( $dir = explode( '/', $base ), $file = array_pop($dir), $path = ROOT, $i = 0, $j = count($dir) ; $i < $j ; ){
				$path .= '/'.$dir[$i++];
				$path = str_replace( '//', '/', $path );
				if( !is_dir($path) ) mkdir($path);
			}
			$path = str_replace( '//', '/', $path.'/'.$file );
			if( $arg[1] === NULL ){
				if( file_exists($path) ) unlink($path );
			}else{
				$f = @fopen( $path, "w+" );
				if( !$f ) return self::err( 11, $path );
				@flock( $f, LOCK_EX );
				fwrite( $f, $arg[1] );
				@flock( $f, LOCK_UN );
				@fclose($f);
			}
		}
	}
	//http
	static function out(){
		for( $t0 = '', $i = 0, $j = func_num_args(), $arg = func_get_args() ; $i < $j ; $i++ ) $t0 .= $arg[$i];
		echo($t0);
	}
	static function in(){//20
		$j = func_num_args();
		$t0 = array();
		if( $j === 0 ){
			foreach( $_POST as $k=>$v ) $t0[$k] = trim($_POST[$k]);
		}else{
			for( $arg = func_get_args(), $i = 0 ; $i < $j ; ){
				$k = $arg[$i++];
				$type = strtolower($arg[$i++]);
				if( isset($_POST[$k]) ){
					$v = trim($_POST[$k]);
					switch( $type[0] ){
					case's':$v = (string)$v; break;
					case'i':$v = (int)$v; break;
					case'f':$v = (float)$v; break;
					case'b':$v = (boolean)$v; break;
					default:self:err( 20, $k );
					}
					$t0[$k] = $v;
				}else self::err( 20, $k );
			}
		}
		return $t0;
	}
	static private $curlBase = NULL;
	static private $curlKey = NULL;
	static private function curl( $url ){
		if( self::$curlBase === NULL ){
			self::$curlBase = array( CURLOPT_HEADER, FALSE, CURLOPT_RETURNTRANSFER, TRUE, CURLOPT_SSL_VERIFYPEER, FALSE, CURLOPT_SSL_VERIFYHOST, 2 );
			self::$curlKey = array( 'post'=>CURLOPT_POSTFIELDS, 'header'=>CURLOPT_HTTPHEADER, 'cookie'=>CURLOPT_COOKIE, 'method'=>CURLOPT_CUSTOMREQUEST );
		}
		$header = array();
		for( $curl = curl_init($url), $arg = self::$curlBase, $i = 0, $j = count($arg) ; $i < $j ; ) curl_setopt( $curl, $arg[$i++], $arg[$i++] );
		for( $arg = func_get_args(), $i = 1, $j = func_num_args() ; $i < $j ; ){
			$k = $arg[$i++];
			$v = $arg[$i++];
			if( $k[0] == '@' ) array_push( $header, substr( $k, 1 ).': '.$v );
			else{
				if( $k == 'post' ) curl_setopt( $curl, CURLOPT_POST, TRUE );
				if( isset(self::$curlKey[$k]) ) $k = self::$curlKey[$k];
				if( is_array($v) ){
					for( $v0 = array(), $m = 1, $n = count($v) ; $m < $n ; ) array_push( $v0, self::encode($v[$m++]).'='.self::encode($v[$m++]) );
					$v = implode( '&', $v0 );
				}
				curl_setopt( $curl, $k, $v );
			}
		}
		if( count($header) > 0 ) curl_setopt( $curl, CURLOPT_HTTPHEADER, $header );
		$t1 = curl_exec($curl);
		curl_close($curl);
		return $t1 === FALSE ? curl_error($curl) : $t1;
	}
	static function get($url){
		$j = func_num_args();
		if( $j > 1 ){
			$url = explode( '#', $url );
			$url[0] .= strpos( $url[0], '?' ) !== FALSE ? '&' : '?';
			for( $arg = func_get_args(), $i = 0 ; $i < $j ; ) $url[0] .= encode($arg[$i++]).'='.encode($arg[$i++]).($i < $j - 1 ? '&' : '');
			$url = implode( '#', $url );
		}
		return self::curl($url);
	}
	static function post($url){return self::curl( $url, 'post', func_get_args() );}
	static function delete($url){return self::curl( $url, 'post', func_get_args(), 'method', 'DELETE' );}
	static function put($url){return self::curl( $url, 'post', func_get_args(), 'method', 'PUT' );}
	static function ck(){
		$arg = func_get_args();
		$k = isset($arg[0]) ? $arg[0] === NULL ? NULL : trim($arg[0]) : NULL;
		$v = isset($arg[1]) ? $arg[1] === NULL ? NULL : strlen( $v = trim($arg[1]) ) === 0 ? NULL : $v : NULL;
		if( $k !== NULL && $v == NULL ){
			setcookie( $arg[0], '', time() - 3600, '/' );
		}else{
			setcookie( $arg[0], trim($arg[1]), isset($arg[2]) ? time() + 86400 * $arg[2] : 0, isset($arg[3]) ? $arg[3] : '/' );
		}
	}
	static function ckGet(){
		for( $t0 = array(), $arg = func_get_args(), $i = 0, $j = func_num_args() ; $i < $j ; ){
			$k = $arg[$i++];
			$t0[$k] = isset($_COOKIE[$k]) ? trim($_COOKIE[$k]) : NULL;
		}
		return $t0;
	}
	static function session( $key ){
		if( $key === NULL ) session_destroy();
		else{
			if( !isset($_SESSION) ) session_start();
			for( $arg = func_get_args(), $i = 0, $j = func_num_args() ; $i < $j ; ){
				$k = $arg[$i++];
				if( $i == $j ) return isset($_SESSION[$k]) ? $_SESSION[$k] : FALSE;
				else{
					$v = $arg[$i++];
					if( $v === null ) unset($_SESSION[$k]);
					else $_SESSION[$k] = $v;
				}
			}
			return $v;
		}
	}
	//util
	static function apply( $context, $method, $arg = NULL ){
		if( !is_array($arg) ){
			$arg = func_get_args();
			if( count($arg) > 2 && $arg !== NULL ) array_splice( $arg, 0, 2 );
			else $arg = array();
		}
		return call_user_func_array( $context ? array( $context, $method ) : $method, $arg );
	}
	static function hash($v){return hash('sha512', 'bs_'.$v.'_sha512' );}
	static private $encryptSALT = NULL;
	static private function encryptSalt(){
		if( self::$encryptSALT === NULL ) self::$encryptSALT = self::file(APP.'bsPHP.salt');
		return self::$encryptSALT;
	}
	static function encrypt( $v ){
		return trim( base64_encode( mcrypt_encrypt(
			MCRYPT_RIJNDAEL_256, self::encryptSalt(), $v, MCRYPT_MODE_ECB,
			mcrypt_create_iv( mcrypt_get_iv_size (MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB ), MCRYPT_RAND )
		) ) );
	}
	static function decrypt( $v ){
		return trim( mcrypt_decrypt(
			MCRYPT_RIJNDAEL_256, self::encryptSalt(), base64_decode($v), MCRYPT_MODE_ECB,
			mcrypt_create_iv( mcrypt_get_iv_size( MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB ), MCRYPT_RAND )
		) );
	}
	static function uuid(){return md5(com_create_guid());}
	static function encode($v){return urlencode(trim($v));}
	static function decode($v){return urldecode(trim($v));}
	static function json( $v, $isDecode = TRUE ){return $isDecode ? json_decode( $v, true ) : json_encode( $v, 256 );}
	static function jsonEncode( $v ){return json_encode( $v, 256 );}
	static private $xmlCache = array();
	static function xml( $xml, $key = FALSE ){
		if( isset(self::$xmlCache[$xml]) ) $t0 = self::$xmlCache[$xml];
		else{
			$t0 = array();
			foreach( simplexml_load_string($xml) as $k=>$v ){
				if( !isset($t0[$k]) ) $t0[$k] = array();
				array_push( $t0[$k], self::xmlParse($v) );
			}
			foreach( $t0 as $k=>$v ) if( count($v) == 1 ) $t0[$k] = $v[0];
			self::$xmlCache[$xml] = $t0;
		}
		if( $key ){
			$key = explode( '.', $key );
			$data = $t0[$key[0]];
			for( $i = 1, $j = count($key) ; $i < $j ; $i++ ){
				$k = $key[$i];
				$data = $data[$k];
			}
			return $k[0] == '@' || $k == 'value' ? $data : $data['value'];
		}else return $t0;
	}
	static private function xmlParse( $node ){
		$out = array();
		$attr = $node->attributes();
		if( count($attr) > 0 ) foreach( $attr as $k=>$v ) $out['@'.$k] = trim($v);
		if( $node->count() ) foreach( $node as $k=>$v ) $out[$k] = self::xmlParse($v);
		else $out['value'] = ''.trim($node);
		return $out;
	}
	static function script($v){echo('<script>'.$v.'</script>');}
	static function go( $url, $target = FALSE ){self::script( ( $target ? $target.'.' : '' )."location.href='".$url."';" );}
	static function reload( $target = FALSE ){self::script( ( $target ? $target.'.' : '' ).".location.reload();" );}
	static function back(){self::script("history.back();");}
	static function alert($v){self::script("alert('".$v."');");}
	
	static function db2html($v){return  preg_replace( '/\n|\r\n|\r/', '<br/>', str_replace( '<', '&lt;', $val ) );}
	static function isnum($v){return preg_match( '/^[0-9.]+$/', $v );}
	
	static function rand( $v0, $v1 ){return mt_rand( $v0, $v1 );}
	
	static function limit( $page, $rpp ){return ( $page - 1 ) * $rpp;}
	static function tp( $total, $rpp ){return (int)( ( $total - 1 ) / $rpp ) + 1;}
	
	static private $serverKey = NULL;
	static function server($k){
		if( self::$serverKey === NULL ) self::$serverKey = array(
			'domain'=>'HTTP_HOST', 'ip'=>'REMOTE_ADDR', 'url'=>'SCRIPT_NAME'
		);
		return $_SERVER[isset(self::$serverKey[$k]) ? self::$serverKey[$k] : $k];
	}
	static function mail( $from, $to, $subject, $contents, $cc = NULL, $bcc = NULL ){
		$charset = 'utf-8';
		$encoded_subject = "=?".$charset."?B?".base64_encode($subject) ."?=";
		$t0 = explode( ',', $to );
		if( count($t0) == 2 ) $to = "=?".$charset."?B?".base64_encode(trim($t0[0]))."?= <".trim($t0[1]).">" ;
		$t0 = explode( ',', $from );
		if( count($t0) == 2 ) $from = "=?".$charset."?B?".base64_encode(trim($t0[0]))."?= <".trim($t0[1]).">";
		$headers = array(
			"From: ".$from,
			"Reply-To: ".$from,
			"Content-type: text/html; charset=".$charset,
			"Content-Transfer-Encoding: 8bit"
		);
		if( $cc ) array_push( $headers, "cc: ".$cc );
		if( $bcc ) array_push( $headers, "bcc: ".$bcc );
		return mail( $to, $encoded_subject, $contents, implode( "\r\n", $headers ) );
	}
	//date
	static private $dateKey = array( 'aam'=>'오전', 'apm'=>'오후', 'w0'=>'일', 'w1'=>'월', 'w2'=>'화', 'w3'=>'수', 'w4'=>'목', 'w5'=>'금', 'w6'=>'토' );
	static function datePart( $part, $date = null ){
		$time = self::dateGet( $date );
		if( strpos( $part, 'w' ) ) $part = str_replace( 'w', self::$dateKey['w'.date( 'w', $time )], $part );
		else if( strpos( $part, 'a' ) ) $part = str_replace( 'a', self::$dateKey['a'.date( 'a', $time )], $part );
		return date( $part, $time );
	}
	static function dateAdd( $interval, $number, $date = null, $part = 'Y-m-d H:i:s' ){
		$time = self::dateGet($date);
		switch( strtolower($interval) ){
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
	static private function mktime( $y, $m, $d, $h = 0, $i = 0, $s = 0 ){return mktime( $h, $i, $s, $m, $d, $y );}
	static private function leapYear( $year ){return ( $year % 4 == 0 && $year % 100 != 0 ) || $year % 400 == 0;}
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
	//application
	static private $applicationConn = NULL;
	static private function applicationConn(){
		if( !APPLICATION ) return FALSE;
		if( !self::$applicationConn ){
			$info = self::json(self::file(DB.APPLICATION.'/db.json'));
			$conn = mysql_connect( $info['url'], $info['id'], $info['pw'] );
			if( !$conn ) return FALSE;
			self::$applicationConn = $conn;
			mysql_select_db( $info['db'], $conn );
			mysql_query( APPLICATION_NEW, $conn );
		}
		 return self::$applicationConn;
	}
	static function application($key){
		$conn = self::applicationConn();
		if( !$conn ) return self::err( 1000, '' );
		$j = func_num_args();
		$key = mysql_real_escape_string($key);
		if( $j == 1 ){
			$rs = mysql_query( str_replace( '@k@', $key, APPLICATION_GET ), $conn );
			if( mysql_num_rows($rs) == 0 ) return FALSE;
			$row = mysql_fetch_row($rs);
			return $row[0];
		}else{
			$arg = func_get_args();
			$v = mysql_real_escape_string($arg[1]);
			if( APPLICATION_MAX < strlen($v) ) return FALSE;
			return mysql_query( 
				$arg[1] === NULL ? str_replace( '@k@', $key, APPLICATION_DEL ) : str_replace( '@v@', $v, str_replace( '@k@', $key, APPLICATION_SET ) ), 
				$conn 
			);
		}
	}
	static function appFile( $key, $file, $cache = TRUE ){
		if( $cache ){
			$data = FALSE;
			if( APPLICATION !== FALSE ) $data = self::application($key);
			if( !$data ){
				$data = self::file($file);
				if( $data === FALSE ) return FALSE;
				if( APPLICATION !== FALSE ) self::application( $key, $data );
			}
		}else{
			if( APPLICATION ) self::application( $key, NULL );
			$data = self::file($file);
		}
		return $data;
	}
	//db
	static private $db = array();
	static private $dbCurr = NULL;
	static private function dbOpen(){
		$d = &self::$db[self::$dbCurr];
		if( !isset($d['conn']) || !$d['conn'] ){
			$d['conn'] = mysql_connect( $d['url'], $d['id'], $d['pw'] );
			$encoding = $d['encoding'];
			mysql_select_db( $d['db'], $d['conn'] );
			mysql_query('set session character_set_connection='.$encoding.';');
			mysql_query('set session character_set_results='.$encoding.';');
			mysql_query('set session character_set_client='.$encoding.';');
		}
		return $d['conn'];
	}
	static private function dbClose(){
		foreach( self::$db as $k=>$v ){
			if( isset($v['conn']) &&  $v['conn'] !== FALSE ){
				mysql_close($v['conn']);
				$v['conn'] = FALSE;
			}
		}
	}
	static function db($key, $cache = TRUE ){
		if( !isset(self::$db[$key]) ) self::$db[$key] = self::json(self::appFile( '@BS@'.self::$dbCurr.'.db:'.$key, DB.$key.'/db.json', $cache ));
		self::$dbCurr = $key;
	}
	static function dbSync( $master, $slaves, $tables = NULL ){
		if( !is_array($slaves) ) $slaves = array($slaves);
		if( !$tables ){
			$tables = array();
			self::db($master);
			$rs = mysql_query( 'show tables', self::dbOpen() );
			while( $row = mysql_fetch_array($rs) ) array_push( $tables, $row[0] );
		}else if( !is_array($tables) ) $tables = array($tables);
		for( $i = 0, $j = count($tables) ; $i < $j ; $i++ ){
			self::db($master);
			$query = 'checksum table '.$tables[$i];
			$rs = mysql_query( $query, self::dbOpen() );
			$row = mysql_fetch_array($rs);
			$hash = $row[1];
			for( $k = 0, $l = count($slaves) ; $k < $l ; $k++ ){
				self::db($slaves[$k]);
				$rs = mysql_query( $query, self::dbOpen() );
				$row = mysql_fetch_array($rs);
				if( $hash != $row[1] ) return FALSE;
			}
		}
		return TRUE;
	}
	//sql
	static private $sqlJSON = array();
	static private $sql = array( '@INFO'=>array( 'SHOW FULL COLUMNS FROM @table@', 0, 'object' ) );
	static private $sqlInfo = array( '@INFO'=>array( 'table'=>array( FALSE, FALSE, FALSE, FALSE, NULL ) ) );
	static private $sqlKey;
	static private $sqlTable = array();
	static $tableInfo = array();
	static private function sqlTable( $table ){
		if( !isset(self::$sqlTable[$table]) ){
			$info = self::appFile( $appKey = '@BS@'.self::$dbCurr.'.table:'.$table, $path = DB.self::$dbCurr.'/table/'.$table.'.json' );
			if( $info === FALSE ){
				$rs = self::query( '@INFO', array( 'table'=>$table ) );
				$info = array();
				for( $i = 0, $j = count($rs) ; $i < $j ; $i++ ){
					$row = $rs[$i];
					$type = strtolower($row->Type);
					$isStr = FALSE;
					$validation = '';
					$comment = preg_replace( '/\/[*](.+)[*]\//', '$1', $row->Comment );
					if( $comment == '/**/' ) $comment = '';
					if( strpos( $type, 'char' ) !== FALSE ){
						$isStr = TRUE;
						if( strpos( $type, 'max_length' ) === FALSE ) $validation .= 'max_length['.substr( $type, strpos( $type, '(' ) + 1, -1 ).']';
					}else if( strpos( $type, 'text' ) !== FALSE || strpos( $type, 'blob' ) !== FALSE || strpos( $type, 'binary' ) !== FALSE || strpos( $type, 'enum' ) !== FALSE || strpos( $type, 'set' ) !== FALSE ){
						$isStr = TRUE;
					}else if( strpos( $type, 'int' ) !== FALSE || strpos( $type, 'timestamp' ) !== FALSE || strpos( $type, 'year' ) !== FALSE ){
						if( strpos( $type, 'integer' ) === FALSE ) $validation .= 'integer';
					}else if( strpos( $type, 'decimal' ) !== FALSE || strpos( $type, 'float' ) !== FALSE || strpos( $type, 'double' ) !== FALSE || strpos( $type, 'real' ) !== FALSE ){
						if( strpos( $type, 'decimal' ) === FALSE ) $validation .= 'decimal';
					}
					$info[$row->Field] = array(
						$validation.( $validation !== '' && $comment !== '' ? '|' : '' ).$comment,
						$isStr,
						strtolower($row->Extra) === 'auto_increment' ? TRUE : FALSE,
						strtolower($row->Null) === 'yes' ? TRUE : FALSE,
						$isStr ? $row->Default : intval($row->Default, 10)
					);
				}
				self::file( $path, json_encode( $info, 256 ) );
				if( APPLICATION ) self::application( $appKey, $info );
			}else $info = json_decode( $info, true );
			self::$sqlTable[$table] = $info;
		}
		return self::$sqlTable[$table];
	}
	static function sqlParse($str){
		if( strpos( $str[0], ':' ) === FALSE ) return $str;
		$str = explode( ':', $str[0] );
		$meta = explode( '.', $str[1] );
		$vali = self::sqlTable($meta[0]);
		self::$sqlInfo[self::$sqlKey][substr( $str[0], 1 )] = $vali[substr( $meta[1], 0, -1 )];
		return $str[0].'@';
	}
	static private function sqlAdd( $key ){//4000
		if( !isset(self::$sqlJSON[$key]) ) return FALSE;
		$query = self::$sqlJSON[$key];
		if( $query[0] == '@' ){
			$i = strpos( $query, '@', 1 );
			$type = trim(substr( $query, 1, $i - 1 ));
			$query = trim(substr( $query, $i + 1 ));
		}else $type = 'object';
		if( !isset(self::$sqlInfo[$key]) ) self::$sqlInfo[$key] = array();
		self::$sqlKey = $key;
		self::$sql[$key] = array( trim(preg_replace_callback( '/@[^@]+@/', 'bs::sqlParse', $query )), preg_match( '/^(insert|update|delete|truncate)/', $query ), $type );
		return TRUE;
	}
	static function sql( $key, $cache = TRUE ){
		foreach( self::json(self::appFile( '@BS@'.self::$dbCurr.'.sql:'.$key, DB.self::$dbCurr.'/sql/'.$key.'.json', $cache )) as $k=>$v ) self::$sqlJSON[trim($k)] = trim($v);
	}
	//query
	static $queryError = NULL;
	static $queryCount = 0;
	static $queryInsertID = 0;
	static function queryBegin(){
		mysql_query( 'set autocommit=0', self::dbOpen() );
		mysql_query( 'begin', self::dbOpen() );
	}
	static function queryCommit(){mysql_query( 'commit', self::dbOpen() );}
	static function queryRollback(){mysql_query( 'rollback', self::dbOpen() );}
	static function query( $key, $data = NULL ){//5000
		$j = func_num_args();
		if( $j > 2 ){
			self::queryBegin();
			for( $arg = func_get_args(), $i = 0 ; $i < $j ; $i += 2 ){
				if( !self::query( $arg[$i], $arg[$i + 1] ) ){
					self::queryRollback();
					return FALSE;
				}
			}
			self::queryCommit();
			return TRUE;
		}
		
		if( !isset(self::$sql[$key]) && !self::sqlAdd($key) ) return self::err( 5000, $key );
		$query = self::$sql[$key][0];
		$isDML = self::$sql[$key][1];
		if( $data === NULL ) $data = $_POST;
		if( count(self::$sqlInfo[$key]) > 0 ){
			if( count($data) == 0 ){
				self::$queryError = 'NoData:'.$k;
				return FALSE;
			}
			$validation = array();
			foreach( self::$sqlInfo[$key] as $k=>$info ){
				if( !isset($data[$k]) ){
					self::$queryError = 'NoData:'.$k;
					return FALSE;
				}
				$v = mysql_real_escape_string($data[$k]);
				if( $info[0] ){
					$v = self::vali( $v, $info[0], $data );
					if( $v === self::$valiFail ){
						self::$queryError = 'VALI:'.self::$valiError;
						return FALSE;
					}
				}
				if( $info[1] === TRUE ) $v = "'".str_replace( "'", "''", $v )."'";
				$query = str_replace( '@'.$k.'@', $v, $query );
			}
		}
		$rs = mysql_query( $query, self::dbOpen() );
		if( $rs === TRUE ){
			self::$queryCount = mysql_affected_rows();
			self::$queryInsertID = strpos( strtolower(substr( $query, 0, 6 )), 'insert' ) !== FALSE ? mysql_insert_id() : 0;
			return TRUE;
		}else if( $rs === FALSE ){
			self::$queryError = 'ERR:'.mysql_error();
			return FALSE;
		}else{
			self::$queryCount = $count = mysql_num_rows($rs);
			if( $count === 0 ){
				self::$queryError = 'NoRecord';
				return FALSE;
			}
			$type = self::$sql[$key][2];
			switch( $type ){
			case'object':
				$r = array();
				while( $row = mysql_fetch_object($rs) ) array_push( $r, $row );
				return $r;
			case'array':
				$r = array();
				while( $row = mysql_fetch_row($rs) ) array_push( $r, $row );
				return $r;
			case'raw':return $rs;
			case'recordObject':return mysql_fetch_object($rs);
			case'record':case'recordArray':return mysql_fetch_row($rs);
			default:
				if( $type[0] == '[' ){
					$r = substr( $type, 1, -1 );
					if( self::isnum($r) ){
						$row = mysql_fetch_row($rs);
						$r = $row[$r];
					}else{
						$row = mysql_fetch_object($rs);
						$r = $row->{$r};
					}
					return $r;
				}
				return self::err( 5001, $type );
			}
		}
	}
	//validation
	static $valiError = NULL;
	static public $valiFail = array();
	static private $valiRex = array(
		'valid_email'=>"/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix",
		'alpha'=>"/^([a-z0-9])+$/i",
		'alpha_numeric'=>"/^([-a-z0-9_-])+$/i",
		'alpha_dash'=>"/^([-a-z0-9_-])+$/i",
		'numeric'=>'/^[\-+]?[0-9]*\.?[0-9]+$/',
		'integer'=>'/^[\-+]?[0-9]+$/',
		'decimal'=>'/^[\-+]?[0-9]+\.[0-9]+$/',
		'is_natural'=>'/^[0-9]+$/',
		'valid_base64'=>'/[^a-zA-Z0-9\/\+=]/'
	);
	static private $valiReplace = array(
		'prep_for_form'=>array( array( "'", '"', '<', '>' ), array( "&#39;", "&quot;", '&lt;', '&gt;' ) ),
		'xss_clean'=>array( array('<'), array('&lt;') ),
		'encode_php_tags'=>array( array( '<?php', '<?PHP', '<?', '?>' ),  array( '&lt;?php', '&lt;?PHP', '&lt;?', '?&gt;' ) )
	);
	static function vali( $v, $vali, $data ){
		$fail = self::$valiFail;
		for( $vali = explode( '|', $vali ), $i = 0, $j = count($vali) ; $i < $j ; $i++ ){
			$f = $vali[$i];
			self::$valiError = $f.'::'.$v;
			$k = strpos( $f, '[' );
			if( $k !== FALSE ){
				$arg = explode( ',', substr( $f, $k + 1, -1 ) );
				$f = substr( $f, 0, $k );
			}else $arg = FALSE;
			switch( $f ){
			case'required':
				if( empty($v) ) return $fail;
				break;
			case'regex_match':
				if( !$arg || !$arg[0] || !preg_match( $arg[0], $v ) ) return $fail;
				break;
			case'matches':
				if( !isset($data[$arg[0]]) || $data[$arg[0]] != $v ) return $fail;
				break;
			case'valid_email':case'alpha':case'alpha_numeric':case'alpha_dash':case'numeric':case'is_natural':case'decimal':case'valid_base64':
				if( !preg_match( self::$valiRex[$f], $v ) ) return $fail;
				break;
			case'is_numeric':
				if( !preg_match( self::$valiRex['numeric'], $v ) ) return $fail;
				break;
			case'is_natural_no_zero':
				if( $v == 0 || !preg_match( self::$valiRex['is_natural'], $v ) ) return $fail;
				break;
			case'is_unique':
				if( !isset($data[$arg[0]]) ) return $fail;
				$arg = explode( '.', $arg[0] );
				$t0 = mysql_query( 'select count(*)from '.$arg[0].' where '.$arg[1].'='.( self::isnum($v) ? $v : "'".$v."'" ), self::dbOpen() );
				$t0 = mysql_fetch_row($t0);
				if( !$t0[0] ) return $fail;
				break;
			case'exact_length':case'max_length':case'min_length':
				$arg = (int)$arg[0];
				$t0 = function_exists('mb_strlen') ? mb_strlen($v) : strlen($v);
				if( $f == 'exact_length' ? $t0 != $arg : $f == 'max_length' ? $t0 >= $arg : $t0 <= $arg ) return $fail;
				break;
			case'valid_emails':
				foreach( explode(',', $v ) as $t0 ) if( !preg_match(self::$valiRex['valid_email'], $v ) ) return $fail;
				break;
			case'valid_ip':
				$t0 = strpos( $v, ':' ) !== FALSE ? FILTER_FLAG_IPV6 : strpos($ip, '.') !== FALSE ? FILTER_FLAG_IPV4 : FALSE;
				if( !$t0 || !filter_var( $v, FILTER_VALIDATE_IP, $t0 ) ) return $fail;
				break;
			case'greater_than':case'less_than':
				if( !preg_match( self::$valiRex['numeric'], $v ) ) return $fail;
				$arg = (int)$arg[0];
				if( $f == 'greater_than' ? $v <= $arg : $v >= $arg ) return $fail;
				break;
			case'prep_for_form': $v = stripslashes($v);
			case'xss_clean':case'encode_php_tags':
				$v = str_replace( self::$valiReplace[$f][0], self::$valiReplace[$f][1], $v );
				break;
			case'prep_url':
				if( substr( $v, 0, 7 ) != 'http://' && substr( $v, 0, 8 ) != 'https://' ) $v = 'http://'.$v;
				break;
			default:
				$arg = array($v);
				if( function_exists($f) ) $v = self::apply( FALSE, $f, $arg );
				else if( method_exists( self::$controller, $f ) ) $v = self::apply( self::$controller, $f, $arg );
				else if( method_exists( 'bs', $f ) ) $v = self::apply( FALSE, 'bs::'.$f, $arg );
				if( $v === FALSE ) return $fail;
			}
		}
		return $v;
	}
	//upload
	static $uploadError = NULL;
	static private $uploadExt = array(
		'all'=>array( 'doc'=>1, 'docx'=>1, 'ppt'=>1, 'pptx'=>1, 'pdf'=>1, 'hwp'=>1, 'zip'=>1, 'jpg'=>1, 'gif'=>1, 'png'=>1, 'txt'=>1 ),
		'image'=>array( 'jpg'=>1, 'gif'=>1, 'png'=>1 )
	);
	static function upload( $field, $savePath, $type = 'image', $max = 3 ){
		$info = $_FILES[$field];
		if( $info['error'] ){
			self::$uploadError = 'Err:'.$info['error'];
			return FALSE;
		}else if( !is_uploaded_file($info['tmp_name']) ){
			self::$uploadError = 'Not uploaded:'.$field;
			return FALSE;
		}
		$i = strrpos( $info['name'], '.' );
		if( $i === FALSE ){
			self::$uploadError = 'No extention:'.$info['name'];
			return FALSE;
		}
		$ext = strtolower(substr( $info['name'], $i + 1 ));
		$i = FALSE;
		if( isset(self::$uploadExt[$type]) ){
			if( !isset(self::$uploadExt[$type][$ext]) ) $i = TRUE;
		}else{
			if( !is_array($type) ) $type = explode( ',', $type );
			if( !in_array( $ext, $type ) ) $i = TRUE;
		}
		if( $i ){
			self::$uploadError = 'Invalid file format:'.$type.'-'.$ext;
			return FALSE;
		}
		$max *= 1000000;
		if( $max < $info['size'] ){
			self::$uploadError = 'Exceeded size:'.$info['size'].'/'.$max;
			return FALSE;
		}
		$path = ROOT.( $savePath[0] == '/' ? substr( $savePath, 1 ) : $savePath );
		if( $path[strlen($path) - 1] == '/' ) $path = substr( $path, 0, -1 );
		if( !is_dir($path) ){
			self::$uploadError = 'Not exist dir:'.$path;
			return FALSE;
		}
		$file = md5(date('Ymd_his_').sha1_file($info['tmp_name']).self::rand(10000,99999)).'.'.$ext;
		if( !move_uploaded_file( $info['tmp_name'], $path.'/'.$file ) ){
			self::$uploadError = 'move fail:'.$path.'/'.$file;
			return FALSE;
		}
		return $file;
	}
}
bs::route();
?>