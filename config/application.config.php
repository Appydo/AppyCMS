<?php
if(isset($_SERVER["SERVER_NAME"])) {
    $host = str_replace('www.', '', $_SERVER["SERVER_NAME"]);
} else {
    $host = 'localhost';
}
if (file_exists(__DIR__.'/'.$host.'.php')) {
    $config = 'config/'.$host.'.php';
} else {
    $config = 'config/default.php';
}
return array(
    'version' => '0.4',
    'modules' => array(
	'Console',
        'Index',
        'Admin',
	),
    'module_listener_options' => array(
        'config_glob_paths'    => array(
            $config,
        ),
        'module_paths' => array(
            './module',
            './plugin',
            './vendor',
        ),
    ),
);
