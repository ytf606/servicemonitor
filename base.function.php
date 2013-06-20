<?php
function __autoload($classname)
{
    $filename = "service/" .  $classname . ".class.php";
    include_once($filename);
}
function error($errno = 0, $errmsg = "success")
{
    return json_encode(array('errno'=>$errno, 'errmsg'=>$errmsg));
}
function pub_data(){
    $f = new SaeFetchurl();
    return $f->fetch( $GLOBALS['public_image_file'] );
}
/**
 * 外部邮件发送
 *
 *
 */
function common_mail($mail_subject, $mail_body, $mail_type = "HTML")
{
    include_once('service/mail.class.php');
    $mail_from_info = $GLOBALS['config']['common_mail'];
    $smtp_server = $mail_from_info['server'];
    $smtp_server_port = $mail_from_info['port'];
    $smtp_user = $mail_from_info['user'];
    $smtp_pass = $mail_from_info['pass'];
    $smtp_mail_from = $mail_from_info['mail'];
    $smtp_mail_to = $GLOBALS['config']['mailto'];
    $mail_subject = mb_convert_encoding( $mail_subject, 'gbk', 'utf-8');
    $mail_body = mb_convert_encoding( $mail_body, 'gbk', 'utf-8');
    $mail_type = empty($mail_type) ? "HTML" : $mail_type;
    $smtp = new SMTP($smtp_server, $smtp_server_port, true, $smtp_user, $smtp_pass);
    $smtp->debug = true;
    return @$smtp->sendmail($smtp_mail_to, $smtp_mail_from, $mail_subject, $mail_body, $mail_type);
}
/**
 * 格式化输入异常服务信息
 * @param $service string 服务名 见config.php
 * @param $method string  服务对应的方法名 见config.php
 * @param $error  array  包括errno、errmsg 
 * @return string 
 *
 */
function format_output($service, $method, $error)
{
    $info = "[" . date("Y-m-d H:i:s") . "]";
    $info .= " 服务:" . $service . " 方法:" . $method . " 操作失败.errno:" . $error['errno'] . " errmsg:" . $error['errmsg'];
    return $info;
}
?>
