<?php
class Controller{
	
	private $url = 'http://www.bsidesoft.com/bs/bsPHP/index.php';
	
	private function assert( $v0, $v1 ){
		return '<div style="color:#'.( $v0 === $v1 ? '090">OK' : '900">NO' ).'</div>';
	}
	private function back(){
		return '<br><div><a href="'.$this->url.'">Back</a></div>';
	}
	public function __construct(){
		
	}
	public function index(){
		bs::out( 'TestList<br><br>',
			'<div><a href="'.$this->url.'/fileGet">fileGet</a></div>'
		);
	}
	public function fileGet(){
		bs::out(
			bs::file('test/test.txt'), $this->assert( bs::file('test/test.txt'), '안녕!' ), 
			bs::file('/test/test.txt'), $this->assert( bs::file('/test/test.txt'), '안녕!' ),
			$this->back()
		);
	}
}
?>