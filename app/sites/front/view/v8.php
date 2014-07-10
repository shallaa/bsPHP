<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title></title>
<style>
body{magin:0;padding:15px}
table{margin-bottom:30px;border-top:1px solid #999;border-left:1px solid #999}
td{border-bottom:1px solid #999;border-right:1px solid #999;padding:10px;text-align:center}
button{width:100px;text-align:center}
#err{color:#f00;font-weight:bold}
</style>
<!--<script src="http://projectbs.github.io/bsJS/bsjs.0.4.js"></script>-->
<script src="http://www.bsidesoft.com/bs/bsJS/bsjs.0.5.js"></script>
</head>
<body>
<script>
bs( function(){
	//aa라는 이름의 네트워커 등록
	bs.networker( 'aa', function(a){
		return a * ( a + 1 ) / 2;
	} );
	//aa네트워커에 완료리스너, 인자1, 호출 
	bs.networker('aa')( function(data){
		bs.Dom('body').S( 'html+', '<div>netWorker:( 20 ) = ' + data + '</div>' );
	}, 20 );
	bs.networker('aa')( function(data){
		bs.Dom('body').S( 'html+', '<div>netWorker:( 100 ) = ' + data + '</div>' );
	}, 100 );
	
	//bb라는 이름의 네트워커 등록
	bs.worker( 'bb', function( a, b ){
		return {v:a * ( a + 1 ) / 2 - b}; //오브젝트로 출력해보기
	} );
	//bb네트워커에 완료리스너, 인자1, 인자2..호출 
	bs.worker('bb')( function(data){
		bs.Dom('body').S( 'html+', '<div>worker:( 20, 30 ) = ' + data.v + '</div>' );
	}, 20, 30 );
	bs.worker('bb')( function(data){
		bs.Dom('body').S( 'html+', '<div>worker:( 30, 50 ) = ' + data.v + '</div>' );
	}, 30, 50 );
	
	bs.Dom('body').S( 'html+', '<pre style="background:#eee;width:100%">'+arguments.callee.toString()+'</pre>' );
});
</script>
</body>
</html>
