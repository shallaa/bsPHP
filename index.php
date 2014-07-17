<?php
define( 'ID', 'front' );

if( defined('STDIN') || ( empty($_SERVER['REMOTE_ADDR']) and !isset($_SERVER['HTTP_USER_AGENT'] ) and count($_SERVER['argv']) > 0) ) {
	$t0 = explode( '/', $_SERVER['argv'][0] );
	array_shift($t0);
	array_pop($t0);

	define( 'ROOT', '/'.implode("/", $t0).'/' );
	define( 'SHELL_MODE', TRUE );
} else {
	define( 'ROOT', realpath('').'/' );
	define( 'SHELL_MODE', FALSE );
}

require_once ROOT.'/app/bsPHP.0.1.php';
?>