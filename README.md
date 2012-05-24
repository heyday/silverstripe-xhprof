#Heyday xhprof

This module provides a silverstripe-centric wrapper for the the pecl package [xhprof](http://pecl.php.net/package/xhprof) and the [xhprof gui](https://github.com/facebook/xhprof).

##Requirements

You will require [xhprof](http://pecl.php.net/package/xhprof) to use heyday-xhprof

##Installation

To install just drop the heyday-xhprof directory into your SilverStripe root and run /dev/build?flush=1

##How to use

You can use HeydayXhprof in two ways. As a global profiler or as a profiler of specific segments of code.

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
	
###Local Profiling

To profile a specific segment of code you need to first ensure global profiling is disabled, and then you need to set up the requisite HeydayXhprof::start() and HeydayXhprof::end() calls.
	
	HeydayXhprof::start('Potentially Troublesome While Loop');

	while (true) {

		//some code that could run slow

	}

	HeydayXhprof::end();
	
##Configuation

There are a couple of configuation options used for global profiling. These config options should be set in a php file located at:

	./mysite/_config_xhprof.php
	
###Limiting global profiling by probability

To limit requests profiled you can use a probability.

	HeydayXhprof::set_probability(2/3);
	
###Excluding urls by partial matching (specifically strpos)

To exclude certain urls:

	HeydayXhprof::add_exclusions(array(
		'/admin/xhprof/',
		'/Security/ping'
	));

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


Then enable xhprof in php.ini