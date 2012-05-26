<?php

class HeydayXhprofAdmin extends ModelAdmin
{

	public static $managed_models = array(
		'HeydayXhprofApp'
	);

	static $url_segment = 'xhprof';

	static $menu_title = 'Xhprof';

	static $model_importers = array();

}
