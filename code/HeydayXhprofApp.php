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
    private static $db = array(
        'Name' => 'Varchar(255)'
    );

    /**
     * Many many fields
     * @var array
     */
    private static $has_many = array(
        'Runs' => 'HeydayXhprofRun'
    );

    /**
     * Get an existing app by name, if it doesn't exist then create it.
     *
     * @param string $appName App name
     *
     * @return HeydayXhprofApp
     */
    public static function getOne($appName)
    {
        $obj = self::get()->filter('Name', $appName)->First();

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
        $fields = parent::getCMSFields();

        $fields->addFieldToTab(
            'Root.Main',
            new LiteralField('RemoveMissing', "<p><button class='action'><a href='/xhprof/removemissing/$this->ID' style='color: inherit; text-decoration: inherit;'>Remove records with missing profile dumps</a></button><p>")
        );

        return $fields;
    }

    /**
     * Returns a name safe for use in urls and filenames
     *
     * @return string
     */
    public function safeName()
    {
        return URLSegmentFilter::create()->filter($this->Name);
    }

}
