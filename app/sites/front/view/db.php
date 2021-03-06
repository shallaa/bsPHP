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
<script src="http://projectbs.github.io/bsJS/bsjs.0.4.js"></script>
</head>
<body>
<h2>CRUD Demo</h2>
<table id="list" cellpadding="0" cellspacing="0">
<tr style="background:#ededed">
<?php
$list = bs::query( 'list', NULL );
foreach( $list[0] as $key=>$val ) bs::out( '<td>'.$key.'</td>' );
?>
<td>edit</td><td>del</td>
</tr>
<?php
foreach( $list as $v ){
	foreach( $v as $key=>$val ) bs::out( ( $key == 'no' ? '<tr id="tr'.$val.'" class="row"><td>'.$val : '<td><input type="text" id="'.$key.$v->no.'" value="'.$val.'" data-value="'.$val.'">' ).'</td>' );
	bs::out(
	'<td><button id="e'.$v->no.'">edit</button>',
	'<td><button id="d'.$v->no.'">remove</button>',
	'</tr>'
	);
}
?>
<tr><td colspan="5"><button id="u">update all</button></td></tr>
</table>
<div id="err"></div>
<table cellpadding="0" cellspacing="0">
<tr id="add">
	<td><input type="text" id="aid"></td>
	<td><input type="text" id="anick"></td>
	<td colspan="2"><button id="a">add</button></td>
</tr>
</table>
<script>
bs( function(){
	var base = location.href;
	base = base.substr( 0, base.lastIndexOf('/') ) + '/db/';
	(function mk(c){
		bs.Style.fn( 'key', c, function( self, style, v ){
			if( v === undefined ) return self[c];
			else if( v === null ) return delete self[c], style.background = '', v;
			else return self[c] = parseInt(v), style.background = 'rgb(' + (self.R||0) + ',' + (self.G||0) + ',' + (self.B||0) + ')', self[c];
		} );
		return mk || arguments.callee;
	})('R')('G')('B');
	function dEndOk(t){
		t.S(null);
	}
	function dEndFail(t){
		bs.Dom( t.S( '>0' ) ).S( '@disabled', null, 'html', 'remove' );
	}
	bs.Dom('button').S( 'click', function(e){
		var i = this.id.substr(1), data, j, t0, k;
		switch(this.id.charAt(0)){
		case'a':
			data = JSON.parse(bs.post( null, base + 'add', 'pass', 'testpassword', 'pwc', 'testpassword', 'id', bs.Dom('#aid').S('@value'), 'nick', bs.Dom('#anick').S('@value') ));
			if( data.err ){
				bs.Dom('#err').S( 'html', data.err );
				bs.Dom('#a').S('@disabled', true ), bs.ANI.style( bs.Dom('#add').S( 'R', 255, 'G', 200, 'B', 200, 'this' ),  'G', 255, 'B', 255, 'delay', .5, 'time', 1.5 );
			}else{
				bs.Dom('#list').S( 'html+',
					'<tr id="tr' + data.no + '"><td>' + data.no + '</td>' +
					'<td><input type="text" id="id' + data.no + '" value="' + data.id +'" data-value="' + data.id +'"></td>' +
					'<td><input type="text" id="nick' + data.no + '" value="' + data.nick +'" data-value="' + data.nick +'"></td>' +
					'<td><button id="e' + data.no + '">edit</button>' +
					'<td><button id="d' + data.no + '">remove</button>' +
					'</tr>'
				);
				bs.ANI.style( bs.Dom( '#tr' + data.no ).S( 'R', 200, 'B', 200, 'G', 255, 'this' ), 'R', 255, 'B', 255, 'delay', .5, 'time', 1.5 );
			}
			bs.Dom('#aid').S( '@value', '' ), 'nick', bs.Dom('#anick').S( '@value', '' ), bs.Dom('#a').S('@disabled', null );
			break;
		case'd':
			data = bs.post( null, base + 'del', 'no', i );
			if( data == '1' ){
				bs.ANI.style( bs.Dom('#tr'+i).S( 'R', 200, 'G', 255, 'B', 200, 'opacity', 1, 'this' ), 'opacity', 0, 'delay', .5, 'time', 1.5, 'end', dEndOk );
			}else{
				bs.Dom('#err').S( 'html', data );
				bs.ANI.style( bs.Dom( bs.Dom(this).S( '@disabled', true, 'html', 'failed', '<' ) ).S( 'R', 255, 'G', 200, 'B', 200, 'this' ), 'G', 255, 'B', 255, 'delay', .5, 'time', 1.5, 'end', dEndFail );
			}
			break;
		case'e':
			data = bs.post( null, base + 'edit', 'no', i, 'id', bs.Dom('#id'+i).S('@value'), 'nick', bs.Dom('#nick'+i).S('@value') );
			if( data == '1' ){
				bs.Dom('#id'+i).S('*value', bs.Dom('#id'+i).S('@value') ), bs.Dom('#nick'+i).S( '*value', bs.Dom('#nick'+i).S('@value') );
				bs.ANI.style( bs.Dom( '#tr' + i ).S( 'R', 200, 'B', 200, 'G', 255, 'this' ), 'R', 255, 'B', 255, 'delay', .5, 'time', 1.5 );
			}else{
				bs.Dom('#err').S( 'html', data );
				data = bs.Dom(this);
				bs.Dom('#id'+i).S('@value', bs.Dom('#id'+i).S('*value') ), bs.Dom('#nick'+i).S( '@value', bs.Dom('#nick'+i).S('*value') );
				data.S( '@disabled', true, 'html', 'failed' );
				bs.ANI.style( bs.Dom('#tr'+i).S( 'R', 255, 'G', 200, 'B', 200, 'this' ), 'G', 255, 'B', 255, 'delay', .5, 'time', 1.5, 'end', function(){
					data.S( '@disabled', null, 'html', 'edit' );
				});
			}
			break;
		case'u':
			for( t0 = [null, base + 'update'], data = bs.Dom('@.row'), i = 0, j = data.length ; i < j ; i++ ){
				k = data.S( i, '@id' ).substr(2);
				if( bs.Dom('#id'+k).S('*value') != bs.Dom('#id'+k).S('@value') || bs.Dom('#nick'+k).S('*value') != bs.Dom('#nick'+k).S('@value') ){
					t0.push( 'id'+k, bs.Dom('#id'+k).S('@value'), 'nick'+k, bs.Dom('#nick'+k).S('@value') );
				}
			}
			if( t0.length > 2 ){
				data = bs.post.apply( null, t0 );
				if( data == '1' ){
					for( i = 2, j = t0.length ; i < j ; i += 4 ){
						k = t0[i].substr(2);
						bs.Dom('#id'+k).S('*value', bs.Dom('#id'+k).S('@value') ), bs.Dom('#nick'+k).S( '*value', bs.Dom('#nick'+k).S('@value') );
						bs.ANI.style( bs.Dom( '#tr'+k ).S( 'R', 200, 'B', 200, 'G', 255, 'this' ), 'R', 255, 'B', 255, 'delay', .5, 'time', 1.5 );
					}
				}else{
					bs.Dom('#err').S( 'html', data );
					for( i = 2, j = t0.length ; i < j ; i += 4 ){
						k = t0[i].substr(2);
						bs.Dom('#id'+k).S('@value', bs.Dom('#id'+k).S('*value') ), bs.Dom('#nick'+k).S( '@value', bs.Dom('#nick'+k).S('*value') );
						bs.ANI.style( bs.Dom('#tr'+k).S( 'R', 255, 'G', 200, 'B', 200, 'this' ), 'G', 255, 'B', 255, 'delay', .5, 'time', 1.5 );
					}
				}
			}
		}
	});
});
</script>
</body>
</html>
