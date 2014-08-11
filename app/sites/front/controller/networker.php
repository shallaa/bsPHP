<?php
class Controller{
	
	private $key = "BSNETWORKER_20140707";
	
	public function index(){
		if( count($_POST) != 2 || !isset($_POST['BS']) || $_POST['BS'] != $this->key || !isset($_POST['c']) ) return;
		ob_start();
		$v8 = new V8Js();
		$class = new ReflectionClass('Controller');
		foreach( $class->getMethods() as $v ){
			$k = $v->name;
			if( $k != 'index' ) $v8->{$k} = $this->{$k};
		}
		$v8->executeString( 'bsPHP=PHP;print(JSON.stringify('.$_POST['c'].'));' );
		bs::out(ob_get_clean());
	}
}
?>