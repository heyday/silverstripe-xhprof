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
 * HeydayXhprof acts as a wrapper to `xhprof` providing useful tools for starting and stopping `xhprof` profiling.
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
     * Stores the "app name"/profile name. Used in profile saving and `HeydayXhprofRun` saving.
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
     * @param int $probability The probability to be set. Should be a from 0 to 1 inclusive
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
     */
    public static function add_exclusion( $exclusion )
    {

        self::$exclusions[] = $exclusion;

    }

    /**
     * Add an array of urls for exclusion.
     */
    public static function add_exclusions( array $exclusions )
    {

        self::$exclusions = array_merge( self::$exclusions, $exclusions );

    }

    /**
     * Set exclusions
     */
    public static function set_exclusions( array $exclusions )
    {

        self::$exclusions = $exclusions;

    }

    /**
     * Get exclusions
     */
    public static function get_exclusions()
    {

        return self::$exclusions;

    }

    /**
     * Check is url is excluded
     */
    public function is_excluded( $url )
    {

        foreach ( self::$exclusions as $exclusion ) {

            if ( stripos( $url, $exclusion ) !== false ) {

                return true;

            }

        }

        return false;

    }

    /**
     * Check if we are allowed to profile based on url. If allowed by url, test probability.
     */
    public static function is_allowed( $url )
    {

        return !self::is_excluded($url) && self::testProbability();

    }

    /**
     * Start the profiling.
     */
    public static function start( $app_name = false, $flags = false )
    {

        if ( extension_loaded( 'xhprof' ) ) {

            if ( self::$started ) {

                user_error( 'You have already started xhprof' );

            }

            require_once dirname( __FILE__ ) . '/ThirdParty/xhprof_lib/utils/xhprof_lib.php';
            require_once dirname( __FILE__ ) . '/ThirdParty/xhprof_lib/utils/xhprof_runs.php';

            xhprof_enable( $flags !== false ? $flags : self::get_default_flags() );

            self::$started = true;

            if ( $app_name ) {

                self::set_app_name( $app_name );

            }

        } else {

            user_error( 'Xhprof extension not loaded' );

        }

    }

    /**
     * End the current profiling.
     */
    public static function end()
    {

        if ( extension_loaded( 'xhprof' ) ) {

            if ( !self::$started ) {

                user_error( 'You haven\'t started a profile' );

            }

            $appName = self::get_app_name();

            $app = HeydayXhprofApp::get( $appName );

            $xhprof_data = xhprof_disable();

            $xhprof_runs = new XHProfRuns_Default();

            $run_id = $xhprof_runs->save_run( $xhprof_data, $app->SafeName() );

            if ( class_exists( 'ClassInfo' ) && ClassInfo::exists( 'HeydayXhprofRun' ) ) {

                //Copied from Director::direct
                if ( isset( $_GET['url'] ) ) {

                    $url = $_GET['url'];

                    // IIS includes get variables in url
                    $i = strpos( $url, '?' );

                    if ( $i !== false ) {

                        $url = substr( $url, 0, $i );
                    }

                // Lighttpd uses this
                } else {

                    if ( strpos( $_SERVER['REQUEST_URI'],'?' ) !== false ) {

                        list( $url, $query ) = explode( '?', $_SERVER['REQUEST_URI'], 2 );

                        parse_str( $query, $_GET );

                        if ( $_GET ) {

                            $_REQUEST = array_merge( (array) $_REQUEST, (array) $_GET );

                        }

                    } else {

                        $url = $_SERVER['REQUEST_URI'];

                    }
                }

                $request = new SS_HTTPRequest(
                    isset( $_SERVER['X-HTTP-Method-Override'] ) ? $_SERVER['X-HTTP-Method-Override'] : $_SERVER['REQUEST_METHOD'],
                    $url,
                    $_GET,
                    array_merge( (array) $_POST, (array) $_FILES ),
                    @file_get_contents( 'php://input' )
                );

                if ( $request instanceof SS_HTTPRequest ) {

                    $requestVars = $request->requestVars();
                    unset( $requestVars['url'] );

                    $xhprofRun = new HeydayXhprofRun( array(
                        'Run' => $run_id,
                        'AppID' => $app->ID,
                        'Url' => $request->getURL(),
                        'Method' => $request->httpMethod(),
                        'IP' => $request->getIP(),
                        'Params' => http_build_query( $request->allParams(), '', "\n" ),
                        'RequestVars' => http_build_query( $requestVars, '', "\n" ),
                        'RequestBody' => $request->getBody()
                    ) );

                }

            }

            $xhprofRun->write();

            self::$started = false;

        } else {

            user_error( 'Xhprof extension not loaded' );

        }

    }

    /**
     * Return default flags, if they don't exists then set some reasonable alternatives.
     */
    public static function get_default_flags()
    {

        if ( self::$default_flags === false ) {

            self::$default_flags = XHPROF_FLAGS_CPU + XHPROF_FLAGS_MEMORY;

        }

        return self::$default_flags;

    }

    /**
     * Set default flags for use in profiling
     */
    public static function set_default_flags( $flags )
    {

        self::$default_flags = $flags;

    }

    /**
     * Get the app name for profile saving, if it doesn't exist then set it the SilverStripe project name
     */
    public static function get_app_name()
    {

        if ( self::$app_name == false ) {

            global $project;

            self::$app_name = $project;

        }

        return self::$app_name;

    }

    /**
     * Set the app name for profile saving and run saving.
     */
    public static function set_app_name( $app_name )
    {

        self::$app_name = $app_name;

    }

    /**
     * Tests if profiling has been started
     */
    public static function is_started()
    {

        return self::$started;

    }

    /**
     * Remove any HeydayXhprofRuns if the corresponding profile is missing from the `tmp` directory.
     */
    public static function remove_missing( $appID = null )
    {

        $dir = realpath( ini_get( 'xhprof.output_dir' ) );

        if ( $dir ) {

            $runs = $appID ? DataObject::get( 'HeydayXhprofRun', "AppID = '$appID'" ) : DataObject::get( 'HeydayXhprofRun' );

            if ( $runs instanceof DataObjectSet ) {

                foreach ( $runs as $run ) {

                    $filename = $dir . '/' . $run->Run . '.' . $run->App()->SafeName();

                    if ( !file_exists( $filename ) ) {

                        $run->delete();

                    }

                }

            }

        }

    }

}
