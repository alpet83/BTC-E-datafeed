

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
       if (!t)
            t = new Date(); 
    
       var h = "";
       if (GMT)
          h = t.getUTCHours();
       else
          h = t.getHours();         
        
       return LZ(h) + ":" + LZ(t.getMinutes()) + ":" + LZ(t.getSeconds());
    }
    
    function fullTimeStr(t, sep, GMT)
    {
       if (!t)
            t = new Date();
       var h = t.getHours();
       if (GMT) h = t.getUTCHours();       
       return LZ(h) + sep + LZ(t.getMinutes()) + sep + LZ(t.getSeconds()) + "." + LZ3(t.getMilliseconds());
    }
  
    function strTimeSQ()
    {
       return "[" + fullTimeStr(false, ":", false) + "]";
    }
  
    function dumpObject(data, only_props = false, recursive = false)
    {
      var result = "";
      for (var index in data)
      {
        var val = data[index];
        var vt = typeof val;
        var row = index + " = ";;
         
        
        if (vt == "number" || vt == "string")
           row = row + String(val) + "\n ";
        else 
        if (vt == "object" && recursive) 
           row = row + "{" + dumpObject(val) + "}\n";          
        else
        {
            if (only_props)
                row = '';
            else        
                row = row + "@" + vt + "\n ";
        }           
       
        if (row != '') 
            result = result + row;
      }
    
      return result;
    }
  
 
    
if (module && module.exports) // NodeJS compat
{ 
  module.exports.dateToStr = dateToStr;
  module.exports.timeToStr = timeToStr;
  module.exports.fullTimeStr = fullTimeStr;
  module.exports.strTimeSQ = strTimeSQ;
  module.exports.dumpObject = dumpObject;
}       