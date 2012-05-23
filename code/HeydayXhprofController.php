<?php

class HeydayXhprofController extends Controller
{

	static $url_segment = 'xhprof';
	static $allowed_actions = array('index', 'callgraph');

	public function init()
	{

		if (!Director::is_cli() && !Permission::check('ADMIN')) {

			user_error('No access allowed');
			exit;

		}

		parent::init();

	}

	public function index()
	{

		error_reporting(E_NONE);
		ini_set('display_errors', false);

		require_once dirname(__FILE__) . '/ThirdParty/xhprof_html/index.php';

	}

	public function callgraph()
	{

		require_once dirname(__FILE__) . '/ThirdParty/xhprof_html/callgraph.php';

	}

}