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
class HeydayXhprofApp extends DataObject
{

    /**
     * Database fields
     * @var array
     */
    public static $db = array(
        'Name' => 'Varchar(255)'
    );

    /**
     * Many many fields
     * @var array
     */
    public static $has_many = array(
        'Runs' => 'HeydayXhprofRun'
    );

    /**
     * Get an existing app by name, if it doesn't exist then create it.
     *
     * @param string $appName App name
     *
     * @return HeydayXhprofApp
     */
    public static function get($appName)
    {

        $obj = DataObject::get_one('HeydayXhprofApp', "Name = '$appName'");

        if (!$obj instanceof self) {

            $obj = new self;

            $obj->Name = $appName;

            $obj->write();

        }

        return $obj;

    }

    /**
     * Get the fields for this type of model
     *
     * @return FieldSet
     */
    public function getCMSFields()
    {

        $fields = new FieldSet(
            new TabSet(
                'Root',
                new Tab('Main')
            )
        );

        $fields->addFieldToTab('Root.Main', new LiteralField('RemoveMissing', "<p><button class='action'><a href='/xhprof/removemissing/$this->ID' style='color: inherit; text-decoration: inherit;'>Remove records with missing profile dumps</a></button><p>"));

        $fields->addFieldToTab(
            'Root.Main', $runs = new TableListField(
                'Runs',
                'HeydayXhprofRun',
                array(
                    'view' => 'View',
                    'Created' => 'Created',
                    'Url' => 'Url',
                    'Run' => 'Run ID',
                    'Method' => 'Method',
                    'IP' => 'IP',
                    'Params' => 'Params',
                    'RequestVars' => 'RequestVars',
                    'RequestBody' => 'RequestBody'
                ),
                "AppID = '$this->ID'"
            )
        );

        $runs->setShowPagination(true);

        return $fields;

    }

    /**
     * Returns a name safe for use in urls and filenames
     *
     * @return string
     */
    public function safeName()
    {

        $safeName = function_exists('mb_strtolower') ? mb_strtolower($this->Name) : strtolower($this->Name);
        $safeName = Object::create('Transliterator')->toASCII($safeName);
        $safeName = str_replace('&amp;', '-and-', $safeName);
        $safeName = str_replace('&', '-and-', $safeName);
        $safeName = ereg_replace('[^A-Za-z0-9]+', '-', $safeName);
        $safeName = ereg_replace('-+', '-', $safeName);
        $safeName = trim($safeName, '-');

        return $safeName;

    }

}
