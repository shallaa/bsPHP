<?php
class Controller{
	
	private $url;
	private function subTitle($v){bs::out( '<h2>'.$v.'</h2>' );}
	private function assert( $v0, $v1 ){return '<div style="color:#'.( $v0 === $v1 ? '090">OK' : '900">NO' ).'</div>';}
	
	public function __construct(){
		if( strpos( $_SERVER["SERVER_NAME"], 'cookilab' ) !== FALSE ) $this->url = 'http://bsphp.cookilab.com/index.php';
		else if( strpos( $_SERVER["SERVER_NAME"], 'bsidesoft' ) !== FALSE ) $this->url = 'http://www.bsidesoft.com/bs/bsPHP/index.php';
		else $this->url = 'http://www.bsplugin.com/bsPHP/index.php';
	}
	public function index( $m = FALSE ){
		bs::out( '<h1>Test Suite</h1>',
			'<div>module : '.( $m ? $m : 'all' ).'</div>'
		);
		
		//cookie
		if( !$m || strpos( $m, 'ck' ) !== FALSE ){
			$this->subTitle('Cookie');
			$v0 = bs::ckGet('test');
			$v0 = $v0['test'];
			if( $v0 === null ){
				bs::ck( 'test', 'Cookie 테스트' );
				header("Refresh:0"); 
				exit;
			}
			bs::out( '/ck : '.$v0, $this->assert( $v0, 'Cookie 테스트' ) );
			bs::ck( 'test' );
		}
		
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
		
		//curl
		if( !$m || strpos( $m, 'curl' ) !== FALSE ){
			$this->subTitle('GET');
			$v0 = bs::get( $this->url.'/get' );
			bs::out( '/get : '.$v0, $this->assert( $v0, 'GET테스트' ) );

			$this->subTitle('POST,IN');
			$v0 = bs::post( $this->url.'/post', 'test', 'POST테스트', 'num', 30 );
			bs::out( '/post : '.$v0, $this->assert( $v0, 'POST테스트integer30' ) );
		}
		
		//xml
		if( !$m || strpos( $m, 'xml' ) !== FALSE ){
			$this->subTitle('XML');
			$xml = array(
				'<rss>',
					'<thread><id>1</id><title>안녕1</title><contents data="14/05/15">내용이다!-1</contents></thread>',
					'<thread><id>2</id><title>안녕2</title><contents data="14/05/14">내용이다!-2</contents></thread>',
					'<kkk><id>1</id><title>안녕3</title><contents data="14/05/13">내용이다!-3</contents></kkk>',
					'<thread><id>3</id><title>안녕4</title><contents data="14/05/12">내용이다!-4</contents></thread>',
					'<kkk><id>2</id><title>안녕5</title><contents data="14/05/11">내용이다!-5</contents></kkk>',
					'<aaa><id>2</id><title>안녕6</title><contents data="14/05/10">내용이다!-6</contents></aaa>',
				'</rss>'
			);
			bs::out( 'XML:<br>'.implode( '<br>', preg_replace( '/[<]/', '&lt;', $xml ) ).'<br><br>' );
			$xml = implode( '', $xml );
			foreach( array(
				'thread.0.id'=>'1', 'thread.1.title'=>'안녕2', 'thread.2.contents'=>'내용이다!-4',
				'aaa.title'=>'안녕6', 'aaa.contents.value'=>'내용이다!-6',
				'kkk.1.contents.@data'=>'14/05/11',
			) as $k=>$v ) bs::out( $k.': '.bs::xml( $xml, $k ), $this->assert( bs::xml( $xml, $k ), $v )  );
		}
		
		//apply
		if( !$m || strpos( $m, 'apply' ) !== FALSE ){
			$this->subTitle('Apply');
			bs::out( 'no argument : '.bs::apply( $this, 'applyTest' ), $this->assert( bs::apply( $this, 'applyTest' ), 0 ) );
			bs::out( 'array argument : '.bs::apply( $this, 'applyTest', array( 3, 5 ) ), $this->assert( bs::apply( $this, 'applyTest', array( 3, 5 ) ), 8 ) );
			bs::out( 'arguments : '.bs::apply( $this, 'applyTest', 3, 5 ), $this->assert( bs::apply( $this, 'applyTest', 3, 5 ), 8 ) );
			
		}
	}
	public function applyTest( $a = 0, $b = 0 ){
		return $a + $b;
	}
	public function get(){bs::out('GET테스트');}
	public function post(){
		$v0 = bs::in( 'test', 's', 'num', 'i' );
		bs::out($v0['test'].gettype($v0['num']).$v0['num']);
	}
	//db
	public function db(){
		bs::db('local');
		bs::sql('member');
		bs::out(
			'<style>',
				'table{margin-bottom:30px;border-top:1px solid #000;border-left:1px solid #000}',
				'td{border-bottom:1px solid #000;border-right:1px solid #000;padding:10px}',
			'</style>',
			'<h2>list</h2>',
			'<table cellpadding="0" cellspacing="0">'
		);
		$list = bs::query('list');
		foreach( $list[0] as $key=>$val ) bs::out( '<td>'.$key.'</td>' );
		bs::out('</tr>');
		foreach( $list as $v ){
			bs::out('<tr>');
			foreach( $v as $key=>$val ) bs::out( '<td>'.$val.'</td>' );
			bs::out('</tr>');
		}
		bs::out(
			'</table>',
			'<h2>view5</h2>',
			'<table cellpadding="0" cellspacing="0"><tr>'
		);
		foreach( bs::query('view',array('rowid'=>5)) as $v ){
			foreach( $v as $key=>$val ) bs::out( '<td>'.$key.'='.$val.'</td>' );
		}
		bs::out('</tr></table>');
	}
}
?>