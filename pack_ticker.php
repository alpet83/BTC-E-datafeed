<?php
  include_once('lib/btc-e.api.php');
  include_once('lib/common.php');
  include_once('lib/config.php');
  include_once('lib/db_tools.php');
  error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
  
  // return false;

  set_time_limit(3000);
  
  $date = utc_time();
  $date_dir = "/var/www/ticker/".$date->format('Ymd');
  // check_mkdir($date_dir);

  $ts = $date->format('Y-m-d H:i:s');
  
  define ('id_field',     "int(11) unsigned NOT NULL AUTO_INCREMENT");
  define ('double_field', "double NOT NULL DEFAULT '0'");
  define ('float_field',  "float NOT NULL DEFAULT '0'");
  define ('dt_field',     "datetime NOT NULL");
  


  $commits = 0; // сколько добавленно в таблицу дифф
                       

  function pack_ticker()
  {
     global $mysqli;

     log_msg("pack_ticker begins");
     if (!$mysqli) init_db();
     $mysqli->select_db('ticker_history');
     

     $id_last = -1;

     $fields_std  = 'ticker_id,ts,updated,buy,sell,last,volume,volume_cur';
     $fields_prec = 'ROUND(buy,5),ROUND(sell,5),ROUND(last,5),ROUND(volume,5),ROUND(volume_cur,5)';


     $mysqli->try_query("TRUNCATE TABLE data__old");

     $ignored = 0;
     $added   = 0;
     $size = $mysqli->select_value('id', 'data__archive', 'ORDER BY id DESC') -
             $mysqli->select_value('id', 'data__archive', 'ORDER BY id');

     log_msg(" source size: $size rows");


     while($size > 0)
     {
        set_time_limit(300);

        // log_msg(" getting rows after $id_last ");
        $rows = select_from("id,$fields_std", 'data__archive', "WHERE id > $id_last LIMIT 10000");
        if (!$rows || 0 == $rows->num_rows) break;

        // log_msg(" result rows: {$rows->num_rows}");

        $pair_last = array('', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '');
        $lines = array();

        $sum = $added + $ignored;
        $progress = 100.0 * $sum / $size;
        printf(" complete %.1f%%, added [$added], ignored [$ignored]     \r", $progress);

        while ($row = $rows->fetch_array(MYSQL_NUM))
        {
          $id_last   = $row[0];
          $ticker_id = $row[1];
          $last = $pair_last[$ticker_id];
          $new = sprintf("%.4f,%.4f,%.4f,%.4f,%.4f", $row[4], $row[5], $row[6], $row[7], $row[8]);
          if ($last != $new)
          {
             $ts        = $row[2];
             $updated   = $row[3];
             $pair_last[$ticker_id] = $new;
             $lines []= "($ticker_id,'$ts','$updated',$new)";
          }
          else
             $ignored ++;
        }

        // log_msg(" after cycle added [$added], ignored [$ignored]");
        if (count($lines) > 0)
        {
          // log_msg(" inserting lines: ".count($lines));
          $insert = "INSERT INTO data__old ($fields_std) VALUES\n";
          $insert .= implode($lines, ",\n");


          if ($mysqli->try_query($insert))
              $added += count($lines);
          else
          {
              if (0 == $added)
                   echo (" \n sample add {$lines[0]},{$lines[1]} \n");
              die(" insert failed!");
          }
        }

     }  // while true
     log_msg(" TRANSFER COMPLETE! ");
	}


  $today = date("Y-m-d H:i:s");
  pack_ticker();
  if ($mysqli) $mysqli->close();
?>
