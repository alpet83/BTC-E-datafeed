<html>
<head>
<title>Optimizator</title>
 <style type=text/css>
 .red {
   color:red;
 }

 </style>

 <script type="text/javascript">

 function onLoad()
 {
   // setTimeout("document.location.reload(true);", 1000);
 }

 </script>

</head>
<body onLoad="onLoad()">
<pre>
<?php
  $dt = new DateTime();

  echo("script start time: ".$dt->format("H:i:s")."\n");

  include_once('common.php');
  include_once('config.php');
  include_once('lib/db_tools.php');

  $link = false;
  set_time_limit(120);

  $double_field = "double NOT NULL DEFAULT '0'";
  $float_field  = "float NOT NULL DEFAULT '0'";


  $depth_fields = array('id' => 'int(11) unsigned NOT NULL AUTO_INCREMENT');
  $depth_fields['pair_id']  = 'int(11) unsigned NOT NULL';
  $depth_fields['ts'] = 'timestamp NOT NULL';
  $depth_fields['price']   = $double_field;
  $depth_fields['volume']  = $double_field;
  $depth_fields['flags']   = 'int(11) NOT NULL';

  function err_msg($msg, $info)
  {
    if ($info)
       log_msg("<b class=red>#ERROR: $msg $info</b>");
    else
       log_msg("<b class=red>#ERROR: $msg</b>");
  }



  $pairs_ids = array();

  function scan_dates($table, $prefix, $id, $max_id, $inc)
  {
     $last = "?";

     if ($id < 0) $id = 0;
     if ($id >= $max_id) return;

     $prv  = $id;
     $start = $id;
     $dst_table = $table . '_map';
     // $max_id = $id + $inc * 10;

     while ($id <= $max_id)
     {
        $query = "SELECT id, DATE_FORMAT(ts, '%Y-%m'), ts FROM depth_history.$table\n WHERE (id = $id) ";

        $result = mysql_query($query);

        $row = mysql_fetch_array ( $result, MYSQL_NUM );

        $ts = $row[1];
        if ($last != $ts)
        {
           if ($last != '?')
           {
              printf("$prefix %d = [%s] -> %d = [%s] \n ", $prv, $last, $row[0], $row[2]);
              if ($inc > 1)
                  scan_dates($table, "$prefix ", $id - $inc, $id, $inc / 10 );
              else
              {  // registering id
                 $query = "DELETE FROM $dst_table\n";
                 $query .= "WHERE ref_id > $id \n";
                 mysql_query($query);

                 $query = "INSERT INTO $dst_table (ref_id, ts)\n";
                 $query .= "SELECT id, ts FROM $table\n";
                 $query .= "WHERE id = $id\n";

                 $r = mysql_query($query);
                 if ($r)
                     printf("$prefix added into $dst_table rows = %d \n", mysql_affected_rows());
                 else
                     err_msg(" failed [$query] error: ", mysql_error());

              }
           }
           $last = $ts;
        }

        $prv = $id;
        $id += $inc;
     } // while

  }


  function update_map($pair, $suffix)
  {
    $src_table = sprintf('%s_%s', $pair, $suffix);
    $dst_table = sprintf('%s_map', $src_table);
    mysql_query("USE depth_history");
    mysql_query("SET sql_mode = ''");

    $query = "CREATE TABLE IF NOT EXISTS `$dst_table`\n";
    $query .= "( `id` int(11) unsigned NOT NULL AUTO_INCREMENT,\n";
    $query .= " `ts` timestamp NOT NULL,\n";
    $query .= " `ref_id` int(11) unsigned NOT NULL,\n";
    $query .= " PRIMARY KEY (`id`),\n";
    $query .= " KEY `TIMESTAMP` (`ts`)\n";
    $query .= ")\n ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;";
    $result = mysql_query($query);
    if (!$result)
    {
        err_msg("failed create table via query:\n $query \nError message: ", mysql_error());
        return false;
    }


    $query = "SELECT id,ts FROM $src_table ORDER BY id DESC LIMIT 1";
    $result = mysql_query($query);
    if ($result)
    {
       $row = mysql_fetch_array ( $result, MYSQL_NUM );
       $max_id = $row[0];
       log_msg(" begin parsing.");
       printf("last id %d, ts = '%s' \n", $max_id, $row[1]);
       $id = 0;
       scan_dates($src_table, ' ', 1, $max_id, 1000000);

       log_msg(" complete parsing...");
    }
    else
       err_msg("query [$query] failed: ", mysql_error());


    // detect last id
    $query = "SELECT ref_id FROM $dst_table\n";
    $query .= "ORDER BY ref_id DESC\n";
    $query .= "LIMIT 1;\n";
    $result = mysql_query($query);
    if (!$result)
    {
       err_msg("failed select query: ", mysql_error());
       return false;
    }
    printf("select returned rows:<b> %d</b>\n", mysql_affected_rows());

    $row = mysql_fetch_array ( $result, MYSQL_NUM );
    $id  = 0;
    if ($row)
        $id = $row[0];

    log_msg(" last id in [$dst_table] = $id ");
    if ($id == 0)
    {  // very slow update map
       $query = "INSERT INTO $dst_table (ref_id, ts)\n";
       $query .= "SELECT id, ts FROM $src_table\n";
       $query .= "WHERE id > $id\n";
       $query .= "GROUP BY CONCAT (YEAR(ts), '-', MONTH(ts))\n";
       $query .= "ORDER BY $src_table.id;\n";
       log_msg("<br><b style='color:#005050'>$query</b>");
       $result = mysql_query($query);
       if (!$result)
       {
          err_msg("failed insert query: ", mysql_error());
          return false;
       }
       log_msg( sprintf("added rows: %d", mysql_affected_rows()) );
    }

    $query = "SELECT ref_id,ts FROM $dst_table\n";
    $query .= "ORDER BY ref_id\n";
    $result = mysql_query($query);
    if (!$result)
    {
       err_msg("failed select query: ", mysql_error());
       return false;
    }

    while ($row = mysql_fetch_array ( $result, MYSQL_NUM ))
    {
       printf("id of %s = %d \n", $row[1], $row[0]);
    }
  }


  function transfer_depth($pair, $suffix)
  {
    global $depth_fields, $pair_ids, $link;
    // mysql_select_db("depth_history") or die('cannot select DB depth_history');
    $table = "depth_history.$pair"."_$suffix";
    echo ("<h1> parsing table $table</h1>\n");
    $result = mysql_query("SELECT COUNT(id) FROM $table");
    if (!$result) die("Q0 Failed with errors:\n".mysql_error());
    $value = mysql_fetch_array ( $result, MYSQL_NUM )[0];
    log_msg("sizeof source = $value \n");
    if ($value < 100000) return false;

    $query = "SELECT ts FROM $table\n";
    $query .= "ORDER BY id\n";
    $query .= "LIMIT 1;\n";
    // echo("Executing $query\n");
    $result = mysql_query($query);
    if (!$result) die("Q1 Failed <$query> with errors:\n".mysql_error());
    $ts = mysql_fetch_array ( $result )[0];


    echo ("first row timestamp: $ts \n");
    $dt = date_parse($ts);
    // print_r();
    $next_m = $dt['month'];
    $next_y = $dt['year'];
    $dt = new DateTime();

    $now = new DateTime();
    $dt->setDate($next_y, $next_m, 1);
    $diff = $now->diff($dt);
    $dt_start = $dt->format('"Y-m-d"');


    log_msg(" date diff from past: {$diff->days} \n ");
    // print_r($diff);
    if ($diff->days < 60) return false;

    update_map($pair, $suffix);

    $dest_table = "depth_archive.$suffix".'_'.$dt->format('mY');

    make_table($dest_table, $depth_fields, true);
    make_index($dest_table, 'PAIR',        'pair_id');

    $query = "ALTER TABLE $dest_table\n";
    $query .= " ADD CONSTRAINT $dest_table"."_ibfk_1` FOREIGN KEY (`pair_id`) REFERENCES `pairs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE";
    mysql_query($query);

    log_msg(" values will be stored in <b>$dest_table</b>\n");

    $next_m = $next_m + 1;
    if ($next_m > 12)
    {
       $next_m = 1;
       $next_y = $next_y + 1;
    }

    $dt->setDate($next_y, $next_m, 1);
    $dt_limit = $dt->format('"Y-m-d"');

    // echo("date limit: $dt_limit \n");
    $pair_id = $pair_ids[$pair] or -1;

    if ($pair_id < 0) die("#FATAL: pair_id not detected for $pair!");

    // удаление случайно добавленных данных
    $query = "DELETE FROM $dest_table\n";
    $query .= "WHERE $dest_table.ts >= $dt_start AND pair_id = $pair_id \n";
    log_msg("<p><b style='color:blue;'>$query</b>");
    $result = mysql_query($query);

    if ($result)
       log_msg ( sprintf("deleted rows: %d\n", mysql_affected_rows()) );
    else
       log_msg("Q2.0 Failed <$query> with errors:\n".mysql_error());


    $limit_id = -1;

    // определение id последней строки, которую нужно перенести в архивную таблицу
    $query = "SELECT ref_id FROM $table"."_map\n";
    $query .= "WHERE ts >= $dt_limit \n";
    $query .= " ORDER BY ref_id\n LIMIT 1\n";

    log_msg("<p><b style='color:brown;'>$query</b>");   // optimized request

    $result = mysql_query($query);
    if ($result && mysql_affected_rows() > 0)
    {
       $row = mysql_fetch_array ( $result, MYSQL_NUM );
       $ref_id = $row[0] - 1;
       log_msg( sprintf(" ref_id from map =<b> %d</b>", $ref_id));
       if ($ref_id > 10000)
           $limit_id = $ref_id;
    }
    else
    {
       err_msg("failed query [$query]: ", mysql_error());
       die("optimization locked!");
    }


    if ($limit_id < 0) // long time request
    {
       $query = "SELECT id FROM $table\n";
       $query .= "WHERE $table.ts < $dt_limit \n";
       $query .= " ORDER BY $table.id DESC LIMIT 1\n";
       log_msg("<pre><b style='color:green'>$query</b>");

       $result = mysql_query($query);
       if ($result)
       {
          $row = mysql_fetch_array ( $result, MYSQL_NUM );
          // print_r($row);
          $limit_id = $row[0];
       }
       else log_msg("Q2.1 Failed <$query> with errors:\n".mysql_error());
    }

    $query = "INSERT INTO $dest_table (pair_id, ts, price, volume, flags)\n ";
    $query .= "SELECT $pair_id, ts, price, volume, flags FROM $table\n";
    if ($limit_id < 0)
        $query .= "WHERE $table.ts < $dt_limit\n";
    else
        $query .= "WHERE $table.id <= $limit_id\n";
    $query .= " ORDER BY $table.id\n";
    log_msg("<b style='color:#800080'><p>$query</b>");
    $result = mysql_query($query);
    if (!$result)  die("#FATAL: Q2.insert failed <$query> with errors:\n".mysql_error());
    log_msg( sprintf("transferred rows: %d", mysql_affected_rows()) );


    $query = "DELETE FROM $table\n";

    if ($limit_id < 0)
       $query .= "WHERE $table.ts < $dt_limit \n";
    else
       $query .= "WHERE $table.id <= $limit_id \n";


    log_msg("<pre><b>$query</b>");
    // return false;

    $result = mysql_query($query);
    if (!$result) die("Q3.delete Failed <$query> with errors:\n".mysql_error());
    log_msg( sprintf("deleted rows: %d", mysql_affected_rows()) );
    log_msg("all ops complete!");
  }

  $link = mysql_connect('localhost', $db_user, $db_pass) or die('cannot connect to DB server: '.mysql_error());


  // foreach ($save_pairs as $pair) save_for_pair($pair);
  echo("<div>\n");
  // transfer_depth('nvc_usd', 'diff');
  // transfer_depth('ltc_btc', 'diff');
  update_map('btc_usd', 'new');


  echo("</div>\n");
  mysql_close($link);
?>

</body>
</html>