<?php
class Controller{
	public function __construct(){
	}
	
	private $url = 'http://www.bsidesoft.com/bs/bsPHP/index.php';
	
	private function assert( $v0, $v1 ){
		return '<div style="color:#'.( $v0 === $v1 ? '090">OK' : '900">NO' ).'</div>';
	}
	private function back(){
		bs::out( '<br><div><a href="'.$this->url.'">Back</a></div>' );
	}
	
	public function index(){
		bs::out( 'TestList<br><br>',
			'<div><a href="'.$this->url.'/fileGet">fileGet</a></div>',
			'<div><a href="'.$this->url.'/fileSet">fileSet</a></div>'
		);
	}
	public function fileGet(){
		$v0 = bs::file('test/testGet.txt');
		$v1 = bs::file('/test/testGet.txt');
		bs::out(
			'test/testGet.txt : '.$v0, $this->assert( $v0, '안녕!' ), 
			'/test/testGet.txt : '.$v1, $this->assert( $v1, '안녕!' )
		);
		$this->back();
	}
	public function fileSet(){
		bs::file( 'test/testSet.txt', null );
		$v0 = bs::file( 'test/testSet.txt' );
		bs::out( '지우기', $this->assert( $v0, FALSE ) );
		$contents = '안녕쓰기!';
		bs::file( '/test/testSet.txt', $contents );
		$v0 = bs::file( 'test/testSet.txt' );
		bs::out( $v0.':'.strlen($v0).':'.strlen('안녕쓰기!'), $this->assert( $v0, $contents ) );
		$this->back();
	}
}
?>