<?php
header("Content-Type content='text/html; charset=utf-8'");
//token = '470789d132db1bc0ed6ddc3f9617af9d';
$token = $_REQUEST['token'];
if ( !$token || $token != '123456' ) {
    echo json_encode(array('errno'=>-1, 'errmsg'=>'no privilge'));
    exit;
}
include "config.php";
include "base.function.php";
$ret = $err_info = $err_service = $check_service = array();
$min = date("i");
$gb = $GLOBALS['config'];
$service = $_REQUEST['service'];
if ($service) {
    $c_name = $gb['prefix'] . $service;
    $obj = new $c_name;
} else {
    foreach ($gb['service'] as $service=>$method) {
        $rate = array_key_exists($service, $gb['rate']) ? $gb['rate'][$service] : 2;
        if ( ($min % $rate) != 0 ) {
            continue;
        }
        $c_name = $gb['prefix'] . $service;
        $obj = new $c_name;
        foreach ($method as $m) {
            $ret[$service][$m] = $obj->{$service . "_" . $m}(); 
        }
    }
}
foreach ($ret as $k=>$v) {
    $check_service[] = $service_name = $k;
    foreach ($v as $kk=>$vv) {
        $method_name = $kk;
        $result = (array)json_decode($vv);
        if ($result['errno'] != 0) {
            $err_service[] = $service_name;
            $err_info[] = format_output($service_name, $method_name, $result);
        }
    }
}
if ( $err_service ) {
    echo date("Y-m-d H:i:s") . " [error] check the service of " . implode(",", $check_service) . " service: " . implode(",", $err_service) . " errors\n";
    echo date("Y-m-d H:i:s") . " [error] error service method: " . implode(",", $err_info) . "\n";
    $subject = "服务 " . implode(",", $err_service) . " 异常";
    $body = "异常信息如下:<br />" . implode("<br />", $err_info);
    $mail_result = common_mail($subject, $body);
    echo date("Y-m-d H:i:s") . " [error] send mail info \n\t\t"; 
    var_dump($mail_result);
    echo "\n\n";
} else {
    echo date("Y-m-d H:i:s") . " [info] check the service of " . implode(",", $check_service) . " and no any service has error\n\n";
}
