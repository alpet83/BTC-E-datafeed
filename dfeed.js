console.log("Initializing...");

var WebSocketClient = require("websocket").client;

var lib = require("./js/alpet_lib");   
var con = require('manakin').global;
con.log.bright = true;

strTimeSQ   = lib.strTimeSQ;
fullTimeStr = lib.fullTimeStr

console.log(strTimeSQ() + ". NodeJS datafeed v0.1");

var srv_local  = '127.0.0.1';
var srv_remote = '10.110.10.10';

var con_list = [];
var con_errs = 0;
var con_busy = false;
var servers = [];
var depth_data = [];
// for filtration only (!)
var depth_asks = [];
var depth_bids = [];
var summary_rows = 0;

var client = false;
var last_connect = false;



function connect_ws(server, port)
{
   var client = servers[server];
   var url = 'ws://' + server + ':' + port;
   
   if (!client) client = new WebSocketClient();
   
   
   var ws = con_list[server];
   if (ws && ws.connected) return ws;    
   
   if (con_busy)
   {
      console.warn(strTimeSQ() + ". #WARN: postponed connect to " + url);
      setTimeout ( function() { connect_ws(server, port); }, 1000 ); // reconnect need
      return false;      
   } 
   
   last_connect = new Date();   
   
   client.on('connectFailed', function(error) {
              if (con_errs < 10)              
                  console.error(strTimeSQ() + '. #ERROR: connect to [' + url + '] failed ' + error.toString());                  
              else
                  process.exit(59);
                     
              setTimeout ( function() { connect_ws(server, port); }, 1500 ); // reconnect need
              con_busy = false;
              con_errs ++;    
            });   
      
   client.on('connect',  
      function(connection) {
               con_errs = 0;
               con_busy = false;
               
               console.info(strTimeSQ() + ". #SUCCESS: connected with " + server + ", object:\n " +  lib.dumpObject(connection, true));
               
               con_list[server] = connection;
               connection.on('error',
                      function(error) {
                          console.warn(strTimeSQ() + ". #ERROR: connection with [" + server + "] failed " + error.toString());                      
                            });  // ob error 
                
               connection.on('close',
                      function() {  
                          console.warn(strTimeSQ() + ". #WARN: disconnected with " + url);
                          con_list[server] = false; 
                          client.connect(url);
                            }); // on close
                                               
               connection.on('message',
                     function(message) {
                          if (message.type == 'utf8')
                              console.log(strTimeSQ() + ". #MSG: " + message.utf8Data);                     
                            }); // on message                              
            }); // on connect
                   
                    
   console.log(strTimeSQ() + ". #DBG: trying connect to " + url);
   con_busy = true;                     
   client.connect(url);     
   servers[server] = client;          
   return true;                
}

function in_filter(pair, data, filter)
{
   if (!data || !filter) return false;


   for (var n = 0; n < data.length; n++)
   { 
       var txt = data[n].join();    
       // if (pair == 'nvc_usd') console.log(strTimeSQ() + '. #DBG: ' + txt + ' in: ' + filter.join(';'));
          
       for (var i = 0; i < filter.length; i++)
       if (txt == filter[i])
       {
          if (pair == 'nvc_usd')
              console.warn(strTimeSQ() + '. #WARN: replay/duplicate depth row [' + pair + '] ignored ' + txt);
          return true;
       }
   }
   return false;
}

function onDepth(pair, data)
{
   var time_last = new Date();  // received was
   // data.ask = [], data.bid = []
      
   if (in_filter(pair, data.ask, depth_asks[pair])) return false;
   if (in_filter(pair, data.bid, depth_bids[pair])) return false;   
   
   var ts = lib.dateToStr(time_last, true) + ' ' + fullTimeStr (time_last, ':', true);
   
   rec = depth_data[pair];
   // 
                               
   if (data.ask)       
     for (var i = 0; i < data.ask.length; i++)
     {
        var a = data.ask[i];
        var txt = a.join();
        depth_asks[pair].push(txt);
                     
        a[2] = ts; 
        rec.ask.push (a);
        summary_rows ++;       
     }
     
   if (data.bid)       
     for (var i = 0; i < data.bid.length; i++)
     {
        var b = data.bid[i];
        var txt = b.join(); // JSON.stringify (b);                
        depth_bids[pair].push(txt);     
        b[2] = ts; 
        rec.bid.push (b);
        summary_rows ++;       
     }
     
   while (depth_asks[pair].length > 32)
          depth_asks[pair].shift();
   while (depth_bids[pair].length > 32)
          depth_bids[pair].shift();    
   // if (pair == 'nvc_usd') console.log("#DBG: " + depth_bids[pair].join(";"));            
                   
}

function onTrade(pair, data)
{
  var ts = fullTimeStr(); 
  if (data.length == 0) return;

  
  for (var server in con_list)
  { 
    var ws = con_list[server];
    if (ws && ws.connected)
    {
       var tick = data[0];
       var txt =  pair + ',' + tick.join();
       console.log(strTimeSQ() + ". #TRADE: sending data to server [" + server + "]: " + txt);
       ws.send ('trade=' + txt);
    }
    else
       console.warn(strTimeSQ() + ". #WARN:  server not connected " + server + "\n" + lib.dumpObject(ws, true)); 
  }
}



// connect_ws(srv_remote, 8000);

function depthDataSubmit()
{
   var date = new Date();
   var sec = date.getSeconds();
   var sel = (sec % 10);   
   
   if (sec % 5 != 0 && summary_rows < 100) return;   
   
   var data = new Object(); // complex update
   var pairs = 0;

   for (var pair in depth_data)
   {
      var rec = depth_data[pair];
      if (rec.ask.length + rec.bid.length > 0)
      {            
        data[pair] = rec;
        pairs ++;
      }      
   }   
   
   if (0 == pairs) return;
   
   var txt = JSON.stringify(data);
   
   var sended = 0;
   // cleanup
   for (var server in con_list)
   { 
     var ws = con_list[server];
     if (ws && ws.connected)
     {
        console.log(strTimeSQ() + ". #DEPTH: sending data to server [" + server + "] size: " + txt.length + ", rows: " + summary_rows);
        // console.log("\t\t\t  " + txt);  
        ws.send('depth=' + txt);
        sended++;
        if (sec == 0) ws.ping();                
     }      
     else
       console.warn(strTimeSQ() + ". #WARN: server not connected " + server);
   }
   
   if (sended > 0)
   for (var pair in depth_data)
   {
      var rec = depth_data[pair];            
      rec.ask = [];
      rec.bid = [];
      summary_rows = 0;  
   }  
   
}

setInterval(depthDataSubmit, 1000);


// подключение к бирже, подписка на данные
var Pusher = require('pusher-client');
// var btce_key = 'c354d4d129ee0faa5c92';       
var wex_key 	= 'ee987526a24ba107824c';
var client = new Pusher(wex_key, { cluster: "eu" }); 

function subcribe(pair)
{
  var rec = new Object();
  rec.bid = [];
  rec.ask = [];
  depth_data[pair] = rec;
  depth_asks[pair] = [];
  depth_bids[pair] = [];

  var ch = client.subscribe(pair + ".trades"); 
  console.log(" subscribe.trades " + pair); 
  ch.bind( "trades",  function (data) { onTrade(pair, data); } );
  console.log(" bind.trades " + pair);
  
  var ch = client.subscribe(pair + ".depth"); 
  console.log(" subscribe.depth " + pair); 
  ch.bind( "depth",  function (data) { onDepth(pair, data); } );
  console.log(" bind.depth " + pair);

}


connect_ws(srv_local, 8000);

function subscribeAll()
{

  var pairs = ['btc_usd', 'btc_rur', 
               'dsh_btc', 'dsh_usd', 
               'eth_btc', 'eth_usd', 'eth_rur', 
               'ltc_btc', 'ltc_usd', 
               'nmc_btc', 'nmc_usd', 
               'nvc_btc', 'nvc_usd', 
               'ppc_btc', 'ppc_usd', 
               'usd_rur'];


      
   for (var i = 0; i < pairs.length; i++)
        subcribe(pairs[i]);
   console.log("servers: " + lib.dumpObject(servers[srv_local], true));
}


setTimeout(subscribeAll, 3000);     

// */
