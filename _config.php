<?php

Director::addRules( 70, array(
	HeydayXhprofController::$url_segment . '//$Action/$ID/$OtherID' => 'HeydayXhprofController'
) );
