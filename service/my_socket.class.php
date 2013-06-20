<?php
class my_socket
{
    private $addr = 'lib.sinaapp.com';
    private $port = '80';
    private $connect_timeout = '2';
    private $response_timeout = '5';
    private $request_header = '';
    public function __construct(){
        $request_header = "GET /index.php HTTP/1.0\r\n";
        $request_header.= "Host: ".$this->addr."\r\n";
        $request_header.= "Accept:*/*\r\n";
        $request_header.= "Connection: close\r\n\r\n";
        $this->request_header = $request_header;
    }
    public function socket_connect( $rep_timeout=false ){
        $rep_timeout = $rep_timeout ? $rep_timeout : $this->response_timeout;
        $fp = fsockopen($this->addr, $this->port, $errno, $errstr, $this->connect_timeout);
        if (!$fp) {
            return error(-1, 'open fsocket failed');
        } else {
            stream_set_blocking($fp, true);
            stream_set_timeout( $fp, $rep_timeout);
            fwrite($fp, $this->request_header);
            $rep = stream_get_contents($fp);
            $meta = stream_get_meta_data($fp);
            if($meta['timed_out']){
                if( $rep_timeout>10 )
                    return error(-9, 'transport time out');
                return $this->ck_connect( ++$rep_timeout );
            }
            fclose($fp);
            if( substr_count($rep, 'sae')>0 )
                return error(0, 'success');
            return error(-10, 'socket service failed');   
        }
    }
}


?>
