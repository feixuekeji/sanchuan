<?php
require_once('Single.php');
// 命令行入口点
if (php_sapi_name() === 'cli') {
    $name = $argv[1];
    $status = $argv[2];
    var_dump($argv);
} else {
        $name = isset($_GET['name']) ? $_GET['name'] : '';
        $status = isset($_GET['status']) ? $_GET['status'] : 0;
}

$single = new Single();
//重试次数
$tryCount = 4;
$i = 0;
//失败，重复请求
while ($i < $tryCount) {
    $res = $single->open($name,$status);
    echo $res['msg']."\n";
    if ($res['code']){
        break;
    }
    $i++;
    sleep(5);
}



