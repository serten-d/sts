#!/bin/bash
export XDEBUG_CONFIG="idekey=netbeans-xdebug";
php7.2 ./vendor/phpunit/phpunit/phpunit --stderr --verbose --debug --colors --bootstrap ./phpunit-bootstrap.php -c ./application/tests/phpunit.xml $1
