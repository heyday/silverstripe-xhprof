<?php
/**
 * HeydayXhprofApp
 *
 * @category SilverStripe_Module
 * @package  Heyday
 * @author   Cam Spiers <cameron@heyday.co.nz>
 * @license  http://www.opensource.org/licenses/MIT MIT
 * @link     http://heyday.co.nz
 */

/**
 * HeydayXhprofApp stores apps in the database.
 *
 * @category SilverStripe_Module
 * @package  Heyday
 * @author   Cam Spiers <cameron@heyday.co.nz>
 * @license  http://www.opensource.org/licenses/MIT MIT
 * @link     http://heyday.co.nz
 */
class HeydayXhprofRun extends DataObject
{

    /**
     * Database fields
     * @var array
     */
    private static $db = array(
        'Run'         => 'Varchar(255)',
        'Url'         => 'Text',
        'Method'      => "Enum('GET,POST,PUT,DELETE','GET')",
        'IP'          => 'Varchar(64)',
        'Params'      => 'Text',
        'RequestVars' => 'Text',
        'RequestBody' => 'Text'
    );

    /**
     * Has one fields
     * @var array
     */
    private static $has_one = array(
        'App' => 'HeydayXhprofApp'
    );

    /**
     * Default way to sort
     * @var string
     */
    private static $default_sort = 'Created DESC';

    private static $summary_fields = array(
        'view'        => 'View',
        'Created'     => 'Created',
        'Url'         => 'Url',
        'Run'         => 'Run ID',
        'Method'      => 'Method',
        'IP'          => 'IP',
        'Params'      => 'Params',
        'RequestVars' => 'RequestVars',
        'RequestBody' => 'RequestBody'
    );

    /**
     * Link for viewing run
     *
     * @return string
     */
    public function view()
    {
        return <<<LINK
<a href="/vendor/facebook/xhprof/xhprof_html/index.php?run=$this->Run&source={$this->App()->safeName()}&sort=wt" target="_blank">View</a>
LINK;

    }

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->addFieldToTab(
            'Root.Main',
            new LiteralField(
                'view',
                $this->view()
            ),
            'Run'
        );

        return $fields;
    }

}
