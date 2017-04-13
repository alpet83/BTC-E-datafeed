#!/bin/bash

while true
do
 echo `date `'  Starting dfeed.js...'
 node dfeed.js
 # > /var/www/dfeed.log 2> /var/www/dfeed.err
 # cat dfeed.log
 # cat dfeed.err
 sleep 10
done



