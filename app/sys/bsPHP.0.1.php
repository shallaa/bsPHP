<?php
class bs{
	static function route(){
		if( !isset($_SERVER['REQUEST_URI']) || !isset($_SERVER['SCRIPT_NAME']) ) return;
		$uri = $_SERVER['REQUEST_URI'];
		$script = $_SERVER['SCRIPT_NAME'];
		$uri = substr( $uri, strlen( strpos( $uri, $script ) === 0 ? $script : dirname($script) ) );
		if( strncmp( $uri, '?/', 2 ) === 0 ) $uri = substr( $uri, 2 );
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
			$controller = new $controller();
			if( !$method ){
				if( method_exists( $controller, $uri[0] ) ){
					$method = $uri[0];
					array_shift($uri);
				}else $method = DEFAULT_METHOD;
			}
			header('Content-Type: text/html; charset=utf-8');
			self::apply( $controller, $method, $uri );
			self::end();
		}
		else echo( '404' );
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
	//file
	static function file(){//10
		$arg = func_get_args();
		if( $arg[0] == '/' ) $arg = substr( $arg[0], 1 );
		if( func_num_args() == 1 ){
			$path = ROOT.$arg[0];
			if( file_exists($path) ){
				$f = fopen( $path, "r" );
				if( !$f ) self::err( 10, $arg[0] );
				$t0 = '';
				while( $t1 = fread( $f, 4096 ) ) $t0 .= $t1;
			}else $t0 = FALSE;
			return $t0;
		}else{
			for( $dir = explode( '/', $arg[0] ), $file = array_pop($dir), $path = ROOT, $i = 0, $j = count($dir) ; $i < $j ; ){
				$path .= '/'.$dir[$i++];
				if( !is_dir($path) ) mkdir($path);
			}
			$path .= '/'.$file;
			if( $arg[1] === NULL ){
				if( file_exists($path) ) unlink($path );
			}else{
				$f = @fopen( $path, "w+" );
				if( !$f ) self::err( 11, $path );
				@flock( $f, LOCK_EX );
					//fwrite( $f, pack("CCC",0xef,0xbb,0xbf) );
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
		if( self::$encryptSALT === NULL ) self::$encryptSALT = self::file(SYS.'bsPHP.salt');
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
	static function json( $v, $isDecode = FALSE ){return $isEncode ? json_decode( $v, true ) : json_encode( $v, 256 );}
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
	//db
	static private $db = array();
	static private $dbDefault = NULL;
	static private $dbCurr = NULL;
	static private function dbOpen( $key = NULL ){//30
		if( $key === NULL ){
			$key = self::$dbDefault;
			if( !isset(self::$db[$key]) ){
				self::err( 30, $key );
				return FALSE;
			}
		}
		self::$dbCurr = $key;
		$d = &self::$db[$key];
		if( !$d['conn'] ){
			$d['conn'] = mysql_connect( $d['url'], $d['id'], $d['pw'] );
			$encoding = $d['encoding'];
			mysql_query('SET NAMES euckr');
			mysql_select_db( $d['db'], $d['conn'] );
			mysql_query('set session character_set_connection='.$encoding.';');
			mysql_query('set session character_set_results='.$encoding.';');
			mysql_query('set session character_set_client='.$encoding.';');
		}
		return $d['conn'];
	}
	static private function dbClose( $key = NULL ){
		foreach( self::$db as $k=>$v ){
			if( $v['conn'] !== FALSE ){
				mysql_close($v['conn']);
				$v['conn'] = FALSE;
			}
		}
	}
	static function db( $key, $url, $id, $pw, $db, $encoding = 'utf8', $isDefault = FALSE ){
		self::$db[$key] = array( 'key'=>$key, 'conn'=>FALSE, 'url'=>$url, 'id'=>$id, 'pw'=>$pw, 'db'=>$db, 'encoding'=>$encoding );
		if( $isDefault || $isDefault === NULL ) self::$dbDefault = self::$db[$key];
	}
	//sql
	static private $sql = array('@INFO'=>'SHOW FULL COLUMNS FROM @table@');
	static private $sqlInfo = array();
	static private $sqlKey;
	static private $sqlTable = array();
	static $tableInfo = array();
	static private function sqlTable( $table, $instance ){
		if( isset(self::$sqlTable[$table]) ) return self::$sqlTable[$table];
		$path = TABLE.self::$dbCurr.'/'.$table.'.json';
		$info = self::file($path);
		if( $info === FALSE ){
			$rs = self::query( '@INFO', array( 'table'=>$table ) );
			$info = array();
			foreach( $rs as $row ){
				$type = strtolower($row['Type']);
				$isStr = FALSE;
				$validation = '';
				$comment = preg_replace( '/\/[*](.+)[*]\//', '$1', $row['Comment'] );
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
				$info[$row['Field']] = array(
					$validation.( $validation !== '' && $comment !== '' ? '|' : '' ).$comment,
					$isStr,
					strtolower($row['Extra']) === 'auto_increment' ? TRUE : FALSE,
					strtolower($row['Null']) === 'yes' ? TRUE : FALSE,
					$isStr ? $row['Default'] : intval($row['Default'], 10)
				);
			}
			self::file( $path, json_encode( $info, 256 ) );
		}else $info = json_decode( $info, true );
		self::$sqlTable[$table] = $info;
		return $info;
	}
	static function sqlParse($str){
		if( strpos( $str[0], ':' ) === FALSE ) return $str;
		$str = explode( ':', $str[0] );
		$meta = explode( '.', $str[1] );
		$vali = self::sqlTable( $meta[0], $this );
		self::$sqlInfo[self::$sqlKey][substr( $str[0], 1 )] = $vali[substr( $meta[1], 0, -1 )];
		return $str[0].'@';
	}
	static private function sqlAdd( $key, $query ){//40
		if( $query[0] == ':' ){
			$str = explode( ' ', $query );
			$table = $str[1];
			switch( $str[0] ){
			case':insert':
				$insert = array();
				$values = array();
				for( $i = 2, $j = count($str) ; $i < $j ; $i++ ){
					$token = explode( ':', $str[$i] );
					array_push( $insert, substr( $token[1], 0, -1 ) );
					array_push( $values, $token[0].':'.$table.'.'.$token[1] );
				}
				$query = 'insert into '.$table.'('.implode( ',', $insert ).')values('.implode( ',', $values ).')';
				break;
			case':update':
				$values = array();
				$where = array();
				$w = false;
				for( $i = 2, $j = count($str) ; $i < $j ; $i++ ){
					if( strtolower( $str[$i] ) == 'where' ){ $w = true; continue; }
					$token = explode( ':', $str[$i] );
					if( $w ) array_push( $where, substr( $token[1], 0, -1 ).'='.$token[0].':'.$table.'.'.$token[1] );
					else array_push( $values, substr( $token[1], 0, -1 ).'='.$token[0].':'.$table.'.'.$token[1] );
				}
				$query = 'update '.$table.' set '.implode( ',', $values ). ' where '.implode( ' and ', $where );
				break;
			case':delete':
				$where = array();
				for( $i = 3, $j = count($str) ; $i < $j ; $i++ ){
					$token = explode( ':', $str[$i] );
					array_push( $where, substr( $token[1], 0, -1 ).'='.$token[0].':'.$table.'.'.$token[1] );
				}
				$query = 'delete from '.$table.' where '.implode( ' and ', $where );
				break;
			default: return self::err( 40, $str[0] );
			}
		}
		if( strpos( $key, ':' ) === FALSE ) $type = 'object';
		else{
			$key = explode( ':', $key );
			$type = strtolower(trim($key[1]));
			$key = trim($key[0]);
		}
		if( !isset(self::$sqlInfo[$key]) ) self::$sqlInfo[$key] = array();
		self::$sqlKey = $key;
		self::$sql[$key] = array( trim(preg_replace_callback( '/@[^@]+@/', 'bs::sqlParse', $query )), $type );
	}
	static function sql( $file, $key = NULL ){
		$conn = self::dbOpen($key);
		$sql = self::file(SQL.$file);
		if( $sql !== FALSE ){
			$sql = explode( '--', $sql );
			for( $i = 1, $j = count($sql) ; $i < $j ; $i++ ){
				$k = strpos( $sql[$i], "\n" );
				if( $k === FALSE ){
					$k = strpos( $sql[$i], "\r" );
					if( $k === FALSE ) return;
				}
				self::sqlAdd( trim(substr( $sql[$i], 0, $k )), trim(substr( $sql[$i], $k + 1 )) );
			}
		}
	}
	//query
	static $queryError = NULL;
	static $queryCount = 0;
	static $queryInsertID = 0;
	static function query( $key, $data = NULL, $db = NULL ){//40
		if( !isset(self::$sql[$key]) ) return self::err( 40, $key );
		$query = self::$sql[$key][0];
		$isDML = strpos( strtolower(substr( $query, 0, 5 )), 'select' ) === FALSE;
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
				}else if( $info[1] && $isDML ){
					self::$queryError = 'AI_DML:'.$k;
					return FALSE;
				}
				$v = mysql_real_escape_string($data[$k]);
				array_push( $validation, $k, $v, $info[2] );
				if( $info[0] === TRUE ) $v = "'".str_replace( "'", "''", $v )."'";
				$query = str_replace( '@'.$k.'@', $v, $query );
			}
			if( !self::vali( $validation ) ){
				self::$queryError = 'VALI:'.self::$valiError;
				return FALSE;
			}
		}
		$rs = mysql_query( $query, self::dbOpen($key) );
		if( $rs === TRUE ){
			self::$queryCount = mysql_affected_rows();
			self::$queryInsertID = strpos( strtolower(substr( $query, 0, 5 )), 'insert' ) ? mysql_insert_id() : 0;
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
			$type = self::$sql[$key][1];
			if( $type == 'object' ){
				$r = array();
				while( $row = mysql_fetch_object($rs) ) array_push( $r, $row );
			}else if( $type == 'array' ){
				$r = array();
				while( $row = mysql_fetch_row($rs) ) array_push( $r, $row );
			}else if( $type == 'raw' ) return $rs;
			else if( $type[0] == '[' ){
				$r = substr( $type, 1, -1 );
				$row = self::isnum($r) ? mysql_fetch_row($rs) : mysql_fetch_object($rs);
				$r = $row[$r];
			}
			return $r;
		}
	}
	//validation
	static $valiError = NULL;
	static function vali( $data ){
		return TRUE;
	}
	/*
	//upload
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
	}
	*/
}
bs::route();
?>