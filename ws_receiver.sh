#!/bin/bash

cd /var/www
# sudo touch /var/log/ws_receiver.log
# sudo chown alpet:alpet /var/log/ws_receiver.log

while true
do
 TS=`date '+%d.%m.%y %H:%M:%S'`
 echo $TS >> /var/log/ws_receiver.log
 echo $TS' Starting ws_receiver.php...'
 php ws_receiver.php
 sleep 5
done

 
