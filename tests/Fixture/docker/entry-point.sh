#!/bin/sh

service mariadb start
service redis-server start
service memcached start
service mongod start


mysql -uroot -e 'CREATE DATABASE shieldon_unittest;'
mysql -uroot -e "CREATE USER 'shieldon'@'localhost' IDENTIFIED BY 'taiwan';"		
mysql -uroot -e "GRANT ALL ON shieldon_unittest.* TO 'shieldon'@'localhost';"

service --status-all
cd /test-app
ls -al
composer test


