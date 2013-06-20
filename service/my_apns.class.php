<?php
class my_apns
{
    private $cert_id = xxx;
    private $device_token = "xxxx";
    private $message = '';
    private $body = '';
    public function __construct()
    {
        $this->message = date('Y-m-d H:i:s') . ": \n" . '测试消息 from SAE';
        $this->body = array('aps' => array('alert' => $this->message, 'badge' => 1, 'sound' => 'in.caf'));
    }

    public function apns_send_v1()
    {
        $apns = new SaeAPNS();
        $result = $apns->push($this->cert_id, $this->body, $this->device_token);
        if ( $apns->errno() != 0 ) {
            return error(-2, 'send apns v1 message error errmsg:' . $apns->errmsg());
        } else {
            return error(0, 'success');
        }
    }
    public function apns_send_v2()
    {
       include_once('apnsv2.class.php');
       $apnsv2 = new SaeAPNSv2();
       $result = $apnsv2->push($this->cert_id, $this->body, $this->device_token);
        if ( $apnsv2->errno() != 0 ) {
            return error(-2, 'send apns v2 message error errmsg:' . $apnsv2->errmsg());
        } else {
            return error(0, 'success');
        }
    }
}
?>
