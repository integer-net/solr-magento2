#!/usr/bin/env bash
set -e
set -x
echo memory_limit=-1 >> /usr/local/etc/php/php.ini
git checkout -b tmp
git add -A
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
mysqladmin -u$M2SETUP_DB_USER -p"$M2SETUP_DB_PASSWORD" -h$M2SETUP_DB_HOST create $M2SETUP_DB_NAME
/usr/local/bin/mage-setup
cd /srv/www
composer config http-basic.repo.magento.com $MAGENTO_REPO_PUBLIC_KEY $MAGENTO_REPO_PRIVATE_KEY
composer config repositories.solr-module vcs $MODULE_DIR
composer config repositories.solr-autosuggest vcs git@github.com:integer-net/solr-magento2-autosuggest.git
composer config repositories.solr-base vcs git@github.com:integer-net/solr-base.git
composer config repositories.solr-pro vcs git@github.com:integer-net/solr-pro.git
sed -i -e 's/"psr-4": {/"psr-4": {\n      "IntegerNet\\\\Solr\\\\": ["vendor\/integer-net\/solr-magento2\/main\/test\/unit", "vendor\/integer-net\/solr-magento2\/main\/test\/integration", "vendor\/integer-net\/solr-base\/test\/Solr" ],/g' composer.json
composer config minimum-stability dev
composer require integer-net/solr-magento2 dev-tmp --no-update
composer require --dev tddwizard/magento2-fixtures 0.3.0 --no-update
phpunit_version="$(composer info | grep "phpunit/phpunit " | awk '{ print $2 }')"
phpunit_minimum="5.7.0"
if [ "$(printf "$phpunit_minimum\n$phpunit_version" | sort -V | head -n1)" == "$phpunit_version" ] && [ "$phpunit_version" != "$phpunit_minimum" ]; then
    composer require --dev phpunit/phpunit ^5.7 --no-update
fi

composer update
sed -i -e "s/8983/$SOLR_CI_PORT_8983_TCP_PORT/g"  vendor/integer-net/solr-magento2/main/test/integration/_files/solr_config.dist.php
sed -i -e "s/localhost/$SOLR_CI_PORT_8983_TCP_ADDR/g" vendor/integer-net/solr-magento2/main/test/integration/_files/solr_config.dist.php
sed -i -e "s/solr-magento2-tests/core0/g" vendor/integer-net/solr-magento2/main/test/integration/_files/solr_config.dist.php
bin/magento module:enable IntegerNet_Solr
bin/magento setup:upgrade