<?php
require_once( 'util.php' );

class run{}
class data extends run{
	static private $key;
	static private $cache = array();
	static function _( $id ){
		if( !$id ) return new self( null );
		if( $id[0] == '@' ){
			$id = substr( $id, 1 );
			self::$cache[$id] = new self( $id );
		}else if( !@self::$cache[$id] ){
			self::$cache[$id] = new self( $id );
		}
		return self::$cache[$id];
	}
	function run( $key, $val = NULL ){
		$type = _::arg( func_get_args(), func_num_args() );
		if( strpos( $key, '.' ) !== false ){
			$t0 = explode( '.', $key );
			if( @$this->$t0[0] instanceof SimpleXMLElement ){
				return str::xmlget( $this->$t0[0], substr( $key, strpos( $key, '.' ) + 1 ) );
			}else{
				$t1 = '$this->';
				for( $i = 0, $j = count( $t0 ) - 1 ; $i < $j ; $i++ ){
					if( $i == 0 ){
						$t1 .= str::bsval( $t0[$i] );
					}else{
						$t1 .= "['". str::bsval( $t0[$i] ) ."']";
					}
					eval( 'if( !isset( '. $t1 .' ) ) '. $t1 .' = array();' );
				}
				$key = str::bsval( $t0[$j] );
				if( $key[0] == '=' ){
					eval( '$t1 = '. $t1 .'[$key]( $val );' );
					return $t1;
				}else if( $type == 's' ){
					eval( $t1 .'[$key] = $val;' );
				}
				eval( '$t1 = '. $t1 .'[$key];' );
				
				return $t1;
			}
		}else if( $key[0] == '=' ){
			return $this->$key( $val );
		}else{
			if( $type == 's' ){
				$this->$key = $val;
			}
			return @$this->$key;
		}
	}
}
class datas extends run{
	static private $cache = array();
	static function _( $id ){
		if( !$id ) return new self( null );
		if( $id[0] == '@' ){
			$id = substr( $id, 1 );
			self::$cache[$id] = new self( $id );
		}else if( !@self::$cache[$id] ){
			self::$cache[$id] = new self( $id );
		}
		return self::$cache[$id];
	}
	public $data = array();
	function run( $key, $val ){
		$i = count( $this->data );
		$t0 = array();
		while( $i-- ) array_push( $t0, $this->data[i].run( $key, $val ) );
		return $t0;
	}
}
class cls extends run{
	static private $cache = array();
	static function _( $id ){
		if( !$id ) return new self( null );
		if( $id[0] == '@' ){
			$id = substr( $id, 1 );
			self::$cache[$id] = new self( $id );
		}else if( !@self::$cache[$id] ){
			self::$cache[$id] = new self( $id );
		}
		return self::$cache[$id];
	}
	private $attr = array();
	function cls( $id ){
		$this->attr['type'] = $id;
	}
	function run( $key, $val ){
		$type = _::arg( func_get_args(), func_num_args() );
		switch( $key ){
		case'type': return $this->attr['type'];
		case'new':
			if( $val ){
				$t0 = new datas();
				for( $i = 0 ; $i < $val ; $i++ ){
					$t1 = data::_();
					array_push( $t0->data, $t1 );
					while( $v = current( $array ) ) $t1->run( key( $v ), $v );
				}
			}else{
				$t1 = data::_();
				while( $v = current( $array ) ) $t1->run( key( $v ), $v );
			}
			return $t1;
		default:
			if( $type == 's' ) $this->attr[$key] = $val;
			return  $this->attr[$key];
		}
	}
	function N(){
		$t1 = data::_();
		while( $v = current( $array ) ) $t1->run( key( $v ), $v );
		return $t1;
	}
}
class sql extends run{
	static private $cache = array();
	static function _( $id ){
		if( !$id ) return new self( null );
		if( $id[0] == '@' ){
			$id = substr( $id, 1 );
			self::$cache[$id] = new self( $id );
		}else if( !@self::$cache[$id] ){
			self::$cache[$id] = new self( $id );
		}
		return self::$cache[$id];
	}
	private $db;
	private $r;
	private $querys;
	function sql( $id ){
		$this->db = 'local';
		$this->r = false;
		$this->querys = false;
	}
	function run( $key ){
		$arg = func_get_args();
		$type = _::arg( $arg, func_num_args() );
		switch( $key ){
		case'db':
			if( $type == 's' ){
				$this->db = $arg[1];
			}
			return $arg[1];
		case'r':
			if( $type == 's' ){
				$this->r = $arg[1];
			}
			return $arg[1];
		case'q':
			if( $type == 's' ){
				$this->querys = explode( ';', $arg[1] );
			}
			return $arg[1];
		case'rs':
			$r = array();
			$sql = $this->querys;
			for( $i = 0, $j = count( $sql ) ; $i < $j ; $i++ ){
				$t1 = $sql[$i];
				if( $arg[1] ) $t1 = bs( 'str(', $t1, $arg[1], ')' );
				//echo( $t1.'<br>' );
				if( strtolower( substr( $t1, 0, 6 ) ) == 'select' ){
					$r[count( $r )] = bs( 'd:'.$this->db, $t1, $this->r );
				}else{
					$r[count( $r )] = bs( 'd:'.$this->db, 'exec', $t1 );
				}
			}
			switch( count( $r ) ){
			case 0: return false;
			case 1: return $r[0];
			default: return $r;
			}
		}
	}
}
class db extends run{
	static private $cache = array();
	static function _( $id ){
		if( !$id ) return new self( null );
		if( $id[0] == '@' ){
			$id = substr( $id, 1 );
			self::$cache[$id] = new self( $id );
		}else if( !@self::$cache[$id] ){
			self::$cache[$id] = new self( $id );
		}
		return self::$cache[$id];
	}
	private $data = array();
	private $conn;
	function run( $key ){
		$arg = func_get_args();
		$type = _::arg( $arg, func_num_args() );
		switch( $key ){
		case'url':case'id':case'pw':case'db':
			if( $type == 's' ) $this->data[$key] = $arg[1];
			return $this->data[$key];
		case'open':
			if( !$this->conn ){
				$this->conn = mysql_connect( $this->data['url'], $this->data['id'], $this->data['pw'] );
				mysql_select_db( $this->data['db'], $this->conn );
				$val = $type == 'g' ? 'utf8' : $arg[1];
				mysql_query('set session character_set_connection='.$val.';');
				mysql_query('set session character_set_results='.$val.';');
				mysql_query('set session character_set_client='.$val.';');
			}
			return $this->conn;
		case'close':
			if( $this->conn ) mysql_close( $this->conn );
			$this->conn = false;
			return true;
		case'exec':
			if( !$this->conn ) $this->run( 'open' );
			$val = explode( ';', $arg[1] );
			$t0 = true;
			$i = 0;
			$j = count( $val );
			while( $i < $j ) if( !mysql_query( $val[$i++], $this->conn ) ) $t0 = false;
			return $t0;
		default:
			if( !$this->conn ) $this->run( 'open' );
			$r = mysql_query( $key, $this->conn );
			if( !$r || !mysql_num_rows( $r ) ){
				return false;
			}else if( $type == 's' ){
				if( $arg[1] == 'R' ){
					return $r;
				}else{
					mysql_data_seek( $r, $arg[1] );
					return mysql_fetch_row( $r );
				}
			}else{
				$rs = array();
				while( $row = mysql_fetch_row( $r ) ) array_push( $rs, $row );
				return $rs;
			}
		}
	}
	static function limit( $page, $rpp ){
		return ( $page - 1 ) * $rpp;
	}
	static function tp( $total, $rpp ){
		return (int)( ( $total - 1 ) / $rpp ) + 1;
	}
}
class _{
	static $temp = array();
	static function temp(){
		$arg = func_get_args();
		$i = 0;
		$j = func_num_args();
		while( $i < $j ) _::$temp[$arg[$i++]] = $arg[$i++];
	}
	static function arg( $arg, $len ){
		if( $len > 1 ){
			$val = $arg[1];
			if( $val == NULL ){//remove
				return 'd';
			}else{//set
				return 's';
			}
		}else{//get
			return 'g';
		}
	}
	static private $protocol = array();
	static private $pool = array( 'length'=>0 );
	static private $ut = array( 'length'=>0 );
	static function protocol( $p, $cls ){
		_::$protocol[$p] = $cls;
	}
	static private function sep( $str ){
		$quot = -1;
		$arr = $func = $depth = 0;
		$r = _::$pool['length'] ? _::$pool[--_::$pool['length']] : array( 'length'=>0 );
		$i = 0;
		$j = strlen( $str );
		$st = '';
		while( $i < $j ){
			$c = $str[$i++];
			if( $quot == -1 ){
				switch( $c ){
				case'{': $st .= $c; $depth++; break;
				case'}':
					$st .= $c;
					$depth--;
					if( !$depth ){
						$r[$r['length']++] = trim( $st ); $st = '';
					}
					break;
				case',':
					if( $depth ){
						$st .= $c;
					}else{
						$t0 = trim( $st );
						if( $t0 !== '' ){
							$r[$r['length']++] = $t0; $st = '';
						}
					}
					break;
				case'[':case'(':
					$st .= $c;
					if( !$depth ){
						$r[$r['length']++] = trim( $st ); $st = '';
					}
					break;
				case')':
					if( !$depth ){
						$t0 = trim( $st );
						if( $t0 !== '' ){
							$r[$r['length']++] = $t0; $st = '';
						}
						$r[$r['length']++] = $c;
					}else{
						$st .= $c;
					}
					break;
				case']':
					if( !$depth ){
						$t0 = trim( $st );
						if( $t0 !== '' ){
							$r[$r['length']++] = trim( $st ); $st = '';
						}
						$r[$r['length']++] = $c;
					}else{
						$st .= $c;
					}
					break;
				case'"': 
					if( $str[$i] == '"' ){
						$st .= '"';
						$i++;
					}else{
						$quot = $i; 	
					}
					break;
				default: $st .= $c;
				}
			}else if( $c == '"' ){
				if( $str[$i] == '"' ){
					$i++;
				}else{
					$st = trim( $st ).str_replace( '""', '"', trim( substr( $str, $quot, $i - $quot - 1 ) ) );
					$quot = -1;
				}
			}
		}
		$t0 = trim( $st );
		if( $t0 ) $r[$r['length']++] = $t0;
		//util::forin( $r );
		return $r;
	}
	static private function token( $str, $isKey = NULL ){
		if( gettype( $str ) == 'string' ){
			$str = trim( $str );
			switch( $str ){
			case'true': return true;
			case'false': return false;
			case'null': return null;
			default:
				$i = strpos( $str, '{' );
				if( $i > -1 ){
					$j = strrpos( $str, '}' );					
					if( $isKey || $i > 0 || $j < strlen( $str ) - 1 ){
						$str = substr( $str, 0, $i ) . _::run( substr( $str, $i + 1, $j - $i - 1 ) ) . substr( $str, $j + 1 );
					}else{
						return _::run( substr( $str, $i + 1, $j - $i - 1 ) );
					}
				}
				if( preg_match( '/^[-]?(?:[0-9]+)$/', $str ) ) return (int)$str;
				if( preg_match( '/^[%]([0-9]+)$/', $str ) ) return _::$temp[(int)substr( $str, 1 )];
				$str = preg_replace_callback( '/[%]([0-9]+)/', '_::token_', $str );
				if( preg_match( '/^[$](?:[0-9]+)$/', $str ) ) return _::$temp[substr( $str, 1 )];
				$str = preg_replace( '/[$]([0-9]+)/', '_::$temp[\\1]', $str );
			}
		}
		return $str;
	}
	static function token_( $input ){
		return _::$temp[$input[1]];
	}
	static private function expr( &$stk ){
		$a = $stk['A'];
		if( $a['length'] > $stk['C'] ){
			$t1 = $a[$stk['C']++];			
			$t0 = _::token( $t1 );
			if( gettype( $t0 ) == 'string' ){
				if( $t0 == '[' ){
					return _::ARR( $stk );
				}else if( $t0[0] == '{' ){
					return _::run( substr( $t0, 1, -1 ), $stk );
				}else if( $t0[strlen($t0) - 1] == '(' ){
					return _::UTIL( $t0, $stk );
				}
			}
			return $t0;
		}
		return NULL;
	}
	static private function ARR( &$stk ){
		$t0 = array();
		$t1 = _::expr( $stk );
		while( $t1 != ']' ){
			array_push( $t0, $t1 );
			$t1 = _::expr( $stk );
		}
		return $t0;
	}
	static private function UTIL( $val, &$stk ){
		if( $val[0] == '%' ){
			$val = '_::temp';
		}else{
			if( strpos( $val, '.' ) === false ){
				if( strpos( $val, '+' ) !== false ){
					$val = str_replace( '+', '.plus', $val );
				}else{
					$val = str_replace( '(', '.at(', $val );
				}
			}
			$val = substr( $val, 0, -1 );
		}
		$t1 = _::$ut['length'] ? _::$ut[--_::$ut['length']] : array();
		$t0 = _::expr( $stk );
		while( $t0 !== ')' ){
			array_push( $t1, $t0 );
			$t0 = _::expr( $stk );
		}
		$t0 = util::apply( $val, $t1 );
		$t1 = array();
		_::$ut[_::$ut['length']++] = $t1;
		
		return $t0;
	}
	static function run( $arg, &$stk = NULL ){
		if( gettype( $arg ) == 'string' ){
			$arg = _::sep( $arg );
		 	$i = $arg['length'];
			$j = 1;
		}else{
			$i = $arg['length'];
			if( $i == 1 && gettype( $arg[0] ) == 'string' && ( strpos( $arg[0], ',' ) !== false || $arg[0][strlen( $arg[0] ) - 1] == ')' ) ){
				$arg = _::sep( $arg[0] );
				$i = $arg['length'];
				$j = 1;
			}
		}
		if( $stk ){
			$stk[$stk['length']++] = $stk['C'];
			$stk[$stk['length']++] = $stk['A'];
		}else{
			$stk = _::$pool['length'] ? _::$pool[--_::$pool['length']] : array( 'length'=>0, 'C'=>0 );
		}
		$stk['A'] = $arg;
		$t0 = _::token( $arg[0] );
		$isSel = false;
		if( gettype( $t0 ) == 'string' ){
			if( @_::$protocol[substr( $t0, 0, 2 )] ){
				$isSel = true;
				$t1 = substr( $t0, 2 );
				switch( $t0[0] ){
				case'D': $t0 = data::_( $t1 ); break;
				case's': $t0 = sql::_( $t1 ); break;
				case'd': $t0 = db::_( $t1 ); break;
				case'C': $t0 = cls::_( $t1 ); break;
				case'@': $t0 = datas::_( $t1 );
				}
			}else if( $t0[0] == '@' ){
				$isSel = true;
				$t0 = cls::_( substr( $t0, 1 ) );
				$t0 = $t0->N();
			}
		}else if( $t0 instanceof run ){
			$isSel = true;
		}
		if( $isSel ){
			$stk['C'] = 1;
			if( $i == 1 ){
				$r = $t0;
			}else{
				while( $stk['C'] < $i ){
					$key = _::token( $arg[$stk['C']++], 1 ).'';
					if( $key == '@' ){
						$r = $t0;
						break;
					}else{
						$t1 = _::expr( $stk );
						if( $t1 === NULL ){
							$r = $t0->run( $key );
						}else{
							$r = $t0->run( $key, $t1 );
						}
					}
				}
				//if( $t0::flush ) $t0::flush();
			}
		}else{
			$stk['C'] = 0;
			$r = _::expr( $stk );
		}
		if( $i = $stk['length'] ){
			$stk['length'] = $i - 2;
			$stk['A'] = $stk[$i - 1];
			$stk['C'] = $stk[$i - 2];
		}else{
			$stk['C'] = $stk['length'] = 0;
			_::$pool[_::$pool['length']++] = $stk;
		}
		if( @$j ){
			$arg['length'] = 0;
			_::$pool[_::$pool['length']++] = $arg;
		}
		return $r;
	}
}
_::protocol( 'D:', 'data::_' );
_::protocol( 'C:', 'cls::_' );
_::protocol( '@:', 'datas::_' );
_::protocol( 'd:', 'db::_' );
_::protocol( 's:', 'sql::_' );

function bs(){
	$arguments = func_get_args();
	$t0 = array( 'length'=>0 );
	$i = 0;
	$j = func_num_args();	
	while( $i < $j ){
		$key = $arguments[$i++];
		if( $key == '_' ){
			if( $t0['length'] > 0 ){
				if( gettype( $t0[0] ) == 'boolean' ){
					if( $t0[0] ){
						array_shift( $t0 );
						_::run( $t0 );
					}
				}else{
					_::run( $t0 );
				}
			}
			$t0 = array( 'length'=>0 );
		}else{
			$t0[$t0['length']++] = $key;
		}
	}
	if( gettype( $t0[0] ) == 'boolean' ){
		if( $t0[0] ){
			array_shift( $t0 );
			return _::run( $t0 );
		}
	}else{
		return _::run( $t0 );
	}
}
?>