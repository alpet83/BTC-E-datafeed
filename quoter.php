<!DOCTYPE html>
<html lang="ru">
 <head>
  <meta charset=utf-8>
  <meta name="author" content="Denusinus">
  <title>BTC-E котировщик v0.1</title>
  <style>
  td{
   vertical-align: top;
  }
  #buy, #sell{
    max-height: 180px;
    overflow: hidden;
  }
  </style>
  
    <script src="http://js.pusher.com/2.2/pusher.min.js" type="text/javascript"></script>
    <script src="js/ajaxsup.js" type="text/javascript"></script>
    <script src="js/jquery-1.11.2.js" type="text/javascript"></script>
    <script src="js/jquery.json-2.4.js" type="text/javascript"></script>
<?php

   $state = file_get_contents('trade_state.json');
   echo '<script type="text/javascript">';
   echo "\nvar trade_state_json = '$state';\n"; 
   echo "</script>\n";
   
   $act = "";
   
   if (isset($_REQUEST['action'])) 
      $act = $_REQUEST['action'];
      
   if ($act == 'upd_state')
   {
      $state = $_POST['trade_state'];
      file_put_contents('trade_state.json', $state);
   }    
      
      
?>      
    
    <script type="text/javascript">
    
        
    var ask_last = 0;
    var bid_last = 0;
    // var price_last = 0;
    var recv_count = 0;
    var time_last  = new Date();
    var trade_state = $.evalJSON(trade_state_json);         
    
    var actions      = new Array();
    var orders       = new Object();
    var o2kill       = new Object(); // заявки для снятия
    var orders_count = new Object();   // counters by ticker
    var positions    = new Object();
    var orders_received = false;
    var work_queue   = new Array();  // джобы на выполнении сейчас
    
    
    var sel_orders    = new Object(); // отмеченные заявки в таблице 
    
    var in_progress = new Object();    
    var checks_count = 0;
    var idle_ticks = 0;
    
    var post_queue = new Array();
    var trans_map  = new Object(); // transaction map: creating orders 
    
    var kill_queue = new Array();
    var killed_map = new Object(); // набор статусов убираемых заявок
    var kill_timer = 0;
    var last_trans_no = new Array();
          
    var nodes_cache = new Object(); // для хранения указателей на узлы под Осла
    var last_min = 0;      
    var last_funds = new Object();
    var last_trade = new Object();
    
    in_progress.load_orders = 0;
    
    // ============================================ functions ========================================= //
    function dumpObject(data)
    {
      var result = "";
      for (var index in data)
      {
        var val = data[index];
        var vt = typeof val;
        result = result + index + " = ";
        
        if (vt == "number" || vt == "string")
           result = result + String(val) + " ";
        else 
        if (vt == "object") 
           result = result + "{" + dumpObject(val) + "}";
          
        else
           result = result + "@" + vt + " ";   
        
      }
    
      return result;
    }
    
    function elem(id)
    {
       var e = document.getElementById(id);
       if (!e) setHTML("info5", "ERROR: document.getElementById not found element '" + id + "'");
       if (e && typeof e.innerHTML == "undefined") alert(id + ".innerHTML == @undefined");
       return e;
        
    }
    
    
    function volcheck(v)
    {
       v = Number(v) * 100;
       v = v - Math.floor(v);              
       if ( v > 0.33 && v < 0.35 ) return true;
       
       return false; 
    }
    
    
    function arval (a, key, def)
    {
       if (typeof a[key] == 'undefined') 
           return def;
       else
          return a[key];
    }

     
    function LZ(num) 
    {
  	   var A = num.toString();
  	   if (A.length == 2) return A;  	   
       if (A.length == 1) return "0" + A;
       	     
	     return "00";
    }
    
    function LZ3 (num) 
    {
  	   var A = num.toString();
       if (A.length >= 3) return A;
  	   if (A.length == 2) return "0" + A;  	   
       if (A.length == 1) return "00" + A;       	     
	     return "000";
    }
    

    function timeToStr(t)
    {
       return LZ(t.getHours()) + ":" + LZ(t.getMinutes()) + ":" + LZ(t.getSeconds());
    }
    function fullTimeStr(t, sep)
    {
      return LZ(t.getHours()) + sep + LZ(t.getMinutes()) + sep + LZ(t.getSeconds()) + "." + LZ3(t.getMilliseconds());
    }
    
    function setTBodyInnerHTML(tbody, html) 
    {
       var temp = tbody.ownerDocument.createElement('div');
       var save_id = tbody.id;
       temp.innerHTML = '<table>' + html + '</table>';
       tbody.id = tbody.id + "1"; // forgot element
       
       var tab = temp.firstChild; 
       var cl = tab.children;
       var i;       
       for (i = 0; i < cl.length; i ++) // && fc[0]
       {
         var e = cl[i];
         if (e.tagName == "TBODY") 
         {
            e.id = save_id;
            // setHTML ("info3", e.id + "@" + e.tagName + " replacing to " + tbody.id);
            tab = tbody.parentNode;                    
            tab.replaceChild(e, tbody);
            nodes_cache[save_id] = e;
         } 
       }
       
    }
    
    function genTransNo()
    {       
       var t = "0";
       var dt = new Date();
       for (i = 0; i < 10; i++)
       {
         t = fullTimeStr(dt, "") + i.toString();
         if (last_trans_no.indexOf(t) < 0) break;
       }
       last_trans_no.push(t);
       if (last_trans_no.length > 10) last_trans_no.shift();
       return t;       
    }
    
    function logMsg(text)
    {
       var e = elem("debug_log");
       var t = timeToStr(new Date());
       if (e)       
       {
           var lines = e.innerHTML.split("\n");
           while (lines.length > 500) lines.shift();
           e.innerHTML = lines.join("\n") + "[" + t + "]. " + text + "</br>\n";
       }           
    } 
    
    function findWork(name, target)
    {
      for (i = 0; i < work_queue.length; i++)
      {
          var w = work_queue[i];
          if (w.name == name && w.target == target) return w;
      }
      return false;
    }
    
    function readyForWork (name, target)
    {
      return (!findWork(name, target) && work_queue.length < 5);
    }
    
    function workFailed(name, target)
    {
      var w = findWork(name, target);
      if (w)
       {
           w.status = "error";
           w.fail_count = w.fail_count + 1;
       }
    
    }
    
    function workSuccess(name, target)
    {
       var w = findWork(name, target);
       if (w) w.status = "complete";
    }

    
    function schedule(cmd, args) 
    { 
      actions[actions.length] = { command: cmd, params: args }; 
    }
    
    function killPostpone(id)
    {
      if (kill_queue.indexOf(id) < 0 && !arval(killed_map, id, false))
      {
          kill_queue.push(id);
          sel_orders[id] = false;
          killed_map[id] = 1;
          idle_ticks = 0;
          
          var e = elem('cbx' + id);
           
          if (e)
          { 
             e.checked = false;
             var tr  = e.parentElement.parentElement;
             var tab = tr.parentElement;
             // alert (tab.nodeName);
             tr.style.backgroundColor = '#FFEEEE';
             // if (tab) tab.removeChild(tr);
          }          
       }   
    }
    
    function killFailed(id)
    {
       o2kill[id] = false;
       killed_map[id] = 0;
       killPostpone(id);       
       if (1 == kill_queue.length)
           kill_timer = 1;
       workFailed("kill_order", id);  
    }
    
    function killSuccess(id)
    {
       // ok
       killed_map[id] = 2;
       workSuccess("kill_order", id);
       in_progress.load_orders = 3;                     
    }
    
    function killOrder(id)
    {
      idle_ticks = 0;
      $.ajax( 
         { dataType: "json",
              cache: false,
                url: "brokserv.php?action=kill_order&order_id=" + id,              
              error: function (jqXHR, status, err)  { logMsg ("#ERROR(killOrder): " + status); killFailed(id); },
            timeout: 28500,   
            success: function (data, status, jqXHR) 
                     { 
                        var err = arval(data, "error", "");                        
                        logMsg ("#SUCCESS(killOrder " + id + "): " + status + ", data: " + dumpObject(data)); 
                        if(1 == data.success || "bad status" == err) killSuccess(id);
                     }
         } );
    }
       
    
    
    function scheduledCancel(timeout)        
    {
       if (0 == kill_queue.length) 
           return;
       
       var cnt = 0;
       for (var i = 0; i < kill_queue.length; i ++)
       {
         var id = kill_queue.shift();
         if (arval(killed_map, id, false)) continue;
         
         cnt = cnt + 1;
         logMsg("   scheduled cancel order #" + String(id) + ", cnt = " + String(cnt));         
         setTimeout ( "killOrder(" + String(id) + ")", cnt * 500 + 5 );
         o2kill[id] = true;
         
         if (cnt >= 10) break;
       } // for
       
       
       if (kill_queue.length > 0)
           kill_timer = 5;          
    }

    function cancelOrders(params)
    {
      // by ticker and volume tag
      if (kill_queue.length > 0)
      {
        // scheduledCancel(1);
        return;        
      }
      
      
      logMsg("cancelOrders: ticker = " + params.ticker);
      var cnt = 0;
       
      for (var id in orders)       
      {
         var rec = orders[id];
         if (rec.pair != params.ticker || !volcheck(rec.qty) ) continue;
         if (o2kill[id] || arval(killed_map, id, false)) continue; // trying to kill
         
         /* if (cnt < 5)
         {
           logMsg("immediate cancelling order " + id + " price = " + String(rec.price) + ", qty = " + String(rec.qty));
           setTimeout ( "killOrder(" + String(id) + ")", cnt * 500 + 5 );
         }
         else */  
         {           
           logMsg("scheduled cancelling order " + id + " price = " + String(rec.price) + ", qty = " + String(rec.qty));
           killPostpone(id);      
         }
                                    
         // killOrder (id); 
         o2kill[id] = true;
         cnt = cnt + 1;                
      }       
      if (kill_queue.length > 0 && 0 == kill_timer)
          kill_timer = 5;                               
    }
  
    function cancelSelected()    
    {
       for (var id in sel_orders)
       if (sel_orders[id])      
           killPostpone(id);       
    }
    
    
    function createOrder(ticker, price, qty, dir, tno)
    {
       idle_ticks = 0;
       if (typeof price == "undefined" || price <= 0.0001)
       {
          logMsg("#ERROR(createOrder): price not valid = " + String(price));
          workFailed("create_order", tno);
          return;            
       }

       if (price < 0.001) 
         price = price.toFixed(5); else
         if (price < 0.01) 
           price = price.toFixed(4); else          
           if (price < 10)           
               price = price.toFixed(3); else
             if (price > 100)
                 price = price.toFixed(2);
             

       logMsg("#DBG(createOrder): " + dir  + " " + ticker + " @ " + String(price)); 
  
       $.ajax( 
       { dataType: "json",
            cache: false, 
              url: "brokserv.php?action=new_order&pair=" + ticker + 
                   "&price=" + String(price) + "&qty=" + String(qty) + "&dir=" + dir,               
            error: function (jqXHR, status, err)  
                   { logMsg ("#ERROR(createOrder): " + status);
                     workFailed("create_order", tno);
                   },
          timeout: 15500,   
          success: function (data, status, jqXHR)
                   { 
                      logMsg ("#SUCCESS(createOrder): price = " + String(price) + ", data: " + dumpObject(data));
                      if (1 == data.success)
                      { 
                          workSuccess ("create_order", tno);
                          in_progress.load_orders = 3;
                          var funds = data["return"].funds;
                          $.each(funds, function(key, val)
                           {
                             last_funds[key] = val;
                             if ("nvc" == key) setHTML("info4", "nvc amount now = " + String(val));
                           }
                          );
                      }
                      else
                          workFailed  ("create_order", tno);    
                   }
       } );       
    }
    
    function addOrder(t, p, v, d)
    {                                  
      var obj = { ticker: t, price: p, amount: v, dir: d };
      obj.transNo = genTransNo();
      post_queue.push(obj.transNo);      
      trans_map[obj.transNo] = obj;
    }
    
    
    
    function scheduleOrders (params, index)
    {
      var t = params.ticker;
      var ask = params.a_targets[index];       
      // setTimeout ( function () { createOrder(t, ask, 0.1034, "sell"); }, index * 500 + 5 );
      addOrder(t, ask, params.volume, "sell");      
      var bid = params.b_targets[index];
      addOrder(t, bid, params.volume, "buy");
      // setTimeout ( function () { createOrder(t, bid, 0.1034, "buy"); },  index * 500 + 250 );  
    }
    
    function placeOrders(params)
    {
      if (orders.length > 32)
      {
         logMsg("#ERROR: placeOrders breaked due to many orders active");
         return;
      }
       
      var dt = new Date(); 
       
      if (dt.getHours() < 23) // TODO: preserve cool-down time 10 minutes only  
        for (var i = 0; i < 8; i ++)
             scheduleOrders(params, i); 
    
    }
    
    
    function processAction()
    {
    
       if (kill_timer > 0)
       {
           kill_timer = kill_timer - 1;
           // if (0 == kill_timer)  scheduledCancel(1);                      
       }    
      setTimeout(processAction, 1000);
      if (actions.length == 0) return;
      var action = actions.shift();
      var cmd = action.command;
      if ("KILL_ORDERS" == cmd)
          cancelOrders(action.params);
      if ("NEW_ORDERS" == cmd)
          placeOrders(action.params);   
      logMsg("processed action: " + cmd);
    }
    
    function addWork(name, target, params)
    {
      var w = new Object();
      w.name = name;
      w.target = target;
      w.status = "new";
      w.fail_count = 0;
      w.timeout    = 300;
      w.params     = params;     
      work_queue.push(w);
      return w;
    }
    
    function handleWork(w)
    {
      w.status = "progress";
      if ("create_order" == w.name)
      {
        var o = w.params;                
        if (o)
        {
            createOrder(o.ticker, o.price, o.amount, o.dir, w.target);
            logMsg("#DBG: handleWork/create_order params = " + dumpObject(o));
        }
      }
      if ("kill_order" == w.name)
          killOrder(w.target);
          
      if ("load_orders" == w.name)
          requestOrders();        
      if ("load_trades" == w.name)
          requestTrades();  
    }
    
    function processWork()
    {
      setTimeout(processWork, 100);
      var i = 0;
      var dt = new Date();
      var sec = dt.getSeconds();
      var tm = dt.getMinutes(); // Math.round (sec / 5);   
      var hr = dt.getHours();
      if ( (tm != last_min) && (tm % 10 == 0) )
      {
        last_min = tm;        
        var price = 1.301;        
        
        var btc_price = 700;
        st = trade_state["btc_usd"];        
        if (typeof st != "undefined" && st.price_last > 0)
            btc_price = st.price_last;
        st = trade_state["nvc_btc"];
        
        var nvc_btc = 0.0005;          
        
        if (typeof st != "undefined" && st.price_last > 0)
             nvc_btc = st.price_last;
             
        var target = nvc_btc * btc_price;
        
        var st = trade_state ["nvc_usd"];
        if (typeof st != "undefined" && st.price_last > 0)
            price = st.price_last;       
        
        
        if (price != 1.301)
        {
          price = price.toFixed(3);
          var rv = Math.random() * 20;          
          logMsg("#DBG: шальной прострел новы по цене около price_last = " + String(price) + ", target = " + String(target) + ", ref = " + String(nvc_btc));           

          if (price > target + 0.003)                                        
            {
              createOrder("nvc_usd", Number(target) -    0.001, 3.1415, "sell", 0);
              createOrder("nvc_btc", nvc_btc + 0.00001, 3.1415, "buy",  0);
            }
          else
          // if (price < target - 0.003)
          {
            createOrder("nvc_usd", target + 0.003, 3.14159, "buy",  0);
            createOrder("nvc_btc", nvc_btc - 0.00001, 3.14159, "sell",  0);
          }
                                
          // 1DvHSF7RMCfd3y3SFgFmvxMqK8K5kTKTTD
          // createOrder("nvc_btc", 1,  3.1415926, "buy", 0);
        }
        
        var day = dt.getDate();
        if (day == 8 && price != 1.301)
        {
          var qadd = ( Math.floor (st.qty_last) % 10 ) * 0.1;
          qadd = qadd + ( st.qty_last - Math.floor(st.qty_last) ) * 0.5;            
          var qty_buy  = 100;
          var qty_sell = 100;
          
          if (sec % 20 == 0)
          {
             if (st.side_last == "sell")
                 qty_buy = qty_buy + qadd;
             else
                 qty_buy = qty_buy - qadd;    
            logMsg("#DBG: correction volume for " + String(st.qty_last) + " = " + String(qadd));
          }
          
                  
          // createOrder("nvc_usd", Number(price) - 0.000, qty_buy.toFixed(8),  "buy",  0);
          // createOrder("nvc_usd", Number(price) + 0.000, qty_sell.toFixed(8), "sell", 0);                   
        }
        
        if (hr >= 22 && hr < 23)
        {
          // logMsg("#DBG: шальная продажа новы по цене, price_last = " + String(price));
          // createOrder("nvc_usd", 1, 3.1415926, "sell", 0);
          // createOrder("nvc_btc", 0.001,  3.1415926, "sell", 0);
        }
        
             
        var amount = last_funds["nvc"];
        if (typeof amount != "undefined" && amount > 1000 && hr == 21)
        {
           price = price * 0.99;
           price = price.toFixed(3);
           logMsg("#DBG: заявка на ликвидацию позиции по цене $ " + String(price));
           createOrder("nvc_usd", price, 500.0034, "sell", 0);
           price = price / btc_price;
           price = price.toFixed(5);
           logMsg("#DBG: заявка на ликвидацию позиции по цене BTC " + String(price));
           createOrder("nvc_btc", price, 500.0034, "sell", 0);
        }      
      }
      
      
      var tab = "";
      var wrest = new Array();
      // dumping works      
      for (i = 0; i < work_queue.length; i++)
      {
          var w = work_queue[i];
          tab = tab + "<tr><td>" + w.name + "<td>" + w.target + "<td>" + w.status;
          if ("complete" != w.status && w.fail_count < 3 && w.timeout > 0)
              wrest.push (w);          
          if ("error" == w.status)   
              tab = tab + ":" + w.fail_count;

          if ("progress" == w.status)
          {
              w.timeout = w.timeout - 1;
              tab = tab + ":" + w.timeout;
          }  
      }
      for (i = work_queue.length; i < 5; i ++)
           tab = tab + "<tr><td>idle<td>nope<td>wait...";

      var e = elem("tbody_jobs");      
      if (e)
      {
        if (navigator  &&  navigator.userAgent.match( /MSIE/i )) 
           setTBodyInnerHTML(e, tab);
        else
           e.innerHTML = tab;
      }
      
      // handling single work
      for (i = 0; i < work_queue.length; i++)
      {
         var w = work_queue[i];
         if (w.status == "new" || w.status == "error")
         { 
             handleWork(w);
             break;
         }         
      }
      
      work_queue = wrest;       
      
      // pushing new jobs
      if (work_queue.length >= 5) return;
           
      if (kill_queue.length > 0)      
      {
         var id = kill_queue.shift();
         addWork("kill_order", id);
         return;
      }
      if (post_queue.length > 0)
      {
         var tno = post_queue.shift();
         addWork("create_order", tno, trans_map[tno]);
         return;
      }
      
    
    }
    
    
    var pusher = null; 
    
    var sender = makeXMLRequest();   
    
    function setHTML(id, text)
    {
       var e = elem(id);
       if (e == null)
       {
           setHTML("info", "ERROR: not found element: " + id);
           return;  
       }
              
       if (typeof text != "undefined")
           e.innerHTML = text.toString();
       else
           e.innerHTML = "NULL";    
    }
 
  

    function onTrade(ticker, data) 
    {            
    
       var i = data.length - 1;
       // setHTML("info", "data last: " + i.toString());
       if (i < 0) return;        
       var rec = data[i];       
       if (typeof rec == "undefined")
       {
          setHTML("info4", "data.last undefined!");
          return;
       }
       
       if (typeof rec[1] == "undefined")
       {
          setHTML("info4", "data.last[1] undefined!");
          return;
       }
       var state = trade_state[ticker];
       if (typeof state == "undefined") 
           state = { price_last: 0, price_ref: 0 };
           
       var price_ref = arval (state, 'price_ref', 0);      
       // setHTML("info", "record length: " + rec.length.toString());
       
       recv_count = recv_count + 1;
       setHTML("counter", String(recv_count) + ", checks: " + String(checks_count) + ", idle: " + String(idle_ticks));
       
       time_last = new Date();  // received was
       
       var pval = Number (rec[1]);        
       
       if (ticker != "btc_usd")
          setHTML("info", "onTrade: " + ticker + "@" + pval);
       
              
       state['side_last']  = rec[0];
       state['price_last'] = pval;
       state['qty_last']   = Number(rec[2]);
       state['time_last']  = timeToStr (time_last);
       
       if (!orders_received) 
       {
         setHTML("info4", "onTrade: orders_received === false");
         return;
       }
       
       
       // if ( Math.abs(pval - price_ref) < 0.0001 ) return;
       if (ticker == "nvc_usd")
           logMsg(" nvc price changed from " + String(price_rev) + " to " + String(pval)); 
              
       
       state['price_ref']  = pval;
       /*
       var a_targets = new Array( 0, 0, 0, 0, 0, 0, 0, 0 );
       var b_targets = new Array( 0, 0, 0, 0, 0, 0, 0, 0 );
       
       var fp = 1;
       if (pval < 100)   fp = 2;
       if (pval < 10)    fp = 3;
       if (pval < 0.1)   fp = 4;
       if (pval < 0.01)  fp = 5;
       // if (pval < 0.01) fp = 6;
       
       
       for (var i = 0; i < a_targets.length; i ++)
       {
          var delta = (i + 1) * 0.02;
          var price = pval * (1 + delta);
          a_targets[i] = price.toFixed(fp);
          price = pval * (1 - delta);
          b_targets[i] = price.toFixed(fp);
       }
              
       state['ask_targets'] = a_targets;
       state['bid_targets'] = b_targets;
              
       // TODO: start order replacing (cancel/new) by timer
       var args = new Object();
       args.ticker = ticker;
       args.tag    = 0.0034;  // float qty tag
       schedule("KILL_ORDERS", args);
       
       orders_active = arval(orders_count, ticker, 0);       
       setHTML ("info3", "active orders for '" + ticker + "'  = " + String(orders_active));
       if (orders_active > 16)
       {
          setHTML ("info5", "#OVERF: to many active orders for '" + ticker + "' = " + String(orders_active));
          // schedule("KILL_ORDERS", args);
          return 0;
       }
       
       
       args.a_targets = a_targets;
       args.b_targets = b_targets;
       args.price = pval;
       args.volume = 0.1034;
       if (pval < 2)
           args.volume = 1.0034;
              
       schedule("NEW_ORDERS",  args);               
                
                
       setHTML("ask_target", a_targets.join(", ") );
       setHTML("bid_target", b_targets.join(", ") );      
       
       
       // alert(txt);
       */

       trade_state[ticker] = state;       
       var txt = $.toJSON (trade_state);
       sender.open("POST", "quoter.php", true);
       var params = "action=upd_state&trade_state=" + encodeURIComponent(txt);
       sender.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
       sender.send(params);         
         
    }

    function onTrade_1(data) { onTrade("btc_usd", data); }
    
    function checkDataLag()
    {       
      var now = new Date();   
      var lag = now - time_last;
      setHTML("lag", lag / 1000.0);
      
      var delay = 1000 - (lag % 1000);
      if (delay < 100) delay += 1000;
      setTimeout(checkDataLag, delay);
      checks_count ++;
      idle_ticks ++;
      
      if (checks_count > 1000 && 0 == work_queue.length && 0 == post_queue.length && 0 == kill_queue.length)
          location.reload(); 
          
      if (idle_ticks > 100 && 0 == idle_ticks % 10)
      { // memory optimization
        killed_map = new Object(); // 
      }

      for (var index in trade_state) 
       if (index != "version")
         {       
            var item = trade_state[index];
            setHTML(index + ".side",      item['side_last']);
            setHTML(index + ".ticker",     index);
            setHTML(index + ".price",      item['price_last']);
            setHTML(index + ".qty",        item['qty_last']);                 
            setHTML(index + ".time",       item['time_last']);
         };
          
    }
    
    function subscribeData(ticker, t_callback)
    {
      var channel = pusher.subscribe(ticker + '.trades');           
      channel.bind('trades', t_callback);         
      logMsg("#SUBSCRIBED: " + ticker);
    }

    function parseTrades(data)
    {
      setHTML ("info2", "parseTrades - processing data");
      positions = new Object();
    
      if (typeof data[0] != "undefined")  
      {        
      
         var e = elem("tbody_trades");
         var tab = ""; // e.innerHTML;   
         var count = 0;
         var bs = new Array("sell", "buy");   
         var rows = new Array();
         setHTML ("info4", "parseTrades - processing start");
         $.each( data, function(key, val) 
          {          
             // logMsg("#DEBUG: Added order " + key + " " + val.pair);             
             // return;              
             var r = "<tr><td>" + val.id;                     
             amount = Number(val.qty);
             if (0 == val.buy) amount = -amount;             
             r = r + "<td>" + val.short_name + "<td>" + bs[val.buy] + "<td>" + val.qty + "<td>" + val.price + "<td>" + val.ts + "\n";
             
             rows.push (r);
             if (volcheck(val.amount)) 
                 positions[val.short_name] = arval(positions, val.short_name, 0) + amount;
             last_trade[val.short_name] = Number(val.price); 
             
          }
         );
         
         while (rows.length > 15) rows.shift();
         
         if (navigator  &&  navigator.userAgent.match( /MSIE/i )) 
          setTBodyInnerHTML(e, rows.join("\n"));
         else           
          e.innerHTML = rows.join("\n");
         e = elem("tbody_positions");
         tab = "";
         for (var pair in positions)
         {
            amount = arval(positions, pair, 0);
            tab = tab + "<tr><td>" + pair + "<td>" + amount + "\n";  
         }
         if (navigator  &&  navigator.userAgent.match( /MSIE/i )) 
          setTBodyInnerHTML(e, tab);
         else          
          e.innerHTML = tab;        
      }
    }

    function requestTrades()
    {         
       $.ajax( 
         { dataType: "json",
              cache: false,
                url: "brokserv.php?action=daily_trades&filter=34",              
              error: function (jqXHR, status, err)  
                     { 
                       setHTML ("info5", "loadTrades - ajax rqs failed" + status); 
                       in_progress.load_trades = 1;
                       workFailed("load_trades", 0);
                     },
            timeout: 15000,   
            success: function (data, status, jqXHR)            
                     {  
                        in_progress.load_trades = 1;
                        if ("success" == status)
                        {                     
                           parseTrades(data); 
                           in_progress.load_trades = 0;
                           workSuccess("load_trades", 0);
                        }
                        else 
                           workFailed("load_trades", 0);
                     }
         } );
      setHTML ("info2", "requestTrades - ajax rqs created");       
    }


    function selOrder(cbx)
    {
      var id = cbx.id.substr(3);
      //alert(id + " = " + cbx.checked);
      sel_orders[id] = cbx.checked;    
    }

    function parseOrders(data)
    {    
      var ret = data["return"];
      setHTML("odump", "data = " + toString(data));
      
      if (typeof ret != "undefined")
      {     
         var e = elem("tbody_orders");
         var tab = ""; // e.innerHTML;   
         var count = 0;    
         var prev_queued = kill_queue.length;
         var dt = new Date();
         setHTML ("info2", "parseOrders - processing start");
         orders_received = true;              
         orders_count = new Object();   
         var sel_orders_new = new Object();
                    
         $.each( ret, function(key, val) 
          {          
             // logMsg("#DEBUG: Added order " + key + " " + val.pair);             
             // if (!volcheck(val.amount)) return;
             count = count + 1;
                           
             var row = "<tr><td>" + count.toString() + "<td>" + key;
             ts = val.timestamp_created;
             dt.setTime( Number(ts) * 1000 );
             row = row + "<td>" + val.pair + "<td>" + val.type + "<td>" + val.amount + "<td>" + val.rate + "<td>" + timeToStr(dt);
             var rec = new Object();
             rec.price = val.rate;
             rec.pair  = val.pair;
             rec.side  = val.type;
             rec.qty   = val.amount;
             
             orders[key] = rec;
             
             active = arval(orders_count, rec.pair, 0) + 1;
             orders_count[rec.pair] = active;
             
             // \"" + key + "\"
             var chk = '';
             if (sel_orders[key]) chk = 'checked=true';
             row = row + "<td><input id='cbx" + key + "' onclick='selOrder(this);'  type='checkbox' " + chk + " />";
             tab = tab + row;
             
             sel_orders_new[key] = sel_orders[key];
             
             if (o2kill[key] || active > 16)
             {
                if (rec.qty < 10)  kill_queue.push(key);
             }                   
          }
         );
         sel_orders = sel_orders_new;  // forgot deprecated orders
         
         if (navigator  &&  navigator.userAgent.match( /MSIE/i )) 
           setTBodyInnerHTML(e, tab);
         else
           e.innerHTML = tab;
               
         // logMsg("#DBG: parseOrders processed " + String(count) + " rows");
               
         if (kill_queue.length > 0 && 0 == prev_queued && checks_count < 100)
         {
            kill_timer = 5;
            logMsg("#WARN: some orders scheduled for cancel. Total active orders = " + String(count));             
         }
         // loadTrades();
         if (readyForWork("load_trades", 0))
         {       
            var w = addWork("load_trades", 0);
            w.timeout = 3000;
         }
         
      }
      else
        logMsg("#ERROR(parseOrders): invalid object " + data); 
    }

    
    function loadOrders()
    {
       setTimeout(loadOrders, 500);          
       
       if (in_progress.load_orders > 0)       
       {
          in_progress.load_orders--; 
          setHTML ("info3", timeToStr(new Date()) + " in_progress.load_orders = " + String(in_progress.load_orders));          
       }
       else
       {             
          if (!readyForWork("load_orders", 0)) return; // busy       
          in_progress.load_orders = 20;
          var w = addWork("load_orders", 0);
          w.timeout = 3000;
       }         
    }
    
    function requestOrders()
    {
       setHTML ("info2", "requestOrders - ajax rqs created");
        $.ajax( 
         { dataType: "json", 
              cache: false,
                url: "brokserv.php?action=orders",              
              error: function (jqXHR, status, err)  
                      { logMsg ("#ERROR(loadOrders): " + status); 
                        workFailed("load_orders", 0); 
                        in_progress.load_orders = 3; 
                      },
            timeout: 15000,   
            success: function (data, status, jqXHR) 
                     { 
                        if ("success" == status)
                        {
                          parseOrders(data); 
                          in_progress.load_orders = 20;                          
                          setHTML ("info5", "requestOrders - ajax rqs status = " + status);
                          workSuccess("load_orders", 0);
                        }
                        else
                          workFailed("load_orders", 0);
                     }
         } );      
    } // requestOrders
    
    
    function connectPusher()
    {
      pusher = new Pusher('c354d4d129ee0faa5c92');
      subscribeData("btc_usd", function (data)
                               { onTrade("btc_usd", data);} );
      subscribeData("nvc_usd", function (data)
                               { onTrade("nvc_usd", data);} );
      subscribeData("nvc_btc", function (data)
                               { onTrade("nvc_btc", data);} );                             
                                                            
    }
    
    
    function afterLoad()
    {                       
      setHTML("info", "afterLoad.1");
      setTimeout(checkDataLag,  1000);      
      setTimeout(connectPusher, 2000);      
      setHTML("info", "afterLoad.2");             
      // var data = new Array(0);
      // data[0] = new Array ("buy", "279.3", "1");
      // onTrade_1(data);
            
      loadOrders ();
      processAction();
      processWork();
      // setHTML("info", "afterLoad.3");
    }        
    
    </script>
  
  </head>
  <body onLoad="afterLoad()">
    <h1>Last trades</h1>
    <?php 
              
       function add_hcells($data) 
       { 
         foreach ($data as $text) echo("<th>$text</th>\n"); 
       }
       function add_dcells($data) 
       { foreach ($data as $text) echo("<td>$text</td>\n"); }
       
       function add_ticker_table($ticker)     
       {            
          $tab_header = '<table border="1" width="400" cellpadding="7" style="border-collapse:collapse;">'."\n";
          echo($tab_header); 
          echo("<tr>"); 
          add_hcells( array("Side", "Ticker", "Price", "Qty", "Received") );
          echo("<tr>");
          $flds = array("side", "ticker", "price", "qty", "time");
          foreach ($flds as $fld) add_dcells( array("<div id='$ticker.$fld'></div>") );
          echo("</table>");   
       }
    
       add_ticker_table("btc_usd");
       add_ticker_table("nvc_btc");
       add_ticker_table("nvc_usd");
       add_ticker_table("ltc_btc");
    ?>
    
  
    
    
    <table border="1" width="700" cellpadding="7" style="border-collapse:collapse;">
     <tr>
       <td>Target ask price<td><div id="ask_target"></div>
     <tr>
       <td>Target bid price<td><div id="bid_target"></div>  
    </table>


    <h2>Orders</h2>
    <table border="1" width="700" cellpadding="7" style="border-collapse:collapse;">
      <tr><th width=50>№<th>ID<th>Pair<th>Side<th>Qty<th>Price<th>Time
      <tbody id="tbody_orders">
      </tbody>
    </table>
    
    <input type="button" value="Cancel selected" onclick="cancelSelected();" /><br />
    
    <h2>Trades</h2>
    <table border="1" width="700" cellpadding="7" style="border-collapse:collapse;">
      <tr><th>ID<th>Pair<th>Side<th>Qty<th>Price<th>Time
      <tbody id="tbody_trades">
      </tbody>
    </table>
    
    <h2>Positions</h2>
    <table border="1" width="700" cellpadding="7" style="border-collapse:collapse;">
      <tr><th>Pair<th>Amount
      <tbody id="tbody_positions">
      </tbody>
    </table>
    

    <h2>Works/Jobs</h2>
    <table border="1" width="700" cellpadding="7" style="border-collapse:collapse;">
      <thead><tr><th width="250">Name<th width="150">Target<th width="300">Status</thead>
      <tbody id="tbody_jobs">
      </tbody>
    </table>
    
    <h2>Status & Log</h2>
    <table border="1" width="700" cellpadding="7" style="border-collapse:collapse;">      
      <tr><td width=200>received records:<td width=500><span id="counter">0</span>  
      <tr><td>Status 1<td><span id="info">WARN: Script not worked!</span>
      <tr><td>Status 2<td><span id="info2">info 2</span></td></tr>
      <tr><td>Status 3<td><span id="info3">info 3</span></td></tr>
      <tr><td>Status 4<td><span id="info4">info 4</span></td></tr>      
      <tr><td>Last Error:<td><span id="info5">info 5</span></td></tr>
      <tr><td>Orders dump<td><span id="odump">orders</span></td></tr>
      <tr><td>Data lag:<td> <span id="lag">0 ms</span> sec.</td></tr>
    </table> 
     <p>Debug: <div id="debug_log"></div></p>
  </body>
</html>