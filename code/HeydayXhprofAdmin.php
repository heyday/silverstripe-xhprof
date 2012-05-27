<?php

class HeydayXhprofAdmin extends ModelAdmin
{

    public static $managed_models = array(
        'HeydayXhprofApp'
    );

    public static $url_segment = 'xhprof';

    public static $menu_title = 'Xhprof';

    public static $model_importers = array();

}
