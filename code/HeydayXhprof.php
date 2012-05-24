<?php

class HeydayXhprof
{

	static protected $default_flags = false;
	static protected $app_name = false;
	static protected $started = false;
	static protected $probability = 1;
	static protected $exclusions = array(
		'xhprof_html'
	);

	/**
	 * Should be a from 0 to 1 inclusive
	 */
	public static function set_probability($probability)
	{

		self::$probability = max(min(1, $probability), 0);

	}

	public static function test_probability($probability = null)
	{

		if (!$probability) {

			$probability = self::$probability;
			
		}

		$unit = pow(10, strlen($probability - (int) $probability) - 1);

		return mt_rand(1, $unit / $probability) <= $unit;

	}

	public static function add_exclusion($exclusion)
	{

		self::$exclusions[] = $exclusion;

	}

	public static function add_exclusions(array $exclusions)
	{

		self::$exclusions = array_merge(self::$exclusions, $exclusions);
		
	}

	public static function is_allowed($url)
	{

		foreach (self::$exclusions as $exclusion) {

			if (strpos($url, $exclusion) !== false) {

				return false;

			}

		}

		return self::test_probability();

	}

	public static function start($app_name = false, $flags = false)
	{

		if (extension_loaded('xhprof')) {

			if (self::$started) {

				user_error("You have already started xhprof");

			}

			require_once dirname(__FILE__) . '/ThirdParty/xhprof_lib/utils/xhprof_lib.php';
			require_once dirname(__FILE__) . '/ThirdParty/xhprof_lib/utils/xhprof_runs.php';

			xhprof_enable($flags !== false ? $flags : self::get_default_flags());

			self::$started = true;

			if ($app_name) {

				self::set_app_name($app_name);

			}

		}

	}

	public static function end()
	{

		if (extension_loaded('xhprof')) {

			if (!self::$started) {

				user_error("You haven't started a profile");

			}

			$appName = self::get_app_name();

			$xhprof_data = xhprof_disable();

			$xhprof_runs = new XHProfRuns_Default();

			$app = HeydayXhprofApp::get($appName);

			$run_id = $xhprof_runs->save_run($xhprof_data, $app->SafeName());

			if (class_exists('ClassInfo') && ClassInfo::exists('HeydayXhprofRun')) {

				//Copied from Director::direct
				if (isset($_GET['url'])) {
					$url = $_GET['url'];
					// IIS includes get variables in url
					$i = strpos($url, '?');
					if($i !== false) {
						$url = substr($url, 0, $i);
					}
					
				// Lighttpd uses this
				} else {
					if(strpos($_SERVER['REQUEST_URI'],'?') !== false) {
						list($url, $query) = explode('?', $_SERVER['REQUEST_URI'], 2);
						parse_str($query, $_GET);
						if ($_GET) $_REQUEST = array_merge((array)$_REQUEST, (array)$_GET);
					} else {
						$url = $_SERVER["REQUEST_URI"];
					}
				}

				$request = new SS_HTTPRequest(
					(isset($_SERVER['X-HTTP-Method-Override'])) ? $_SERVER['X-HTTP-Method-Override'] : $_SERVER['REQUEST_METHOD'],
					$url, 
					$_GET, 
					array_merge((array)$_POST, (array)$_FILES),
					@file_get_contents('php://input')
				);

				if ($request instanceof SS_HTTPRequest) {

					$requestVars = $request->requestVars();
					unset($requestVars['url']);

					$xhprofRun = new HeydayXhprofRun(array(
						'Run' => $run_id,
						'AppID' => $app->ID,
						'Url' => $request->getURL(),
						'Method' => $request->httpMethod(),
						'IP' => $request->getIP(),
						'Params' => http_build_query($request->allParams(), '', "\n"),
						'RequestVars' => http_build_query($requestVars, '', "\n"),
						'RequestBody' => $request->getBody()
					));

				}

			}

			$xhprofRun->write();

			self::$started = false;

		}

	}

	public static function get_default_flags()
	{

		if (self::$default_flags === false) {

			self::$default_flags = XHPROF_FLAGS_CPU + XHPROF_FLAGS_MEMORY;

		}

		return self::$default_flags;

	}

	public static function set_default_flags($flags)
	{

		self::$default_flags = $flags;

	}

	public static function get_app_name()
	{

		if (self::$app_name == false) {

			global $project;

			self::$app_name = $project;

		}

		return self::$app_name;

	}

	public static function set_app_name($app_name)
	{

		self::$app_name = $app_name;

	}

	public static function is_started()
	{

		return self::$started;

	}

	public static function remove_missing($appID = null)
	{

		$dir = realpath(ini_get("xhprof.output_dir"));

		if ($dir) {

			$runs = $appID ? DataObject::get('HeydayXhprofRun', "AppID = '$appID'") : DataObject::get('HeydayXhprofRun');

			if ($runs instanceof DataObjectSet) {

				foreach ($runs as $run) {

					$filename = $dir . '/' . $run->Run . '.' . $run->App()->SafeName();

					if (!file_exists($filename)) {

						$run->delete();

					}

				}

			}

		}

	}

}