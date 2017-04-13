#!/bin/bash

cd /var/www/
while true
do
 php /var/www/save_trades.php all
 sleep 10
done
