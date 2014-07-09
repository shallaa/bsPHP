<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>view</title>
</head>
<body>
<h1>view</h1>
<table border="1">
	<tr>
		<td>No.</td>
		<td>userid</td>
		<td>nick</td>
	</tr>
<?php
$view = bs::data('view');
if( $view ){
	bs::out(
	'<tr>',
		'<td>'.$view->no.'</td>',
		'<td>'.$view->id.'</td>',
		'<td>'.$view->ni.'</td>',
	'</tr>'
	);
}else{
	bs::out( '<tr><td colspan="3">no record</td></tr>' );
}
?>
</table>
</body>
</html>
