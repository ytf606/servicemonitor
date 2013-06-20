<?php
$GLOBALS['config']['prefix'] = "my_";
$GLOBALS['public_image_file'] = 'http://lib.sinaapp.com/xxx/10k.jpg';

$GLOBALS['config']['common_mail']['mail'] = "xxx@sina.com";
$GLOBALS['config']['common_mail']['user'] = "xxx";
$GLOBALS['config']['common_mail']['pass'] = "xxx";
$GLOBALS['config']['common_mail']['server'] = "smtp.sina.com";
$GLOBALS['config']['common_mail']['port'] = 25;
$GLOBALS['config']['mailto'] = 'xxx';


$GLOBALS['config']['service']['mc'] = array('connect', 'set');
$GLOBALS['config']['service']['kv'] = array('init', 'add', 'set', 'replace', 'delete', 'mget', 'pkrget');
$GLOBALS['config']['service']['tq'] = array('addtask');
$GLOBALS['config']['service']['socket'] = array('connect');
$GLOBALS['config']['service']['fetchurl'] = array('fetch');
$GLOBALS['config']['service']['image'] = array('info');
$GLOBALS['config']['service']['storage'] = array('write', 'read', 'update', 'delete');
$GLOBALS['config']['service']['mysql'] = array('connect_m', 'connect_s', 'create_table', 'insert', 'sync', 'update', 'delete', 'truncate', 'droptable');
$GLOBALS['config']['service']['mail'] = array('send');
$GLOBALS['config']['service']['apns'] = array('send_v1', 'send_v2');
$GLOBALS['config']['service']['segment'] = array('seg');
$GLOBALS['config']['service']['fts'] = array('addDoc', 'modifyDoc', 'search', 'delete');
$GLOBALS['config']['rate'] = array('mc'=>2, 'kv'=>2, 'tq'=>5, 'socket'=>5, 'fetchurl'=>5, 'image'=>5, 'storage'=>3, 'mysql'=>3, 'mail'=>3, 'apns'=>5, 'segment'=>5, 'fts'=>5);
