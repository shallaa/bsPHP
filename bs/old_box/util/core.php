<?php
class core{
	static function T(){
		$arguments = func_get_args();
		$i = 0;
		$j = func_num_args() ;
		while( $i < $j ) if( $arguments[$i++] ) return true;
		return false;
	}
	static function F(){
		$arguments = func_get_args();
		$i = 0;
		$j = func_num_args() ;
		while( $i < $j ) if( !$arguments[$i++] ) return true;
		return false;
	}
	static function len( $data, $add = 0 ){
		if( gettype( $data ) == 'string' ){
			return strlen( $data ) + $add;
		}else{
			return count( $data ) + $add;
		}			
	}
	static private $root;
	
	static function fr( $file ){
		if( !core::$root ) core::$root = $_SERVER['DOCUMENT_ROOT'];
		if( substr( $file, 0, 5 ) != substr( core::$root, 0, 5 ) ) $file = core::$root.( $file[0] == '/' ? '' : '/' ).$file;
		if( !( $file = fopen( $file, "r" ) ) ) die( "could not open file" );
		$r = '';
		while( $t0 = fread( $file, 4096 ) ) $r .= $t0;
		return $r;
	}
}
?>