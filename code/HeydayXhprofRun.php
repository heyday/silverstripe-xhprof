<?php

class HeydayXhprofRun extends DataObject
{

	static $db = array(
		'Run' => 'Varchar(255)'
	);

	static $has_one = array(
		'App' => 'HeydayXhprofApp'
	);

	static $summary_fields = array(
		'Run' => 'Run ID'
	);

	static $default_sort = 'Created DESC';

	public function View()
	{

		$source = $this->App()->Name;

		return <<<LINK
<a href="/xhprof/?run=$this->Run&source=$source&sort=wt" target="_blank">View</a>
LINK;

	}

}