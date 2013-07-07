<?php

require_once __DIR__ . '/../HeydayXhprof.php';

if (HeydayXhprof::isAllowed(isset($_GET['url']) ? $_GET['url'] : $_SERVER['SCRIPT_FILENAME'])) {
    HeydayXhprof::start('Global');
}
