#!/usr/bin/env bash
set -e
set -x
echo memory_limit=-1 >> /usr/local/etc/php/php.ini
git checkout -b tmp
git add -A
git config --global user.email "wercker@localhost"
git config --global user.name "Wercker"
git commit --allow-empty -m "tmp"
export MODULE_DIR=`pwd`
export M2SETUP_DB_HOST=$MYSQL_CI_PORT_3306_TCP_ADDR
export M2SETUP_DB_USER=root
export M2SETUP_DB_PASSWORD=$MYSQL_CI_ENV_MYSQL_ROOT_PASSWORD
export M2SETUP_DB_NAME=magento
export M2SETUP_BASE_URL=http://m2.localhost:8000/
export M2SETUP_ADMIN_FIRSTNAME=Admin
export M2SETUP_ADMIN_LASTNAME=User
export M2SETUP_ADMIN_EMAIL=dummy@example.com
export M2SETUP_ADMIN_USER=magento2
export M2SETUP_ADMIN_PASSWORD=magento2
export M2SETUP_VERSION=$1
export M2SETUP_USE_SAMPLE_DATA=false
export M2SETUP_USE_ARCHIVE=true
export COMPOSER_HOME=$WERCKER_CACHE_DIR/composer
BIN_MAGENTO=magento-command

# Reconfigure composer after COMPOSER_HOME has been changed
[ ! -z "${COMPOSER_MAGENTO_USERNAME}" ] && \
    composer config -a -g http-basic.repo.magento.com $COMPOSER_MAGENTO_USERNAME $COMPOSER_MAGENTO_PASSWORD

mysqladmin -u$M2SETUP_DB_USER -p"$M2SETUP_DB_PASSWORD" -h$M2SETUP_DB_HOST create $M2SETUP_DB_NAME
DEBUG=true magento-installer
cd /var/www/magento
composer config repositories.solr-module '{"type":"path", "url":"'$MODULE_DIR'", "options":{"symlink":false}}'
composer config repositories.solr-autosuggest vcs git@github.com:integer-net/solr-magento2-autosuggest.git
composer config repositories.solr-base vcs git@github.com:integer-net/solr-base.git
composer config repositories.solr-pro vcs git@github.com:integer-net/solr-pro.git
sed -i -e 's/"psr-4": {/"psr-4": {\n      "IntegerNet\\\\Solr\\\\": ["vendor\/integer-net\/solr-magento2\/main\/test\/unit", "vendor\/integer-net\/solr-magento2\/main\/test\/integration", "vendor\/integer-net\/solr-base\/test\/Solr" ],/g' composer.json
composer require integer-net/solr-magento2 @dev --no-update
composer require --dev tddwizard/magento2-fixtures 0.8.0@dev --no-update
phpunit_version="$(composer info | grep "phpunit/phpunit " | awk '{ print $2 }')"
phpunit_minimum="5.7.0"
if [ "$(printf "$phpunit_minimum\n$phpunit_version" | sort -V | head -n1)" == "$phpunit_version" ] && [ "$phpunit_version" != "$phpunit_minimum" ]; then
    composer require --dev phpunit/phpunit ^5.7 --no-update
fi

composer update
sed -i -e "s/8983/$SOLR_CI_PORT_8983_TCP_PORT/g"  vendor/integer-net/solr-magento2/main/test/integration/_files/solr_config.dist.php
sed -i -e "s/localhost/$SOLR_CI_PORT_8983_TCP_ADDR/g" vendor/integer-net/solr-magento2/main/test/integration/_files/solr_config.dist.php
sed -i -e "s/solr-magento2-tests/core0/g" vendor/integer-net/solr-magento2/main/test/integration/_files/solr_config.dist.php
$BIN_MAGENTO module:enable IntegerNet_Solr
$BIN_MAGENTO setup:di:compile