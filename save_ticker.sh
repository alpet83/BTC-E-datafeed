#!/bin/bash

cd /var/www
while true
do
 php /var/www/save_ticker.php all
 sleep 1
done

