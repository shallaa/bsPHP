<?php
class Controller{
	
	private $url;
	private function subTitle($v){bs::out( '<h2>'.$v.'</h2>' );}
	private function assert( $v0, $v1 ){return '<div style="color:#'.( $v0 === $v1 ? '090">OK' : '900">NO' ).'</div>';}
	
	public function __construct(){
		$this->url = 'http://www.'.( strpos( $_SERVER["SERVER_NAME"], 'bsidesoft' ) !== FALSE ? 'bsidesoft.com/bs' : 'bsplugin.com' ). '/bsPHP/index.php';
	}
	public function index( $m ){
		bs::out( '<h1>Test Suite</h1>',
			'<div>module : '.( $m ? $m : 'all' ).'</div>'
		);
		
		$v = 'aaaaa';
		bs::out( count( explode( '#', $v ) ) );
		
		//file
		if( !$m || strpos( $m, 'file' ) !== FALSE ){
			$this->subTitle('파일읽기');
			$v0 = bs::file('test/testGet.txt');
			$v1 = bs::file('/test/testGet.txt');
			bs::out(
				'test/testGet.txt : '.$v0, $this->assert( $v0, '안녕!' ), 
				'/test/testGet.txt : '.$v1, $this->assert( $v1, '안녕!' )
			);
			
			$this->subTitle('파일삭제');
			bs::file( 'test/testSet.txt', null );
			$v0 = bs::file( 'test/testSet.txt' );
			bs::out( 'test/testSet.txt : '.($v0 === FALSE ? '없음' : '존재'), $this->assert( $v0, FALSE ) );
			
			$this->subTitle('파일쓰기');
			$contents = '안녕쓰기!';
			bs::file( '/test/testSet.txt', $contents );
			$v0 = bs::file( 'test/testSet.txt' );
			bs::out( $v0.':'.strlen($v0).':'.strlen('안녕쓰기!'), $this->assert( $v0, $contents ) );
		}
	}
}
?>