<?php

if (file_exists(__DIR__.'/../../mysite/_config_xhprof.php')) {
    require_once __DIR__.'/../../mysite/_config_xhprof.php';
}

require_once __DIR__.'/../../vendor/autoload.php';

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
    const LINK_MODE_NONE = 0;
    const LINK_MODE_JS = 1;
    const LINK_MODE_LINK = 2;

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
     * @var int
     */
    protected static $link_mode = 1;
    /**
     * Stores request url based exclusions
     */
    protected static $exclusions = array(
        'xhprof_html'
    );
    /**
     * @param int $link_mode
     */
    public static function setLinkMode($link_mode)
    {
        if (in_array($link_mode, array(self::LINK_MODE_NONE, self::LINK_MODE_JS, self::LINK_MODE_LINK))) {
            self::$link_mode = $link_mode;
        } else {
            user_error('Unknown link mode');
        }
    }
    /**
     * @return int
     */
    public static function getLinkMode()
    {
        return self::$link_mode;
    }

    /**
     * Set the probability for profiling under load
     *
     * @param int $probability The probability to be set.
     *                         Should be a from 0 to 1 inclusive
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
     *                          to the exclusions list
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
     *                          exclusions list
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
     *                    exclusions list
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
     *                    exclusions list
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
     * @param boolean $flags    Flags for xhprof_enable call
     *
     * @return null
     */
    public static function start($app_name = false, $flags = false)
    {

        if (extension_loaded('xhprof')) {

            if (self::$started) {

                user_error('You have already started xhprof');

            }

            xhprof_enable($flags !== false ? $flags : self::getDefaultFlags());

            register_shutdown_function(array(__CLASS__, 'end'));

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

        if (extension_loaded('xhprof') && self::isStarted()) {

            self::$started = false;

            $xhprof_runs = new XHProfRuns_Default();

            $run_id = $xhprof_runs->save_run(xhprof_disable(), self::getAppName());

            switch (self::$link_mode) {
                case self::LINK_MODE_NONE:
                    break;
                case self::LINK_MODE_JS:
                    echo sprintf(
                        <<<JSCRIPT
<script>
var winName;
if (winName = window.prompt('Specify a window for xhprof to open in', '_blank')) {
    window.open('%s', winName).focus();
}
</script>
JSCRIPT
                        ,
                        self::getUrl($run_id),
                        self::getUrl($run_id)
                    );
                    break;
                case self::LINK_MODE_LINK:
                    echo self::getLink($run_id);
                    break;
            }

        } else {

            user_error('Xhprof extension not loaded');

        }

    }

    public static function getLink($run_id)
    {
        return sprintf(
            '<a href="%s" target="_blank">View xhprof run</a>',
            self::getUrl($run_id)
        );
    }

    public static function getUrl($run_id)
    {
        return sprintf(
            '/vendor/facebook/xhprof/xhprof_html/index.php?run=%s&source=%s&sort=wt',
            $run_id,
            self::getAppName()
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
     * Set default flags for use in profiling
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
        self::$app_name = urlencode($app_name);
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

}
