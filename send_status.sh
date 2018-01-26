#!/bin/sh

SERVER=monitor.lan
MINER=`cat /etc/hostname`

echo "OK" > /tmp/info_status.log

ls /config/*miner.conf > /tmp/app
APP=`grep -o [bcg].miner /tmp/app`
echo local MINER=$MINER
echo using APP=$APP


while true; do
  sleep 1
  ping -q -c1 $SERVER 
  if [ $? -eq 0 ] 
  then
   echo "#OK: monitor.lan accessible!" 
  else
   echo "#FATAL: unavailable server ".$SERVER
   continue
  fi

  /usr/sbin/wait.sh
  ps > /tmp/ps.list

  if grep -Fq "$APP" /tmp/ps.list
  then
   echo "OK: $APP runned, trying API request, asic = $MINER"
   $APP-api -o stats > /tmp/laststats
   if grep -Fq "Socket connect failed" /tmp/laststats
   then
     echo "#WARN: $APP in warmup state..."
     continue
   else
     ping -c2 $SERVER 
     curl -s -F "file=@/tmp/laststats;filename=laststats" http://$SERVER/miner.php?asic=$MINER > /tmp/info_status.log
     cat /tmp/info_status.log
   fi
  else
   if grep -Fq "single-board-test" /tmp/ps.list
   then
     echo "#WARN: performing board test now"
     echo "testing..." > /tmp/info_status.log
   else
     echo "#WARN_STRANGE: No mining apps runed " > /tmp/info_status.log
     /etc/init.d/$APP.sh start >> /var/log/miner_restart.log
     sleep 30
   fi
  fi

  if grep -Fq "WARN_HANG" /tmp/info_status.log
  then
   echo " trying restart miner !"
   curl -s "http://$SERVER/miner.php?asic=$MINER&event=Retart%20initiated"
   /etc/init.d/$APP.sh stop > /var/log/miner_restart.log
   sleep 30
   /etc/init.d/$APP.sh start >> /var/log/miner_restart.log
  else
    echo "#STATUS: all ok, mining stable"
  fi

done
