<?php

class HeydayXhprofController extends Controller
{

    public static $url_segment = 'xhprof';
    public static $allowed_actions = array(
        'globalprofile',
        'removemissing'
    );

    public function init()
    {

        if ( !Director::is_cli() && !Permission::check( 'ADMIN' ) ) {

            user_error( 'No access allowed' );
            exit;

        }

        parent::init();

    }

    public function index()
    {

        echo implode( PHP_EOL, array(
            'Commands available:',
            'sake xhprof/globalprofile/enable',
            'sake xhprof/globalprofile/disable',
            'sake xhprof/removemissingprofiles'
        ) ), PHP_EOL;

        exit;

    }

    public function removemissing( $request )
    {

        HeydayXhprof::remove_missing( $request->param( 'ID' ) ? $request->param( 'ID' ) : null );

        Director::redirectBack();

    }

    public function globalprofile( $request )
    {

        $backupFileName = BASE_PATH . '/heyday-xhprof/code/GlobalProfile/backup/backup.htaccess';
        $htaccessFileName = BASE_PATH . '/.htaccess';

        switch ( $request->param( 'ID' ) ) {

            case 'disable';
                if ( file_exists( $backupFileName ) ) {
                    unlink( $htaccessFileName );
                    rename( $backupFileName, $htaccessFileName );
                }
                break;

            case 'enable':
            default:
                if ( !file_exists( $backupFileName ) ) {
                    rename( $htaccessFileName, $backupFileName );
                    file_put_contents( $htaccessFileName, $this->globalIncludes() . file_get_contents( $backupFileName ) );
                }
                break;

        }

    }

    public function globalIncludes()
    {

        $dir = realpath( dirname( __FILE__ ) . '/GlobalProfile' );

        return <<<HTACCESS
php_value auto_prepend_file $dir/Start.php
php_value auto_append_file $dir/End.php

HTACCESS;

    }

}
