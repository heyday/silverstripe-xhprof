#Heyday xhprof

This module provides a SilverStripe-centric wrapper for the pecl package [xhprof](http://pecl.php.net/package/xhprof) and the [xhprof gui](https://github.com/facebook/xhprof).

For a version compatible with SilverStripe `2.4` see the `1.0` branch.

##License

This project is licensed under an MIT license which can be found at `silverstripe-xhprof/LICENSE`

##Requirements

You will require [xhprof](http://pecl.php.net/package/xhprof) installed in php to use `silverstripe-xhprof`. In order to create call graphs through the `xhprof gui` you will also need [Graphviz](http://www.graphviz.org/).

##Installation

To install drop the `silverstripe-xhprof` directory into your SilverStripe root and run `/dev/build?flush=1`.

##How to use

You can use `silverstripe-xhprof` in two ways. As a global profiler or as a profiler of specific segments of code. Please note, you can't do both global profiling and local profiling in the same request.

###Global Profiling

####With Sake

Enable:

	./sake xhprof/enable

Disable:

	./sake xhprof/disable

####Without sake (you need to have ADMIN privedges)

Enable:

	http://localhost/xhprof/enable

Disable:

	http://localhost/xhprof/disable

Enabling global profiling edits your `.htaccess` file by adding two lines of code to the beginning, but `silverstripe-xhprof` makes a backup of your `.htaccess` which can be found in `silverstripe-xhprof/code/GlobalProfile/backup/`.

When you disable global profiling your `.htaccess` file will be restored from the backup.

###Local Profiling

To profile a specific segment of code you need to first ensure global profiling is disabled, and then you need to set up the requisite `HeydayXhprof::start()` and `HeydayXhprof::end()` calls.

```php
<?php
HeydayXhprof::start('Potentially Troublesome Code');

//Code to profile

HeydayXhprof::end();
```

##Viewing saved profiles

For each profile made, there is a corresponding database record (`HeydayXhprofRun`) created. These database records store information about the request (url, query string etc) that the profiling occured on, and also the identifier to the profile.

To view profiles saved go to:

	http://localhost/admin/xhprof/

All global profiles are saved under the `App` name of `Global`.

##Configuation

There are a couple of configuation options available when profiling. Global config options can be set in a php file located at:

	./mysite/_config_xhprof.php

When global profiling is enabled, this file (if it exists) is included before any `SilverStripe` code is included.

###Limiting global profiling by probability

To limit requests profiled you can use a probability. This useful for profiling on live server under load.
```php
<?php
HeydayXhprof::setProbability(2/3);
```

This example would make the probability of a profile being made `2 in 3`
```php
<?php
HeydayXhprof::setProbability(1/1000);
```

This example would make the probability of a profile being made `1 in 1000`

###Limiting local profiling by probability
```php
<?php
if (HeydayXhprof::testProbability(1/100)) {

	HeydayXhprof::start('Potentially Troublesome Code');

}

//Code to profile

if (HeydayXhprof::isStarted()) {

	HeydayXhprof::end();

}
```
###Excluding urls by partial matching (specifically strpos)

To exclude certain urls:
```php
<?php
HeydayXhprof::addExclusions(array(
	'/admin/xhprof/',
	'/Security/ping'
));
```

##Unit Testing

If you have `phpunit` installed you can run `silverstripe-xhprof`'s unit tests to see if everything is functioning correctly.

###Running the unit tests

From the command line:

	./sake dev/tests/module/silverstripe-xhprof


From your browser:

	http://localhost/dev/tests/module/silverstripe-xhprof

##Contributing

###Code guidelines

This project follows the standards defined in:

* [PSR-1](https://github.com/pmjones/fig-standards/blob/psr-1-style-guide/proposed/PSR-1-basic.md)
* [PSR-2](https://github.com/pmjones/fig-standards/blob/psr-1-style-guide/proposed/PSR-2-advanced.md)
