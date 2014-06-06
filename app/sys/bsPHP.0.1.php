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
			call_user_func_array( array( $controller, $method ), $uri );
			foreach( self::$db as $k=>$v ) self::Db( $k, '.close' );
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
	//file
	static function file(){
		$arg = func_get_args();
		if( $arg[0] == '/' ) $arg = substr( $arg[0], 1 );
		if( func_num_args() == 1 ){
			$f = fopen( ROOT.$arg[0], "r" );
			if( !$f ) err( 0, $arg[0] );
			$t0 = '';
			while( $t1 = fread( $f, 4096 ) ) $t0 .= $t1;
			return $t0;
		}else{
			for( $dir = explode( '/', $arg[0] ), $file = array_pop($dir), $path = ROOT, $i = 0, $j = count($dir) ; $i < $j ; ){
				$path .= '/'.$dir[$i++];
				if( !is_dir($path) ) mkdir($path);
			}
			$f = @fopen( $path.'/'.$file , "w+" );
			if( !$f ) err( 1, $path.'/'.$file );
			@flock( $f, LOCK_EX );
				fwrite( $f, pack("CCC",0xef,0xbb,0xbf) );
				fwrite( $f, $arg[1] );
			@flock( $f, LOCK_UN );
			@fclose($f);
		}
	}
	//in,out
	static function out(){
		for( $t0 = '', $i = 0, $j = func_num_args(), $arg = func_get_args() ; $i < $j ; $i++ ) $t0 .= $arg[$i];
		echo($t0);
	}
}
bs::route();
	/*
	//db----------------------------------------------------------------------
	
	static private $db = array();
	static private $dbDefault;
	static function Db($sel){
		if( !self::$dbDefault ) self::$dbDefault = $sel;
		if( !isset(self::$db[$sel]) ) self::$db[$sel] = $d = array( 'conn'=>false, 'driver'=>'mysql' );
		else $d = &self::$db[$sel];
		for( $arg = func_get_args(), $i = 1, $j = func_num_args() ; $i < $j ; ){
			$k = $arg[$i++];
			$v = $i < $j ? $arg[$i++] : NULL;
			switch( $k ){
			case'driver':case'url':case'id':case'pw':case'db': if( $v ) $d[$k] = $v; $v = $d[$k]; break;
			case'conn': return $d['conn'];
			case'.close':
				if( $d['conn'] ){
					switch( $d['driver'] ){
					case'mysql':mysql_close( $d['conn'] ); break;
					}
					$d['conn'] = false;
				}
				return;
			case'.open':
				if( !$d['conn'] ){
					switch( $d['driver'] ){
					case'mysql':
						$d['conn'] = mysql_connect( $d['url'], $d['id'], $d['pw'] );
						mysql_query('SET NAMES euckr');
						mysql_select_db( $d['db'], $d['conn'] );
						$v = $v || 'utf8';
						mysql_query('set session character_set_connection='.$v.';');
						mysql_query('set session character_set_results='.$v.';');
						mysql_query('set session character_set_client='.$v.';');
						break;
					}
				}
				return $d['conn'];
			case'.query':
				if( !$d['conn'] ) self::DB( $sel, '.open' );
				switch( $d['driver'] ){
				case'mysql':return mysql_query( $v, $d['conn'] );
				}
			case'.sql':
				if( !$d['conn'] ) self::DB( $sel, '.open' );
				$query = self::file( BS_PATH_MODEL.$key );
				if( $query !== FALSE ){
					$query = explode( '--', $query );
					for( $i = 1, $j = count($query) ; $i < $j ; $i++ ){
						$k = strpos( $query[$i], "\n" );
						if( $k === FALSE ){
							$k = strpos( $query[$i], "\r" );
							if( $k === FALSE ) return;
						}
						$this->sqlAdd( trim(substr( $query[$i], 0, $k )), trim(substr( $query[$i], $k + 1 )) );
					}
				}
			}
		}
	}
	function sqlParse($str){
		if( strpos( $str[0], ':' ) === FALSE ) return $str;
		$str = explode( ':', $str[0] );
		$meta = explode( '.', $str[1] );
		$vali = self::tableInfo( $meta[0], $this );
		$this->queryInfo[$this->sqlKey][substr( $str[0], 1 )] = $vali[substr( $meta[1], 0, -1 )];
		return $str[0].'@';
	}
	function sqlAdd( $key, $query ){
		if( $query[0] == ':' ){
			$str = explode( ' ', $query );
			switch( $str[0] ){
			case':insert':
				$table = $str[1];
				$insert = array();
				$values = array();
				for( $i = 2, $j = count($str) ; $i < $j ; $i++ ){
					$token = explode( ':', $str[$i] );
					array_push( $insert, substr( $token[1], 0, -1 ) );
					array_push( $values, $token[0].':'.$table.'.'.$token[1] );
				}
				$query = 'insert into '.$table.'('.implode( ',', $insert ).')values('.implode( ',', $values ).')';
				break;
			case ':update':
				$table = $str[1];
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
			case ':delete':
				$table = $str[1];
				$where = array();
				for( $i = 3, $j = count($str) ; $i < $j ; $i++ ){
					$token = explode( ':', $str[$i] );
					array_push( $where, substr( $token[1], 0, -1 ).'='.$token[0].':'.$table.'.'.$token[1] );
				}
				$query = 'delete from '.$table.' where '.implode( ' and ', $where );
				break;
			default:
				return;
			}
		}
		if( isset($this->queryInfo[$key]) === FALSE ) $this->queryInfo[$key] = array();
		$this->sqlKey = $key;
		$this->query[$key] = trim(preg_replace_callback( BS_TEMPLATE, $this->sqlParseContext, $query ));
	}
	function sql( $key, $query = null ){
		$this->dbc();
		if( $this->sqlParseContext === null ) $this->sqlParseContext = array( $this, 'sqlParse' );
		if( $query == null ){
			$query = read_file( BS_PATH_MODEL.$key );
			if( $query !== FALSE ){
				$query = explode( '--', $query );
				for( $i = 1, $j = count($query) ; $i < $j ; $i++ ){
					$k = strpos( $query[$i], "\n" );
					if( $k === FALSE ){
						$k = strpos( $query[$i], "\r" );
						if( $k === FALSE ) return;
					}
					$this->sqlAdd( trim(substr( $query[$i], 0, $k )), trim(substr( $query[$i], $k + 1 )) );
				}
			}
		}else{
			$this->sqlAdd( $key, $query );
		}
	}
	/*
					if( @!$d['conn'] ) self::DB( $sel, '.open' );
				$r = mysql_query(  $v, $d['conn'] );
				if( $r === true ) return true;
				else if( $r === false || mysql_num_rows( $r ) == 0 ) return false;
				$rs = array();
				while( $row = mysql_fetch_row( $r ) ) array_push( $rs, $row );
				return $rs;
			case'.raw':
				if( @!$d['conn'] ) self::DB( $sel, '.open' );
				$r = mysql_query(  $v, $d['conn'] );
				if( !$r || !mysql_num_rows( $r ) ) return false;
				return $r;
			case'.record':
				if( @!$d['conn'] ) self::DB( $sel, '.open' );
				$r = mysql_query(  $v, $d['conn'] );
				if( !$r || !mysql_num_rows( $r ) ) return false;
				mysql_data_seek( $r, $arg[$i] );
				return mysql_fetch_row( $r );

	//sql----------------------------------------------------------------------
	static private $sql = array();
	static function Sql( $sel ){
		if( !@self::$sql[$sel] ) {$s = &self::$sql[$sel]; $s=array();}
		else $s = self::$sql[$sel];
		$i = 1; $j = func_num_args(); $arg = func_get_args();
		while( $i < $j ){
			$k = $arg[$i++];
			$v = $i < $j ? $arg[$i++] : NULL;
			switch( $k ){
			case'query':case'q': if( $v ) $s['q'] = $v; $s['isSelect'] = strtolower( substr( $v, 0, 6 ) ) == 'select'; break;
			case'db':case'type':case'record':case'field': if( $v ) $s[$k] = $v; $v = $s[$k]; break;
			case'.run':
				$r = array();
				$sql = $s['q'];
				$db = @$s['db'] ? $s['db']:self::$dbDefault;
				if( is_array($sql) ){
					$success = true;
					bs::Db( $db, '.ex', "SET AUTOCOMMIT=0" );
					bs::Db( $db, '.ex', "BEGIN" );
					for( $m = 0, $n = count( $sql ) ; $m < $n ; $m++ ){
						if( $v ) $sql[$m] = bs::tmpl( $sql[$m], $v );						
						if( !bs::Db( $db, '.ex', $sql[$m] ) ) return bs::Db( $db, '.ex', "ROLLBACK" );
					}
					bs::Db( $db, '.ex', "COMMIT" );
				}else{
					if( $v ) $sql = bs::tmpl( $sql, $v );
					//echo( $sql.'<br>');
					switch( @$s['type'] ){
					case'raw':
						if( $s['isSelect'] ) return bs::Db( $db, '.raw', $sql );
						else die();
					case'record':
						if( $s['isSelect'] ) return bs::Db( $db, '.record', $sql, $s['record']  );
						else die();
					case'field':
						if( $s['isSelect'] ){
							$r = bs::Db( $db, '.record', $sql, $s['record']  );
							return $r[$s['field']];
						}else die();
					}
					return bs::Db( $db, '.rs', $sql );
				}
			}
		}
	}
	static function tmpl( $val, $arr ){
		if( !is_array($arr) ) $arr = func_get_args();
		foreach( $arr as $key=>$t0 ){
			$t0 = @mysql_escape_string( $t0 );
			if( @$t0[0] == '@' ) $t0 = "'". substr( $t0, 1 ) ."'";
			$val = str_replace( '@'.$key.'@', $t0, $val );
		}
		return $val;
	}
	static function jsonencode($v){return json_encode( $v, 256 );}
	static function jsondecode($v){return json_decode( $v, true );}
	static function limit( $page, $rpp ){return ( $page - 1 ) * $rpp;}
	static function tp( $total, $rpp ){return (int)( ( $total - 1 ) / $rpp ) + 1;}
	static function split( $val, $sep ){return explode( $sep, $val );}
	static function replace( $val, $search, $replace = '' ){return str_replace( $search, $replace, $val );}
	static function stripBr( $val ){return preg_replace( '/[\s]*[\s]/', ' ', preg_replace( '/(<br \/\>)|(<br>)/', ' ', $val ) );}
	static function lineBr( $val ){return preg_replace( '/[\n]/', '<br>', $val );}
	static function db2html( $val ){return  preg_replace( '/\n|\r\n|\r/', '<br/>', str_replace( '<', '&lt;', $val ) );}
	static private $SALT = 'bsidesoft13&%)(*7';
	static function en( $val ){
		return trim( base64_encode( mcrypt_encrypt(
			MCRYPT_RIJNDAEL_256, self::$SALT, $val, MCRYPT_MODE_ECB,
			mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB),MCRYPT_RAND)
		) ) );
	}
	static function de( $val ){ 
		return trim( mcrypt_decrypt(
			MCRYPT_RIJNDAEL_256, self::$SALT, base64_decode($val), MCRYPT_MODE_ECB,
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
		curl_setopt( $t0, CURLOPT_SSL_VERIFYPEER, FALSE);     
		curl_setopt( $t0, CURLOPT_SSL_VERIFYHOST, 2); 
		curl_setopt( $t0, CURLOPT_URL, $url );
		if( ( $j = func_num_args() ) > 1 ){
			$i = 0; $t1 = func_get_args(); $url .= "?";
			while( $i < $j ) $url .= $t1[$i++].'='.$t1[$i++].($i < $j - 1 ? '&' : '');
		}
		
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
	
	static function http( $val ){
		$arguments = func_get_args();
		for( $i = 1, $j = func_num_args() ; $i < $j ; $i++ ) $val .= $arguments[$i];
		echo( $val );
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
	static function hash( $val ){return hash('sha512', 'bs_'.$val.'_sha512' );}
	static function uuid(){return md5(com_create_guid());}
	static function g( $val, $default = false ){return @$_GET[$val] ? trim( $_GET[$val] ) : $default;}
	static function p( $val, $default = false ){return @$_POST[$val] ? trim( $_POST[$val] ) : $default;}
	static function r( $val, $default = false ){return @$_REQUEST[$val] ? trim( $_REQUEST[$val] ) : $default;}
	static function in( $val, $default = false ){
		$r = self::g( $val, $default );
		if( $r ) return $r;
		return self::p( $val, $default );
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
			$expires = $arguments[2] ? time()+3600*24*$arguments[2] : 0;
			setcookie( $key, $arguments[1], $expires, '/' );
		}
	}
	static function session(){		
		$arg = func_get_args();
		$i = 0; $j = func_num_args();
		if( $arg[0] === null ){
			session_destroy();
		}else{
			if( !isset($_SESSION) ) session_start();
			while( $i < $j ){
				$k = $arg[$i++]; 
				if( $i < $j ) $v = $arg[$i++];
				else return @$_SESSION[$k];
				if( $v === null ) unset($_SESSION[$k]);
				else $_SESSION[$k] = $v;
			}
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
	//validate------------------------------------------------------------------
	static function _ip($arg){return preg_match("/^((([0-9]{1,2})|(1[0-9]{2})|(2[0-4][0-9])|(25[0-5]))\.){3}(([0-9]{1,2})|(1[0-9]{2})|(2[0-4][0-9])|(25[0-5]))$/",$arg);	}
	static function _url($arg){
		return preg_match("/^https?:\/\/[-\w.]+(:[0-9]+)?(\/([\w\/_.]*)?)?$/",$arg);	
	}
	static function _email($arg){
		return preg_match("/^(\w+\.)*\w+@(\w+\.)+[A-Za-z]+$/",$arg);	
	}
	static function _korean($arg){
		return preg_match("/^[ㄱ-힣]+$/",$arg);	
	}
	static function _alphaL($arg){
		return preg_match("/^[a-z]+$/",$arg);	
	}
	static function _ALPHAU($arg){
		return preg_match("/^[A-Z]+$/",$arg);	
	}
	static function _num($arg){
		return preg_match("/^[0-9]+$/",$arg);	
	}
	static function _alphanum($arg){
		return preg_match("/^[a-z0-9]+$/",$arg);	
	}
	static function _1alphaL($arg){
		return preg_match("/^[a-z]/",$arg);	
	}
	static function _1ALPHAU($arg){
		return preg_match("/^[A-Z]/",$arg);	
	}
	static function _float($arg){
		return floatval($arg).''===$arg;	
	}
	static function _int($arg){
		return intval($arg,10).''===$arg;	
	}
	static function _ssn($arg){
		$r = '/\s|-/';
		$key = '234567892345';
		$v = preg_replace($r,'',$arg);
		if(strlen($v)!=13) return false;
		for($t0 = $i = 0; $i < 12 ; $i++){
			$t0 += intval($v{$i},10) * intval($key{$i},10);
		}
		return intval($v{12},10) == ((11-($t0%11)%10));
	}
	static function _biz($arg){
		$r = '/\s|-/';
		$key = array(1,3,7,1,3,7,1,3,5,1);
		$v = preg_replace($r,'',$arg);
		$t0=0;
		if(strlen($v)!=10) return false;
		for($t0 = $i = 0; $i < 8 ; $i++){
			$t0 += $key[$i] * intval($v{$i},10); 
		}
		$t1 = '0'. ($key[8] * intval($v{8},10));
		$t1 = substr($t1,strlen($t1)-2);
		$t0 += intval($t1{0},10) + intval($t1{1},10);
		return intval($v{9},10) == ((10-($t0%10)%10));
	}
	static function _length($arg,$len){
		return strlen($arg)===$len;
	}
	static function _range($arg,$s,$e){
		return $s <= strlen($arg) && strlen($arg) <= $e;
	}
	static function _indexOf($arg,$val){
		return strpos($arg, $val) ? 1 : 0;	
	}
	static function valiByFile($filepath){
		$result = array();
		$t0 = self::fileR($filepath);
		$t0 = explode("\n",$t0);
		for( $i = 0, $j = count($t0) ; $i < $j ; $i++ ){
			$t1 = explode('=', trim( $t0[$i] ) );
			if(self::_indexOf(trim( $t1[0] ) ,',')){
				$p = explode (',', $t1[0]);
				for($k = 0, $l = count($p) ; $k < $l ; $k++){
					if( self::ruleTest( trim($t1[1]), $_REQUEST[trim($p[$k])]) ){
						$result[trim($p[$k])]= $_REQUEST[trim($p[$k])];
					}else{ 
						return 0; 
					}
				}
			}else{
				if( self::ruleTest( trim($t1[1]), $_REQUEST[trim($t1[0])]) ){
					$result[trim($t1[0])]= $_REQUEST[trim($t1[0])];
				}else{ 
					return 0; 
				}
			}
		}
		//echo 'print_r';
		//print_r($result);
		return $result;
	}
	static private function ruleTest($rule,$val){
		$result = 1;
		$rule = $rule .',';
		$rule = explode ( ',', $rule);	
		for($i=0,$j=count($rule) ; $i < $j ; $i++){
			if( trim($rule[$i]!='') ){
				switch( trim($rule[$i]) ){
					case 'ip'      : $result = $result && self::_ip($val); break;
					case 'url'     : $result = $result && self::_url($val); break;
					case 'email'   : $result = $result && self::_email($val); break;
					case 'korean'  : $result = $result && self::_korean($val); break;
					case 'alphaL'  : $result = $result && self::_alphaL($val); break;
					case 'ALPHAU'  : $result = $result && self::_ALPHAU($val); break;
					case 'num'     : $result = $result && self::_num($val); break;
					case 'alphanum': $result = $result && self::_alphanum($val); break;
					case '1alphaL' : $result = $result && self::_1alphaL($val); break;
					case '1ALPHAU' : $result = $result && self::_1ALPHAU($val); break;
					case 'float'   : $result = $result && self::_float($val); break;
					case 'int'     : $result = $result && self::_int($val); break;
					case 'ssn'     : $result = $result && self::_ssn($val); break;
					case 'biz'     : $result = $result && self::_biz($val); break;
					//case 'length'  : $result = $result && self::_length    ($val); break;
					//case 'range'   : $result = $result && self::_range     ($val); break;
					//case 'indexOf' : $result = $result && self::_indexOf   ($val); break;	
				}
			}
		}
		return $result;
	}
}


class bsError{
	static private $flag = false;//발생하면 true
	static private $desc = array();
	static function occur($key,$description){
		self::$flag = true;
		self::$desc[$key]=$description;
		//array_push(self::$desc,$key,$description);
	}
	static function isErr(){
		return self::$flag;
	}
	static function count(){
		return count(self::$desc);
	}
	static function desc($key){
		return self::$desc[$key];
	}
	///에러의 이름을 줘서 등록하고, 찾을지 고민중 ex a00, b00, b01.... 비정의는 etc
}
class bsSecure{
	static function login( $id, $pwd, $tp ){
		$t0 =  bs::SQL( 'x01','.run' , array('@'.$id) );
		$dbpwd = '';
		if($t0)$dbpwd = $t0[0][0];
		
		//echo $dbpwd;
		
		if($dbpwd === bs::hash($pwd)){ 
			//login ok
			$tknTm = ''.bs::datePart('ymdhms');
			bs::session( 'id' , $id );
			bs::session( 'idx' , $t0[0][1] );
			bs::session( 'bsTkn' , bs::hash(bs::ip().$tknTm) );
			bs::session( 'tknTm' , $tknTm );//idx, uid, nickname
			bs::ck( 'idx' , $t0[0][1] );
			bs::ck( 'id' , $t0[0][2] );
			bs::ck( 'nickname' , $t0[0][3] );
			bs::ck( 'bsTkn' , bs::hash(bs::ip().$tknTm) );
			return 200;
		}else{
			self::logout();
			return 401;
		}
	}
	static function logout(){
		bs::session( 'id' , '' );
		bs::session( 'bsTkn' , '' );
		bs::ck( 'id' , '' );
		bs::ck( 'bsTkn' , '' );
		bs::ck( 'idx' , '' );
		bs::ck( 'nickname', '' );
	}
	static function loginStatus(){
		/*if( bs::ck( 'bsTkn' ) === bs::session( 'bsTkn' ) && bs::ck( 'bsTkn' ) === bs::hash(bs::ip().bs::session( 'tknTm' )) ) {  
			$tknTm = ''.bs::datePart('ymdhms');
			bs::session( 'bsTkn' , bs::hash( bs::ip().$tknTm ) );
			bs::session( 'tknTm' , $tknTm );
			bs::ck( 'bsTkn' , bs::hash(bs::ip().$tknTm) );
		
		if(bs::session('id')!=''){
			return 200;
		}else{
			return 401;
		}
	}
	static function getUrl(){
		$url = $_SERVER["REQUEST_URI"];
		if(strpos( $url, '?' )){
			$url = explode( '?', $url );
			$url = $url[0];
		}
		if(!strpos( $url, '.php' )){
			$url = $url.'index.php';
		}
		return $url;
	}
}

*/
?>