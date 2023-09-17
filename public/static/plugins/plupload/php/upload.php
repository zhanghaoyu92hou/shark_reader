<?php
error_reporting(0);
require_once 'Upload.class.php';
$path = "/tmp/normal";
$root = $_SERVER['DOCUMENT_ROOT'];
$upload_dir = $root.$path;
if(!is_dir($upload_dir)){
    mkdir($upload_dir,0777,true);
}
$upload_dir .= '/';
$path .= '/';
$config = array(
    'maxSize'    =>    1024*1024*2,
    'rootPath'   =>    $upload_dir,
    'saveName'   =>    'uniqid',
    'exts'       =>    array('jpg','png','jpeg'),
    'autoSub'    =>    true,
    'subName'    =>    array('date','Ymd')
);
$upload = new Upload($config);
$info = $upload->upload();
if(!$info) {
    returnMessage($upload->getError());
}else{
    $url = ltrim($path,'.').$info['file']['savepath'].$info['file']['savename'];
    returnMessage('success',0,array('img'=>$url));
}
function returnMessage($message,$error=1,$data=array()){
    $message = strtolower($message);
    if(in_array($message,array('ok','success'))){
        $error = 0;
    }
    $result = array(
        'error' => $error,
        'msg' => $message,
        'list' => $data
    );
    echo json_encode($result);
    exit;
}