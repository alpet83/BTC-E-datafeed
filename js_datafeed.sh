#!/bin/bash

PATH=/root/.nvm/versions/node/v7.8.0/bin:$PATH

echo 'PATH='$PATH
while true
do
 echo `date `'  Starting dfeed.js...'
 cd /var/www
 node dfeed.js
 # > /var/www/dfeed.log 2> /var/www/dfeed.err
 # cat dfeed.log
 # cat dfeed.err
 sleep 10
done



