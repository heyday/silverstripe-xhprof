<?php

require_once dirname(__FILE__) . '/../HeydayXhprof.php';

if (strpos($_SERVER['SCRIPT_FILENAME'], 'xhprof_html') === false) {

	HeydayXhprof::start('Global');
	
}