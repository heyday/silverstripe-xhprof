<?php

require_once dirname(__FILE__) . '/../HeydayXhprof.php';

if (file_exists(dirname(__FILE__) . '/../../../mysite/_config_xhprof.php')) {

	require_once dirname(__FILE__) . '/../../../mysite/_config_xhprof.php';

}

if (HeydayXhprof::is_allowed(isset($_GET['url']) ? $_GET['url'] : $_SERVER['SCRIPT_FILENAME'])) {

	HeydayXhprof::start('Global');

}
