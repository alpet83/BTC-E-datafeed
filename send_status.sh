#!/bin/sh

while true; do
  sleep 10
  ps aux > /tmp/ps.list

  miner=`grep -o [0-9]* /etc/hostname`

  if grep -Fq "bmminer" /tmp/ps.list
  then
   echo "OK: bmminer runned, trying API request, asic = $miner"
   bmminer-api -o stats > /tmp/laststats
   if grep -Fq "Socket connect failed" /tmp/laststats
   then
     echo "#WARN: bmminer in warmup state..."
     continue
   else
     curl -s -F "file=@/tmp/laststats;filename=laststats" http://alpet.me/miner.php?asic=$miner > /tmp/info_status.log
     cat /tmp/info_status.log
   fi
  else
   if grep -Fq "single-board-test" /tmp/ps.list
   then
     echo "#WARN: performing board test now"
     echo "testing..." > /tmp/info_status.log
   else
     echo "#WARN_HANG: No mining apps runed " > /tmp/info_status.log
   fi
  fi

  if grep -Fq "WARN_HANG" /tmp/info_status.log
  then
   echo " trying restart miner !"
   curl -s "http://alpet.me/miner.php?asic=$miner&event=Restart%20initiated"

   /etc/init.d/bmminer.sh stop > /var/log/stop.log
   screen -qdmS BitMiner sh -c '/usr/sbin/bmminer_job.sh'
   #  bmminer --bitmain-fan-ctrl --bitmain-fan-pwm 99  --version-file /usr/bin/compile_time --api-listen --default-config  > /var/log/mining.log
  else
    echo "#STATUS: all ok, mining stable"
  fi

done