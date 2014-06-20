<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title></title>
<style>
table{margin-bottom:30px;border-top:1px solid #000;border-left:1px solid #000}
td{border-bottom:1px solid #000;border-right:1px solid #000;padding:10px}
</style>
<script src="http://projectbs.github.io/bsJS/bsjs.0.4.js"></script>
</head>
<body>
<h2>list</h2>
<table id="list" cellpadding="0" cellspacing="0">
<?php
$list = bs::query( 'list', NULL, FALSE );
foreach( $list[0] as $key=>$val ) bs::out( '<td>'.$key.'</td>' );
?>
<td>edit</td>
<td>del</td>
</tr>
<?php
foreach( $list as $v ){
	foreach( $v as $key=>$val ) bs::out( ( $key == 'no' ? '<tr id="tr'.$val.'"><td>'.$val : '<td><input type="text" id="'.$key.$v->no.'" value="'.$val.'">' ).'</td>' );
	bs::out(
	'<td><button id="e'.$v->no.'">edit</button>',
	'<td><button id="d'.$v->no.'">del</button>',
	'</tr>'
	);
}
?>
</table>

<table cellpadding="0" cellspacing="0">
<tr>
	<td><input type="text" id="aid"></td>
	<td><input type="text" id="anick"></td>
	<td colspan="2"><button id="a">add</button></td>
</tr>
</table>
<script>
bs( function(){
	bs.Dom('button').S( 'click', function(e){
		var i = this.id.substr(1);
		switch(this.id.charAt(0)){
		case'a':
			data = JSON.parse( bs.post( null, 'index.php/db/add', 'pw', 'aaaaa', 'pwc', 'aaaaa', 'id', bs.Dom('#aid').S('@value'), 'nick', bs.Dom('#anick').S('@value') ) );
			bs.Dom('#list').S('html+',
				'<tr><td>' + data.no + '</td>' +
				'<td><input type=\"text\" id=\"id' + data.no + '\" value=\"' + data.id +'\"></td>' +
				'<td><input type=\"text\" id=\"nick' + data.no + '\" value=\"' + data.nick +'\"></td>' +
				'</tr>'
			);
			break;
		case'e':
			data = JSON.parse( bs.post( null, 'index.php/db/edit', 'no', i, 'id', bs.Dom('#id'+i).S('@value'), 'nick', bs.Dom('#nick'+i).S('@value') ) );
			bs.Dom('#id'+i).S('@value', data.id );
			bs.Dom('#nick'+i).S('@value', data.nick);
			break;
		case'd':
			JSON.parse( bs.post( null, 'index.php/db/del', 'no', i ) );
			bs.Dom('#tr'+i).S(null);
			break;
		}
	});
	

});
</script>
<h2>view5</h2>
<table cellpadding="0" cellspacing="0"><tr>
<?php
foreach( bs::query( 'view', array( 'rowid'=>5 ), FALSE ) as $v ){
	foreach( $v as $key=>$val ) bs::out( '<td>'.$key.'='.$val.'</td>' );
}
?>
</tr></table>
</body>
</html>
