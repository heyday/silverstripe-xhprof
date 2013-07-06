<?php
/**
 * HeydayXhprofController
 *
 * @category SilverStripe_Module
 * @package  Heyday
 * @author   Cam Spiers <cameron@heyday.co.nz>
 * @license  http://www.opensource.org/licenses/MIT MIT
 * @link     http://heyday.co.nz
 */

/**
 * HeydayXhprofController Provides sake callable actions for the xprof module
 *
 * @category SilverStripe_Module
 * @package  Heyday
 * @author   Cam Spiers <cameron@heyday.co.nz>
 * @license  http://www.opensource.org/licenses/MIT MIT
 * @link     http://heyday.co.nz
 */
class HeydayXhprofController extends Controller
{

    /**
     * Allowed actions
     * @var array
     */
    public static $allowed_actions = array(
        'globalprofile',
        'removemissing'
    );

    /**
     * Init method to check permissions
     *
     * @return null
     */
    public function init()
    {
        if (!Director::is_cli() && !Permission::check('ADMIN')) {
            user_error('No access allowed');
            exit;
        }

        parent::init();
    }

    /**
     * Lists available commands
     *
     * @return null
     */
    public function index()
    {

        echo implode(
            PHP_EOL,
            array(
                'Commands available:',
                'sake xhprof/globalprofile/enable',
                'sake xhprof/globalprofile/disable',
                'sake xhprof/removemissingprofiles'
            )
        ), PHP_EOL;

        exit;

    }

    /**
     * Remove any runs where the profile is missing
     *
     * @param SS_HTTPRequest $request Request for action
     *
     * @return null
     */
    public function removemissing($request)
    {

        HeydayXhprof::removeMissing($request->param('ID') ? $request->param('ID') : null);

        Director::redirectBack();

    }

    /**
     * Enable or disable global profiling
     *
     * @param SS_HTTPRequest $request Request for action
     *
     * @return null
     */
    public function globalprofile($request)
    {

        $backupFileName = XHPROF_BASE_PATH . '/code/GlobalProfile/backup/backup.htaccess';
        $htaccessFileName = BASE_PATH . '/.htaccess';

        switch ($request->param('ID')) {

            case 'disable':
                if (file_exists($backupFileName)) {
                    unlink($htaccessFileName);
                    rename($backupFileName, $htaccessFileName);

                    return 'Done' . PHP_EOL;
                } else {
                    return "It appears that global profiling is not enabled as there is no backup file to restore from." . PHP_EOL;
                }
                break;

            case 'enable':
            default:
                if (!file_exists($backupFileName)) {
                    rename($htaccessFileName, $backupFileName);
                    file_put_contents($htaccessFileName, $this->globalIncludes() . file_get_contents($backupFileName));

                    return 'Done' . PHP_EOL;
                } else {
                    return "It appears that global profiling is already enabled as a backup file exists." . PHP_EOL;
                }
                break;

        }

    }

    /**
     * Gets content for .htaccess file based on project directory
     *
     * @return string
     */
    public function globalIncludes()
    {

        $dir = realpath(__DIR__ . '/GlobalProfile');

        return <<<HTACCESS
php_value auto_prepend_file $dir/Start.php
php_value auto_append_file $dir/End.php

HTACCESS;

    }

}
