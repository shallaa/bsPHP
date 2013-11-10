<?php
class bsRouter{
	static private $target = '';
	static private $config = '';
	static private $rule = array();
	static private $table = array();
	static function config( $t, $i ){
		self::$target = @getenv( "DOCUMENT_ROOT" ).$t;
		self::$config = self::$target.($i[0]=='/'?'':'/').$i;
	}
	static function table(){
		for( $i = 0, $j = func_num_args(), $arg = func_get_args() ; $i < $j ; ) self::$table[$arg[$i++]] = $arg[$i++];
	}
	static function rule(){
		self::$rule = func_get_args();
	}
	static function route(){
		$uri = @getenv( "SCRIPT_NAME" );
		require_once( self::$target.'/bs/bs.php' );
		if( self::$config ) require_once( self::$config );
		if( $t0 = @self::$table[$uri] ) require_once( self::$target . $t0 );
		else if( $j = count( self::$rule ) ){
			for( $i = 0 ; $i < $j ; $i++ ){
				$rule = self::$rule[$i];
				$t0 = $uri;
				$k = 0; $l = count( $rule );
				while( $k < $l ){
					$key = $rule[$k++]; $val = $rule[$k++];
					if( $key == 'require' ){
						$t0 = $val;
						break;
					}else{
						$t0 = explode( '/', $t0 );
						switch( $key ){
						case'path': $t0[$val] = $rule[$k++]; break;
						case'tail':
							$file = explode( '.', $t0[count($t0)-1] );
							$t0[count($t0)-1] = $file[0].$val.'.'.$file[1];
							break;
						case'head':
							$file = explode( '.', $t0[count($t0)-1] );
							$t0[count($t0)-1] = $val.$file[0].'.'.$file[1];
							break;
						case'name':
							$file = explode( '.', $t0[count($t0)-1] );
							$t0[count($t0)-1] = $val.'.'.$file[1];
							break;
						case'ext':
							$file = explode( '.', $t0[count($t0)-1] );
							$t0[count($t0)-1] = $file[0].'.'.$val;
						}
						$t0 = implode( '/', $t0 );
					}
				}
				require_once( self::$target.$t0 );
			}
		}else require_once( self::$target.$uri );
	}
}
?>