<?php
class my_mail
{
    private $to = 'xxx@sina.com';
    private $subject = "monitor test";
    private $body = "monitor test";
    private $from = "xxx@sina.com";
    private $password = "xxxx";
    private $mail = '';
    public function __construct()
    {
        $this->mail = new SaeMail();
    }

    public function mail_send()
    {
        $this->mail->clean();
        $this->mail->quickSend($this->to, $this->subject, $this->body, $this->from, $this->password);
        if ( $this->mail->errno() != 0 ) {
            return error($this->mail->errno(), 'send mail failed errmsg:' . $this->mail->errmsg());
        } else {
            return error(0, 'success');
        }
    }
}
?>
