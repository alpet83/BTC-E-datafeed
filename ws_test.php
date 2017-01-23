<html>
<head>
  
 <script type="text/javascript">
  function elem(id)
  {
     var e = document.getElementById(id);
     if (!e) logMsg("ERROR: document.getElementById not found element '" + id + "'");
     if (e && typeof e.innerHTML == "undefined") alert(id + ".innerHTML == @undefined");
     return e;
        
  }

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

    function dateToStr(d, GMT)
    {
       if (GMT)
         return d.getUTCFullYear() + "-" + LZ(d.getUTCMonth() + 1) + "-" + LZ(d.getUTCDate());
       else
         return d.getFullYear() + "-" + LZ(d.getMonth() + 1) + "-" + LZ(d.getDate());
    }

    function timeToStr(t, GMT)
    {
       var h = "";
       if (GMT)
          h = t.getUTCHours();
       else
          h = t.getHours();         
        
       return LZ(h) + ":" + LZ(t.getMinutes()) + ":" + LZ(t.getSeconds());
    }
    function fullTimeStr(t, sep = ":")
    {
    
       return LZ(t.getHours()) + sep + LZ(t.getMinutes()) + sep + LZ(t.getSeconds()) + "." + LZ3(t.getMilliseconds());
    }
  
  function logMsg(text)
  {
     var e = elem("debug_log");
     var t = fullTimeStr(new Date());
     if (e)       
     {
         var lines = e.innerHTML.split("\n");
         while (lines.length > 500) lines.shift();
         e.innerHTML = lines.join("\n") + "[" + t + "]. " + text + "</br>\n";
     }           
  }
  
 function open_socket()
 { 
   return new WebSocket('ws://10.10.10.97:8000');
 }
  
 var ws = open_socket();
 
 var opened = false;
 
 ws.onopen = function()
 {  
    opened = true;
 }
 
 ws.onclose = function()
 {  
    opened = false;
 }
 
 
 ws.onmessage = function (event)
 {
    logMsg('server: ' + event);
 } 
              
 function SendMsg()
 {      
    setTimeout(SendMsg, 1000);
    if (ws.readyState == 1)
       ws.send("works!");
           
    if (ws.readyState == 2)
       logMsg("connecting to server...");
       
    if (ws.readyState == 3)
    {
       logMsg("trying reconnect...");
       // ws = open_socket();
    }     
 }                                    
                              
 </script>
</head>
<body onLoad="SendMsg()">
<?php

?>
 <div id="msgs"></div>
 <div id="debug_log"></div>

</body>