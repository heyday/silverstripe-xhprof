<?php
/**
 * HeydayXhprofAdmin
 *
 * @category SilverStripe_Module
 * @package  Heyday
 * @author   Cam Spiers <cameron@heyday.co.nz>
 * @license  http://www.opensource.org/licenses/MIT MIT
 * @link     http://heyday.co.nz
 */

/**
 * HeydayXhprofAdmin provides and admin interface for view `Apps` and `Runs`
 * 
 * @category SilverStripe_Module
 * @package  Heyday
 * @author   Cam Spiers <cameron@heyday.co.nz>
 * @license  http://www.opensource.org/licenses/MIT MIT
 * @link     http://heyday.co.nz
 */
class HeydayXhprofAdmin extends ModelAdmin
{

    /**
     * List of models managed by this admin
     * @var array
     */
    public static $managed_models = array(
        'HeydayXhprofApp'
    );

    /**
     * Url segment for this admin
     * @var string
     */
    public static $url_segment = 'xhprof';

    /**
     * Title of the tab for this admin
     * @var string
     */
    public static $menu_title = 'Xhprof';

    /**
     * No modle importers
     * @var array
     */
    public static $model_importers = array();

}
