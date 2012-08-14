#Heyday xhprof

This module provides a SilverStripe-centric wrapper for the pecl package [xhprof](http://pecl.php.net/package/xhprof) and the [xhprof gui](https://github.com/facebook/xhprof).

##License

This project is licensed under an MIT license which can be found at `heyday-xhprof/LICENSE`

##Requirements

You will require [xhprof](http://pecl.php.net/package/xhprof) installed in php to use `heyday-xhprof`. In order to create call graphs through the `xhprof gui` you will also need [Graphviz](http://www.graphviz.org/).

##Installation

To install drop the `heyday-xhprof` directory into your SilverStripe root and run `/dev/build?flush=1`.

##How to use

You can use `heyday-xhprof` in two ways. As a global profiler or as a profiler of specific segments of code. Please note, you can't do both global profiling and local profiling in the same request.

###Global Profiling

####With Sake

Enable:

	./sake xhprof/globalprofile/enable

Disable:

	./sake xhprof/globalprofile/disable
	
####Without sake (you need to have ADMIN privedges)

Enable:

	http://localhost/xhprof/globalprofile/enable

Disable:

	http://localhost/xhprof/globalprofile/disable
	
Enabling global profiling edits your `.htaccess` file by adding two lines of code to the beginning, but `heyday-xhprof` makes a backup of your `.htaccess` which can be found in `heyday-xhprof/code/GlobalProfile/backup/`.

When you disable global profiling your `.htaccess` file will be restored from the backup.
	
###Local Profiling

To profile a specific segment of code you need to first ensure global profiling is disabled, and then you need to set up the requisite `HeydayXhprof::start()` and `HeydayXhprof::end()` calls.

```php
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

	HeydayXhprof::setProbability(2/3);
	
This example would make the probability of a profile being made `2 in 3`

	HeydayXhprof::setProbability(1/1000);

This example would make the probability of a profile being made `1 in 1000`

###Limiting local profiling by probability

	if (HeydayXhprof::testProbability(1/100)) {
	
		HeydayXhprof::start('Potentially Troublesome Code');
	
	}

	//Code to profile
	
	if (HeydayXhprof::isStarted()) {

		HeydayXhprof::end();
	
	}
	
###Excluding urls by partial matching (specifically strpos)

To exclude certain urls:

	HeydayXhprof::addExclusions(array(
		'/admin/xhprof/',
		'/Security/ping'
	));

##Unit Testing

If you have `phpunit` installed you can run `heyday-xhprof`'s unit tests to see if everything is functioning correctly.

###Running the unit tests

From the command line:
	
	./sake dev/tests/module/heyday-xhprof


From your browser:

	http://localhost/dev/tests/module/heyday-xhprof

##Contributing

###Code guidelines

This project follows the standards defined in:

* [PSR-1](https://github.com/pmjones/fig-standards/blob/psr-1-style-guide/proposed/PSR-1-basic.md)
* [PSR-2](https://github.com/pmjones/fig-standards/blob/psr-1-style-guide/proposed/PSR-2-advanced.md)

##Notes:

###OS X:

Installing xhprof with MAMP on OSX

	cd /Applications/MAMP/bin/php/php5.3.6

	mkdir include

	cd include

	wget http://www.php.net/get/php-5.3.6.tar.gz/from/this/mirror

	tar zxvf mirror

	mv php-5.3.6/ php

	cd php

	MACOSX_DEPLOYMENT_TARGET=10.7 CFLAGS="-arch i386 -arch x86_64 -g -Os -pipe -no-cpp-precomp" CCFLAGS="-arch i386 -arch x86_64 -g -Os -pipe" CXXFLAGS="-arch i386 -arch x86_64 -g -Os -pipe" LDFLAGS="-arch i386 -arch x86_64 -bind_at_load"

	export CFLAGS CXXFLAGS LDFLAGS CCFLAGS MACOSX_DEPLOYMENT_TARGET

	./configure CFLAGS="-arch i386" --with-config-file-path=/Applications/MAMP/bin/php/php5.3.6/bin/php-config

	cd ~/Downloads

	wget http://pecl.php.net/get/xhprof-0.9.2.tgz

	tar zxvf xhprof-0.9.2.tgz

	cd xhprof-0.9.2/extension

	phpize

	sudo MACOSX_DEPLOYMENT_TARGET=10.7 CFLAGS='-O3 -fno-common -arch i386 -arch x86_64' LDFLAGS='-O3 -arch i386 -arch x86_64' CXXFLAGS='-O3 -fno-common -arch i386 -arch x86_64' ./configure --with-php-config="/Applications/MAMP/bin/php/php5.3.6/bin/php-config"

	make

	make install


Then enable xhprof in `php.ini` make sure you create a tmp directory for xhprof.

	[xhprof]
	extension="/Applications/MAMP/bin/php/php5.3.6/lib/php/extensions/no-debug-non-zts-20090626/xhprof.so"
	xhprof.output_dir="/Applications/MAMP/tmp/xhprof"