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
        // If enabled, the merged configuration will be cached and used in
        // subsequent requests.
        // 'config_cache_enabled' => 1,

        // The key used to create the configuration cache file name.
        // 'config_cache_key' => $stringKey,

        // Whether or not to enable a module class map cache.
        // If enabled, creates a module class map cache which will be used
        // by in future requests, to reduce the autoloading process.
        // 'module_map_cache_enabled' => 1,

        // The key used to create the class map cache file name.
        // 'module_map_cache_key' => 1,

        // The path in which to cache merged configuration.
        // 'cache_dir' => 'data/cache/',

        // Whether or not to enable modules dependency checking.
        // Enabled by default, prevents usage of modules that depend on other modules
        // that weren't loaded.
        // 'check_dependencies' => true,
    ),
);
