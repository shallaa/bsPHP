<?php
class Controller{
	
	function __construct(){
		bs::db( 'local', FALSE );
		bs::sql('sql', FALSE);
	}
	function index(){
		bs::data( 'list', bs::query( 'list', NULL, FALSE ) );
		bs::view( 'list', FALSE );
	}
	function view( $no ){
		bs::data( 'view', bs::query( 'view', array( 'no'=>$no ) ) );
		bs::view( 'view', FALSE );
	}
	function add(){
		if( count($_POST) ){
			if( bs::query('add') ){
				$this->index();
			}else{
				bs::out( bs::$queryError );
			}
		}else{
			bs::view( 'add', FALSE );
		}
	}
}
?>