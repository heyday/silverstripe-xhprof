<?php

class HeydayXhprofApp extends DataObject
{

	static $db = array(
		'Name' => 'Varchar(255)'
	);

	static $has_many = array(
		'Runs' => 'HeydayXhprofRun'
	);

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

	public function getCMSFields()
	{

		$fields = new FieldSet(
			new TabSet(
				'Root',
				new Tab('Main')
			)
		);

		$fields->addFieldToTab('Root.Main', $runs = new TableListField(
			'Runs',
			'HeydayXhprofRun',
			array(
				'Run' => 'Run ID',
				'View' => 'View'
			),
			"AppID = '$this->ID'"
		));

		$runs->setShowPagination(true);

		return $fields;

	}

}