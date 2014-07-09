<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>list</title>
</head>
<body>
<h1>list</h1>
<table border="1">
	<tr>
		<td>No.</td>
		<td>userid</td>
		<td>nick</td>
	</tr>
<?php
$list = bs::data('list');
if( $list ){
	foreach( $list as $v ){
		bs::out(
		'<tr>',
			'<td>'.$v->no.'</td>',
			'<td>'.$v->id.'</td>',
			'<td>'.$v->ni.'</td>',
		'</tr>'
		);
	}
}else{
	bs::out( '<tr><td colspan="3">no record</td></tr>' );
}
?>
</table>
</body>
</html>
