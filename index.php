<?php
define( 'ID', 'front' );

//info
define( 'EXT', '.php' );
define( 'CONTROLLER_CLASS', 'Controller' );
define( 'DEFAULT_CONTROLLER', 'index'.EXT );
define( 'DEFAULT_METHOD', 'index' );
//path
define( 'ROOT', realpath('').'/' );
define( 'APP', ROOT.'app/' );
define( 'SYS', APP.'sys/' );
define( 'TABLE', APP.'db/table/' );
define( 'SQL', APP.'db/sql/' );
define( 'SITE', APP.'sites/'.ID.'/' );
define( 'CONTROLLER', SITE.'controller/' );
define( 'VIEW', SITE.'view/' );
define( 'CONFIG', SITE.'config'.EXT );

require_once SYS.'bsPHP.0.1'.EXT;
?>