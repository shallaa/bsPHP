<?php
class Controller{
	public function __construct(){
		
	}
	public function index( $p0, $p1 ){
		echo( 'other.index::'.$p0.','.$p1 );
	}
	public function go($p0, $p1 ){
		echo( 'other.go::'.$p0.','.$p1 );
	}
}
?>