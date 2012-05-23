<?php

class HeydayXhprofRun extends DataObject
{

	static $db = array(
		'Run' => 'Varchar(255)',
		'Url' => 'Text',
		'Method' => "Enum('GET,POST,PUT,DELETE','GET')",
		'IP' => 'Varchar(64)',
		'Params' => 'Text',
		'RequestVars' => 'Text',
		'RequestBody' => 'Text'
	);

	static $has_one = array(
		'App' => 'HeydayXhprofApp'
	);

	static $default_sort = 'Created DESC';

	public function View()
	{

		return <<<LINK
<a href="/heyday-xhprof/code/ThirdParty/xhprof_html/index.php?run=$this->Run&source={$this->App()->SafeName()}&sort=wt" target="_blank">View</a>
LINK;

	}

}