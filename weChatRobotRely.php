<?php
Chat::run();

/**
 * Class Chat 
 * @author wyj
 * @description 本程序为远程调用图灵机器人第三方平台，所以需要到
 *  图灵机器人官方注册一个账号，并获取调用api的key
 */
class Chat{
    static private $openUrl = 'http://www.tuling123.com/openapi/api';
    static private $key = '';       //这个地方输入你在图灵机器人获取的key
    static public function run()
    {
        if(isset($_GET['content'])) {
            $data = [
                'key' => self::$key,
                'info' => $_GET['content'],
                'userid' => '123456'
            ];
        }else{
           echo '('.json_encode([
                'code'=>0,
                'text'=>'不要意思，机器人故障了'
            ],JSON_UNESCAPED_UNICODE).')';
            return;
        }
        self::getResMsg($data);
    }
    //远程调用
    static public function getResMsg($data)
    {
        $ch = curl_init ();
        curl_setopt ( $ch, CURLOPT_URL, self::$openUrl );//地址
        curl_setopt ( $ch, CURLOPT_POST, 1 );//请求方式为post
        curl_setopt ( $ch, CURLOPT_HEADER, 0 );//不打印header信息
        curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );//返回结果转成字符串
        curl_setopt ( $ch, CURLOPT_POSTFIELDS, http_build_query($data) );//post传输的数据。
        $return = curl_exec ( $ch );
        curl_close ( $ch );
        if($return === false)
        {
            echo 'test_send('.json_encode([
                'code'=>0,
                'text'=>'不要意思，机器人故障了'
            ],JSON_UNESCAPED_UNICODE).')';
            return;
        }else{
            echo "test_send({$return})";
            return;
        }
    }
} 

