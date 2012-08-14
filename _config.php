<?php

define('XHPROF_BASE_PATH', __DIR__);

Director::addRules( 70, array(
    HeydayXhprofController::$url_segment . '//$Action/$ID/$OtherID' => 'HeydayXhprofController'
) );
