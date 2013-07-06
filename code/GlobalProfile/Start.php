<?php

require_once __DIR__ . '/../HeydayXhprof.php';

if (file_exists(BASE_PATH.'/mysite/_config_xhprof.php')) {

    include_once BASE_PATH.'/mysite/_config_xhprof.php';

}

if (HeydayXhprof::isAllowed(isset($_GET['url']) ? $_GET['url'] : $_SERVER['SCRIPT_FILENAME'])) {
    HeydayXhprof::start('Global');
}
