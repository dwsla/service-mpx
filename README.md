
=======
service-mpx
===========

A PHP 5.3 library to access MPX services from [The Platform](http://mpx.theplatform.com/) 

Requirements
============

* PHP 5.3+
* [Composer](https://getcomposer.org) (Recommended)

Installation
============

In your `composer.json`, add the following require directive:

```
{
    "require" : {

        // other requirements
        // here

        // this library
        "dwsla/service-mpx" : "master-dev"
    }
}
```

Then run:

```
$ composer update
```

Components
==========

MediaFeed
---------

@todo

Authentication
--------------

@todo

FeedConfig
----------

@todo

Development/Testing
===================

To run the unit-tests:

```
$ git clone git@github.com:dwsla/service-mpx
$ cd ./service-mpx
$ composer install --dev
$ ./bin/phpunit -c ./tests/config/phpunit.xml
```
