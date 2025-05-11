<?php

spl_autoload_register(function($class_name) {
	$fragments = explode('\\', $class_name);

	if(0 < count($fragments) && 'Portalbox' == $fragments[0]) {
		$fragments[0] = __DIR__;

		require join(DIRECTORY_SEPARATOR, $fragments) . '.php';
	}
});
