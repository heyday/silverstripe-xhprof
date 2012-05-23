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
				'Url' => 'Url',
				'Run' => 'Run ID',
				'View' => 'View'
			),
			"AppID = '$this->ID'"
		));

		$runs->setShowPagination(true);

		return $fields;

	}

	public function SafeName()
	{

		$t = (function_exists('mb_strtolower')) ? mb_strtolower($this->Name) : strtolower($this->Name);
		$t = Object::create('Transliterator')->toASCII($t);
		$t = str_replace('&amp;','-and-',$t);
		$t = str_replace('&','-and-',$t);
		$t = ereg_replace('[^A-Za-z0-9]+','-',$t);
		$t = ereg_replace('-+','-',$t);
		$t = trim($t, '-');

		return $t;

	}

}