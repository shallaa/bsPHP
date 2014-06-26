<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>upload</title>
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
<h2>Upload Demo</h2>
<form id="f" method="post" enctype="multipart/form-data">
<div><input type="file" name="upfile" id="upfile"></div>
<div><input type="submit" value="submit"></div>
</form>
<div>
<?php bs::out( bs::data('file') ); ?>
<script>
bs( function(){
	var base = location.href;
	bs.Dom('#f').S( '@action', location.href );
});
</script>
</body>
</html>
