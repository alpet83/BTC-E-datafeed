<?php

/*
  Класс WebSocket сервера, сделан благодаря исходному коду примеров https://habrahabr.ru/post/209864/
  ООП переработка by alpet 2017  
 
*/

 function ws_handshake($connect) 
 {
    $info = array();

    $line = fgets($connect);
    $header = explode(' ', $line);
    $info['method'] = $header[0];
    $info['uri'] = $header[1];

    //считываем заголовки из соединения
    while ($line = rtrim(fgets($connect))) {
        if (preg_match('/\A(\S+): (.*)\z/', $line, $matches)) {
            $info[$matches[1]] = $matches[2];
        } else {
            break;
        }
    }

    $address = explode(':', stream_socket_get_name($connect, true)); //получаем адрес клиента
    $info['ip'] = $address[0];
    $info['port'] = $address[1];

    if (empty($info['Sec-WebSocket-Key'])) {
        return false;
    }

    //отправляем заголовок согласно протоколу вебсокета
    $SecWebSocketAccept = base64_encode(pack('H*', sha1($info['Sec-WebSocket-Key'] . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));
    $upgrade = "HTTP/1.1 101 Web Socket Protocol Handshake\r\n" .
        "Upgrade: websocket\r\n" .
        "Connection: Upgrade\r\n" .
        "Sec-WebSocket-Accept:$SecWebSocketAccept\r\n\r\n";
    fwrite($connect, $upgrade);

    return $info;
 }
   
  
 class WebSocketServer
 {
   public    $connects = array();
   public    $socket = false;    
   
   function  __construct($bind = '0.0.0.0:8000')
   {   
      $errno = 0;
      $errstr = '?';
      
      $this->connects =  array();
      $this->socket = stream_socket_server("tcp://$bind", $errno, $errstr);      
      if (!$this->socket)          
         $this->onError("WebSocket failed stream_socket_server function  $errstr ($errno)\n");      
   }
   
   function __destruct()
   {
     $this->stop();  
   }
   
   function drop($connect) 
   {
     fclose($connect);
     $i = array_search($connect, $this->connects);
     unset($this->connects[$i]);
     $this->onClose($connect);//вызываем пользовательский сценарий       
   }
   
   function stop()   
   {
      $cons = $this->connects;   
      foreach($cons as $connect)
         $this->drop($connect);
        
      if ($this->socket) fclose($this->socket);
      $this->socket = false;
   }
   
   function accept_new()
   {
      $connect = stream_socket_accept($this->socket, -1);
      if ($connect && $info = ws_handshake($connect)) 
      {
         array_push ($this->connects, $connect);    //добавляем его в список необходимых для обработки
         $this->onOpen($connect, $info);  //вызываем пользовательский сценарий
      }                
   }
      
   function work()   
   {
      //формируем массив прослушиваемых сокетов:
      $read = $this->connects;
      $read []= $this->socket;
      $write = $except = null;
      $data  = "";
      $count = 0;
  
      if (!stream_select($read, $write, $except, null)) //ожидаем сокеты доступные для чтения (без таймаута)
          return -1;
      
  
      if (in_array($this->socket, $read)) 
      {   //есть новое соединение    
          //принимаем новое соединение и производим рукопожатие:
          $this->accept_new();
          $i = array_search($this->socket, $read);      
          unset($read[$i]);
      }
  
      foreach($read as $connect) 
      {   //обрабатываем все соединения
          $data = fread($connect, 100000);
          if (!$data) { //соединение было закрыто
              $this->drop($connect);
              continue;
          }  
          $this->onMessage($connect, $data);//вызываем пользовательский сценарий
          $count ++;
      } // work
      return $count;
   }
    
   /*------------------------------------------------------------------*/    
   function encode($payload, $type = 'text', $masked = false)
   {
        $frameHead = array();
        $payloadLength = strlen($payload);
        $codes = array( 'text'=> 129, 'close' => 136, 'ping' => 137, 'pong' => 138);
        $frameHead[0] = $codes[$type];
        if (!$frameHead[0]) $frameHead[0] = 129; 
        // set mask and payload length (using 1, 3 or 9 bytes)
        if ($payloadLength > 65535) {
            $payloadLengthBin = str_split(sprintf('%064b', $payloadLength), 8);
            $frameHead[1] = ($masked === true) ? 255 : 127;
            for ($i = 0; $i < 8; $i++) {
                $frameHead[$i + 2] = bindec($payloadLengthBin[$i]);
            }
            // most significant bit MUST be 0
            if ($frameHead[2] > 127) {
                return array('type' => '', 'payload' => '', 'error' => 'frame too large (1004)');
            }
        } elseif ($payloadLength > 125) {
            $payloadLengthBin = str_split(sprintf('%016b', $payloadLength), 8);
            $frameHead[1] = ($masked === true) ? 254 : 126;
            $frameHead[2] = bindec($payloadLengthBin[0]);
            $frameHead[3] = bindec($payloadLengthBin[1]);
        } else {
            $frameHead[1] = ($masked === true) ? $payloadLength + 128 : $payloadLength;
        }
    
        // convert frame-head to string:
        foreach (array_keys($frameHead) as $i) {
            $frameHead[$i] = chr($frameHead[$i]);
        }
        if ($masked === true) {
            // generate a random mask:
            $mask = array();
            for ($i = 0; $i < 4; $i++) {
                $mask[$i] = chr(rand(0, 255));
            }
    
            $frameHead = array_merge($frameHead, $mask);
        }
        $frame = implode('', $frameHead);
    
        // append payload to frame:
        for ($i = 0; $i < $payloadLength; $i++) {
            $frame .= ($masked === true) ? $payload[$i] ^ $mask[$i % 4] : $payload[$i];
        }
    
        return $frame;
    }
    /*------------------------------------------------------------------*/
    
    function decode($data)
    {
        $unmaskedPayload = '';
        $decodedData = array();
    
        // estimate frame type:
        $firstByteBinary = sprintf('%08b', ord($data[0]));
        $secondByteBinary = sprintf('%08b', ord($data[1]));
        $opcode = bindec(substr($firstByteBinary, 4, 4));
        $isMasked = ($secondByteBinary[0] == '1') ? true : false;
        $payloadLength = ord($data[1]) & 127;
    
        // unmasked frame is received:
        if (!$isMasked) {
            return array('type' => '', 'payload' => '', 'error' => 'protocol error (1002)');
        }
        
        $set = array(false, 'text', 'binary', false, false, false, false, false, 'close', 'ping', 'pong');

        $decodedData['type'] = $set[$opcode];
        if (!$decodedData['type'])
                return array('type' => '', 'payload' => '', 'error' => 'unknown opcode (1003)');
        
    
        if ($payloadLength === 126) {
            $mask = substr($data, 4, 4);
            $payloadOffset = 8;
            $dataLength = bindec(sprintf('%08b', ord($data[2])) . sprintf('%08b', ord($data[3]))) + $payloadOffset;
        } elseif ($payloadLength === 127) {
            $mask = substr($data, 10, 4);
            $payloadOffset = 14;
            $tmp = '';
            for ($i = 0; $i < 8; $i++) {
                $tmp .= sprintf('%08b', ord($data[$i + 2]));
            }
            $dataLength = bindec($tmp) + $payloadOffset;
            unset($tmp);
        } else {
            $mask = substr($data, 2, 4);
            $payloadOffset = 6;
            $dataLength = $payloadLength + $payloadOffset;
        }
    
        /**
         * We have to check for large frames here. socket_recv cuts at 1024 bytes
         * so if websocket-frame is > 1024 bytes we have to wait until whole
         * data is transferd.
         */
        if (strlen($data) < $dataLength) {
            return false;
        }
    
        if ($isMasked) {
            for ($i = $payloadOffset; $i < $dataLength; $i++) {
                $j = $i - $payloadOffset;
                if (isset($data[$i])) {
                    $unmaskedPayload .= $data[$i] ^ $mask[$j % 4];
                }
            }
            $decodedData['payload'] = $unmaskedPayload;
        } else {
            $payloadOffset = $payloadOffset - 4;
            $decodedData['payload'] = substr($data, $payloadOffset);
        }
    
        return $decodedData;
   }
   /*------------------------------------------------------------------*/  
   // перегружаемые функции обработки событий

   function onError($err_msg)
   {
     die($err_msg);
   }
  
   function onOpen($connect, $info) 
   {
     log_msg("ws:open");
     print_r($info);
     // fwrite($connect, encode('Привет'));
   }
  
   function onClose($connect) 
   {
     log_msg("ws:close");
   }
  
   function onMessage($connect, $data) 
   {
     $data = $this->decode($data);
     $type = $data['type'];
     $text = $data['payload'];
     if ($type == 'text')          
         $this->onText($connect, $text);
         
     if ($type == 'ping')
         fwrite($connect, encode('', 'pong'));    
   }
   
   function onText($connect, $text)
   { 
     echo "$text\n";
   }    
    
} // class WebSocket ========================================



