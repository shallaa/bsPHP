<?php
class Controller{
	
	public function __construct(){}
	public function index( $db, $sql, $key ){
		bs::db($db);
		bs::sql($sql);
		$t0 = bs::queryData($key);
		if( $t0 ){
			$t1 = array();
			foreach( $t0 as $k=>$v ){
				$t1[$k] = $v[0];
			}
			bs::out(bs::jsonEncode($t1));
		}
	}
}
?>