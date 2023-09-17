<?php
namespace app\channel\model;
use app\channel\model\Common;
use think\Db;

class cMessage extends Common{
    
    //保存公告阅读记录
    public static function saveRead($id){
        global $loginId;
        $read_id = Db::name('MessageRead')->where('message_id','=',$id)->where('channel_id','=',$loginId)->value('id');
        if(!$read_id){
            $data = [
                'message_id' => $id,
                'channel_id' => $loginId,
                'create_time' => time()
            ];
            Db::name('MessageRead')->insert($data);
        }
    }
}