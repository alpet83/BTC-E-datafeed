<html>
<head>
<title>Acrhive transfer script</title>
 <style type=text/css>
 .red {
   color:red;
 }

 </style>

 <script type="text/javascript">

 function onLoad()
 {
   // setTimeout("document.location.reload(true);", 100000);
 }

 </script>

</head>
<body style="background-color:gray;color:white;" onLoad="onLoad()">
<pre>
<?php
  $dt = new DateTime();


  include_once('lib/common.php');
  include_once('lib/config.php');
  include_once('lib/db_tools.php');

  log_msg("script loaded.");

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

  function fmt_num($num)
  {
    return number_format($num, 0, '', ' ');
  }



  $pairs_ids = array();

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

    log_msg(" last id in [$dst_table] = ".fmt_num($id));
    if ($id == 0)
    {
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
       printf("   id for [%s] = <b style='color:#ff00ff'>%s</b> \n", $row[1], fmt_num($row[0]));
    }
  }


  function transfer_depth($pair, $suffix)
  {
    global $depth_fields, $pair_ids, $link;
    // mysql_select_db("depth_history") or die('cannot select DB depth_history');
    $table = "depth_history.$pair"."_$suffix";
    echo ("<h1> parsing table $table</h1>\n");
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


    log_msg(" date diff from past: <b style='color:yellow'>{$diff->days}</b> \n ");
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

    log_msg("<p><b style='color:yellow;'>$query</b>");   // optimized request

    $result = mysql_query($query);
    if ($result && mysql_affected_rows() > 0)
    {
       $row = mysql_fetch_array ( $result, MYSQL_NUM );
       $ref_id = $row[0] - 1;
       log_msg( sprintf(" ref_id from map =<b> %s</b>", fmt_num($ref_id) ));
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
    log_msg( "transferred rows: <b>".fmt_num( mysql_affected_rows()).'</b>' );


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


  log_msg("loading pairs...");
  // updating pairs list
  for ($i = 0; $i < 1; $i++)
  {
     $query = "SELECT * FROM depth_archive.pairs";
     $result = mysql_query($query) or die("failed request[$query]");

     while ($row = mysql_fetch_array ( $result, MYSQL_ASSOC ))
     {
        $pair = $row['pair'];
        $pair_ids[$pair] = $row['id'];
     }

     foreach ($save_pairs as $pair)
     if (!isset($pair_ids[$pair]))
     {
        echo(" id for pair $pair is not set - addding...\n");
        $query = "INSERT INTO depth_archive.pairs (pair)\n";
        $query .= "VALUES ('$pair')\n";
        mysql_query($query);
     }
  }

  print_r($pair_ids);

  // foreach ($save_pairs as $pair) save_for_pair($pair);
  echo("<div>\n");
  // transfer_depth('nvc_usd', 'diff');
  transfer_depth('btc_usd', 'full');
  echo("</div>\n");
  mysql_close($link);
?>

</body>
</html>