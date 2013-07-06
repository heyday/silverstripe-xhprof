<?php
/**
 * HeydayXhprof
 *
 * @category SilverStripe_Module
 * @package  Heyday
 * @author   Cam Spiers <cameron@heyday.co.nz>
 * @license  http://www.opensource.org/licenses/MIT MIT
 * @link     http://heyday.co.nz
 */

/**
 * HeydayXhprof acts as a wrapper to `xhprof` providing useful tools for
 * starting and stopping `xhprof` profiling.
 *
 * @category SilverStripe_Module
 * @package  Heyday
 * @author   Cam Spiers <cameron@heyday.co.nz>
 * @license  http://www.opensource.org/licenses/MIT MIT
 * @link     http://heyday.co.nz
 */
class HeydayXhprof
{
    /**
     * Stores default flags used in `xhprof_enable`
     */
    protected static $default_flags = false;
    /**
     * Stores the "app name"/profile name. Used in profile saving and
     * `HeydayXhprofRun` saving.
     */
    protected static $app_name = false;
    /**
     * Stores whether or not profiling is currently in progress
     */
    protected static $started = false;
    /**
     * Stores probability for use in profiling under load
     */
    protected static $probability = 1;
    /**
     * Stores request url based exclusions
     */
    protected static $exclusions = array(
        'xhprof_html'
    );

    /**
     * Set the probability for profiling under load
     *
     * @param int $probability The probability to be set.
     * Should be a from 0 to 1 inclusive
     *
     * @return null
     */
    public static function setProbability($probability)
    {

        self::$probability = round(max(min(1, $probability), 0), 6);

    }

    /**
     * Get Probability
     *
     * @return int
     */
    public static function getProbability()
    {
        return self::$probability;

    }

    /**
     * Test whether or not request should be profiled based on a probability.
     *
     * @param int $probability Optional probability to be set.
     *
     * @return bool
     */
    public static function testProbability($probability = null)
    {

        if ($probability) {

            self::setProbability($probability);

        }

        if (self::$probability) {

            $unit = pow(10, strlen(self::$probability));

            return mt_rand(1, $unit) <= self::$probability * $unit;

        } else {
            return false;

        }

    }

    /**
     * Add single url for exclusion
     *
     * @param string $exclusion Url to add to exclusion list
     *
     * @return null
     */
    public static function addExclusion($exclusion)
    {

        self::$exclusions[] = $exclusion;

    }

    /**
     * Add an array of urls for exclusion.
     *
     * @param array $exclusions An array of exclusions to be merged on
     * to the exclusions list
     *
     * @return null
     */
    public static function addExclusions(array $exclusions)
    {

        self::$exclusions = array_merge(self::$exclusions, $exclusions);

    }

    /**
     * Set exclusions
     *
     * @param array $exclusions An array of exclusions to set to the
     * exclusions list
     *
     * @return null
     */
    public static function setExclusions(array $exclusions)
    {

        self::$exclusions = $exclusions;

    }

    /**
     * Get exclusions
     *
     * @return array
     */
    public static function getExclusions()
    {
        return self::$exclusions;

    }

    /**
     * Check is url is excluded
     *
     * @param string $url A url to check if it is excluded by the
     * exclusions list
     *
     * @return bool
     */
    public static function isExcluded($url)
    {

        foreach (self::$exclusions as $exclusion) {

            if (stripos($url, $exclusion) !== false) {
                return true;

            }

        }

        return false;

    }

    /**
     * Check if we are allowed to profile based on url. If allowed by url,
     * test probability.
     *
     * @param string $url A url to check if it is excluded by the
     * exclusions list
     *
     * @return bool
     */
    public static function isAllowed($url)
    {
        return !self::isExcluded($url) && self::testProbability();

    }

    /**
     * Start the profiling.
     *
     * @param boolean $app_name The "app name" for the profile save and Run save
     *
     * @param boolean $flags Flags for xhprof_enable call
     *
     * @return null
     */
    public static function start($app_name = false, $flags = false)
    {

        if (extension_loaded('xhprof')) {

            if (self::$started) {

                user_error('You have already started xhprof');

            }

            include_once __DIR__ . '/ThirdParty/xhprof_lib/utils/xhprof_lib.php';
            include_once __DIR__ . '/ThirdParty/xhprof_lib/utils/xhprof_runs.php';

            xhprof_enable($flags !== false ? $flags : self::getDefaultFlags());

            self::$started = true;

            if ($app_name) {

                self::setAppName($app_name);

            }

        } else {

            user_error('Xhprof extension not loaded');

        }

    }

    /**
     * End the current profiling.
     *
     * @return null
     */
    public static function end()
    {

        if (extension_loaded('xhprof')) {

            if (!self::$started) {

                user_error('You haven\'t started a profile');

            }

            $appName = self::getAppName();

            $app = HeydayXhprofApp::get($appName);

            $xhprof_data = xhprof_disable();

            $xhprof_runs = new XHProfRuns_Default();

            $run_id = $xhprof_runs->save_run($xhprof_data, $app->safeName());

            if (class_exists('ClassInfo') && ClassInfo::exists('HeydayXhprofRun')) {

                $request = self::getRequest();

                if ($request instanceof SS_HTTPRequest) {

                    $requestVars = $request->requestVars();
                    unset($requestVars['url']);

                    $xhprofRun = new HeydayXhprofRun(
                        array(
                            'Run' => $run_id,
                            'AppID' => $app->ID,
                            'Url' => $request->getURL(),
                            'Method' => $request->httpMethod(),
                            'IP' => $request->getIP(),
                            'Params' => http_build_query($request->allParams(), '', "\n"),
                            'RequestVars' => http_build_query($requestVars, '', "\n"),
                            'RequestBody' => $request->getBody()
                        )
                    );

                }

            }

            $xhprofRun->write();

            self::$started = false;

        } else {

            user_error('Xhprof extension not loaded');

        }

    }

    /**
     * Get the current request as a SS_HTTPRequest object
     *
     * @return SS_HTTPRequest Request built from current request information
     */
    protected static function getRequest()
    {

        //Copied from Director::direct
        if (isset($_GET['url'])) {

            $url = $_GET['url'];

            // IIS includes get variables in url
            $position = strpos($url, '?');

            if ($position !== false) {

                $url = substr($url, 0, $position);

            }

        } else { // Lighttpd uses this

            if (strpos($_SERVER['REQUEST_URI'], '?') !== false) {

                list($url, $query) = explode('?', $_SERVER['REQUEST_URI'], 2);

                parse_str($query, $_GET);

                if ($_GET) {

                    $_REQUEST = array_merge((array) $_REQUEST, (array) $_GET);

                }

            } else {

                $url = $_SERVER['REQUEST_URI'];

            }

        }

        return new SS_HTTPRequest(
            isset($_SERVER['X-HTTP-Method-Override']) ? $_SERVER['X-HTTP-Method-Override'] : $_SERVER['REQUEST_METHOD'],
            $url,
            $_GET,
            array_merge((array) $_POST, (array) $_FILES),
            @file_get_contents('php://input')
        );

    }

    /**
     * Return default flags, if they don't exists then set some reasonable alternatives.
     *
     * @return int
     */
    public static function getDefaultFlags()
    {

        if (self::$default_flags === false) {

            self::$default_flags = XHPROF_FLAGS_CPU + XHPROF_FLAGS_MEMORY;

        }

        return self::$default_flags;

    }

    /**
     * [Set default flags for use in profiling
     *
     * @param int $flags Flags to set
     *
     * @return null
     */
    public static function setDefaultFlags($flags)
    {

        self::$default_flags = $flags;

    }

    /**
     * Get the app name for profile saving, if it doesn't exist then set it the SilverStripe project name
     *
     * @return string The app name
     */
    public static function getAppName()
    {

        if (self::$app_name == false) {

            global $project;

            self::$app_name = $project;

        }

        return self::$app_name;

    }

    /**
     * Set the app name for profile saving and run saving.
     *
     * @param string $app_name App name to set
     *
     * @return null
     */
    public static function setAppName($app_name)
    {

        self::$app_name = $app_name;

    }

    /**
     * Tests if profiling has been started
     *
     * @return boolean Started
     */
    public static function isStarted()
    {
        return self::$started;

    }

    /**
     * Remove any HeydayXhprofRuns if the corresponding profile is missing from the `tmp` directory.
     *
     * @param int $appID App id
     *
     * @return null
     */
    public static function removeMissing($appID = null)
    {

        $dir = realpath(ini_get('xhprof.output_dir'));

        if ($dir) {

            $runs = DataObject::get('HeydayXhprofRun', $appID ? "AppID = '$appID'" : null);

            if ($runs instanceof DataObjectSet) {

                foreach ($runs as $run) {

                    $filename = $dir . '/' . $run->Run . '.' . $run->App()->safeName();

                    if (!file_exists($filename)) {

                        $run->delete();

                    }

                }

            }

        }

    }

}
