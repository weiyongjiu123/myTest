<?php
SimulationLogin::$loginData['username'] = '';     //在此处输入你的学号
SimulationLogin::$loginData['password'] = '';     //在此处输入你的密码
SimulationLogin::index();

/**
 * Class SimulationLogin 模拟登录
 * @author wyj
 * @description 实现的步骤：
 *  1.用get请求来获取学校登录界面的html字符串
 *  2.然后提取模拟登录所需要的字符串和随机数
 *  3.携带组合的数据提交给学校服务器
 *  4.登陆通过后，就可以获取你需要的课表或其他信息
 *  更详细的步骤请自行分析源码
 */
class SimulationLogin {
    private static  $cookie_file;
    private static $http = [
        'login'=>'http://class.sise.com.cn:7001/sise/login.jsp',      //登录界面
        'schedule'=>'http://class.sise.com.cn:7001/sise/module/student_schedular/student_schedular.jsp',        //课表界面
        'dataToLogin'=>'http://class.sise.com.cn:7001/sise/login_check_login.jsp',              //登录地址
        'perMsg'=>'http://class.sise.com.cn:7001/SISEWeb/pub/course/courseViewAction.do?method=doMain&studentid=VPix7t5zG8w='   //个人信息界面
    ];
    public static $loginData = [
        'username'=>null,
        'password'=>null
    ];
    //初始化模拟登录必要的东西
    static function init()
    {
        if(!self::$loginData['username']||!self::$loginData['password'])
        {
            echo '<h2>你还未在代码的上方输入学号或密码</h2>';
            die;
        }
        self::$cookie_file = dirname(__FILE__).'/cookie.txt';
    }
    //程序入口函数
    static function index()
    {
        self::init();
        $loginHtml = self::getLoginHtml();
        $dataRes = self::getValidStr($loginHtml);
        $loginData = self::getLoginData($dataRes);
        $loginRes = self::login($loginData);
        if($loginRes)
        {
            $str = self::getHtmlByGet(self::$http['perMsg']);
            $perMsgArr = self::getPerMsg($str);
            self::perMsgDisplay($perMsgArr);
            $str = self::getHtmlByGet(self::$http['schedule']);
            $scheduleArr = self::getScheduleArr($str);
            self::scheduleDisplay($scheduleArr);
        }else{
            echo '<h2>模拟登录失败，请检查学号和密码是否正确</h2>';
        }
    }
    //获取个人的信息，演示的代码只获取少量信息，其它的靠你了
    static function getPerMsg($htmlStr)
    {
        preg_match_all('/class="td_left">[\S\s]*?align="left">([\S\s]*?)<\/div>/',$htmlStr,$arr);
        return [
            'number'=>trim($arr[1][0]),             //学号
            'name'=>trim($arr[1][1]),               //姓名
            'whenComeIn'=>trim($arr[1][2]),         //入学年
            'majors'=>trim($arr[1][3]),             //专业
            'email'=>trim($arr[1][5]),              //邮箱
            'teacher'=>trim($arr[1][6]),            //导师
            'counsellor'=>trim($arr[1][7])          //辅导员
        ];
    }
    //组合提交给学校服务器的数据，一个也不能少，否则登录失败
    static function getLoginData($data)
    {
        return [
            'username'=>self::$loginData['username'],
            'password'=>self::$loginData['password'],
            $data['validKey'] => $data['validValue'],
            'random'=>$data['random'],
        ];
    }
    //通过登录界面的html字符串来获取登录学校服务器所需要验证的字符串和随机数
    static function getValidStr($strRes)
    {
        preg_match('/<input type="hidden" name="([\S\s]*?)"[\S\s]*?value="([\S\s]*?)"/',$strRes,$validStr);
        preg_match('/<input id="random"   type="hidden"  value="([\s\S]*?)"/',$strRes,$random);
        return [
            'validKey'=>$validStr[1],
            'validValue'=>$validStr[2],
            'random'=>$random[1]
        ];
    }
    //获取登录界面的html字符串
    static function getLoginHtml()
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, self::$http['login']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);  //表示不返回header信息
        curl_setopt($ch, CURLOPT_COOKIEJAR,  self::$cookie_file); //存储cookies
        $output = curl_exec($ch);
        curl_close($ch);
        if ($output === FALSE) {
//            return "CURL Error:".curl_error($ch);
            return false;
        } else {
            return $output;
        }
    }
    //模拟登录
    static function login($data)
    {
        $ch = curl_init ();
        curl_setopt ( $ch, CURLOPT_URL, self::$http['dataToLogin'] );//地址
        curl_setopt ( $ch, CURLOPT_POST, 1 );//请求方式为post
        curl_setopt ( $ch, CURLOPT_HEADER, 0 );//不打印header信息
        curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );//返回结果转成字符串
        curl_setopt ( $ch, CURLOPT_POSTFIELDS, http_build_query($data) );//post传输的数据。
        curl_setopt($ch, CURLOPT_COOKIEFILE, self::$cookie_file);
        $return = curl_exec ( $ch );
        curl_close ( $ch );

        preg_match('/<script>([\S\s]*?)<\/script/',$return,$res);
        if($res[1] == 'top.location.href=\'/sise/index.jsp\'')      //这个地方判断是否登录成功
        {
            return true;
        }
        return false;
    }
    //在已经登录的前提之下，用get方式获取个人的信息，如课表
    static function getHtmlByGet($httpUrl)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $httpUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);  
        curl_setopt($ch, CURLOPT_COOKIEFILE, self::$cookie_file);
        $output = curl_exec($ch);
        curl_close($ch);
        $output = iconv('GBK', 'UTF-8', $output); //将字符串的编码从GBK转到UTF-8
        return $output;
    }
    //根据课表的html字符串用正则提取课表信息
    static function getScheduleArr($htmlStr)
    {
        preg_match_all('/<td width=\'10%\' align=\'left\' valign=\'top\' class=\'font12\'>([\S\s]*?)<\/td>/',$htmlStr,$scheduleArr);
        $arr = [];
        for ($i=1;$i<=17;$i++)
        {
            for ($j=1;$j<=7;$j++)
            {
                for ($k=1;$k<=8;$k++)
                {
                    $arr[$i][$j][$k]['classroom'] = 0;
                    $arr[$i][$j][$k]['subject'] = 0;
                }
            }
        }
        foreach ($scheduleArr[1] as $key => $value)
        {
            if($value == '&nbsp;')
            {
                continue;
            }
            $classArr = explode(',',$value);
            foreach ($classArr as $v)
            {
                $str = str_replace('周','',$v);
                preg_match('/[\S\s]*?\(([\S\s]*?)\)/',$str,$weekStr);
                $weekArr = explode(' ',$weekStr[1]);
                preg_match('/^([\S\s]*?)\([\S\s]*?\[([\S\s]*?)\]/',$str,$msgArr);
                for ($i=2;$i<=count($weekArr)-2;$i++)
                {
                    $arr[$weekArr[$i]][($key+1)%7 ? ($key+1)%7: 7][ceil(($key+1)/7)]['classroom'] = $msgArr[2];
                    $arr[$weekArr[$i]][($key+1)%7 ? ($key+1)%7: 7][ceil(($key+1)/7)]['subject'] = $msgArr[1];
                }
            }
        }
        return $arr;
    }
    //将数组打印出来
    static function write($data)
    {
        echo '<pre>';
        print_r($data);
        echo '</pre>';
    }
    //输出个人信息的数组
    static function perMsgDisplay($data)
    {
        echo '-----------------------个人信息---------------------<br>';
        self::write($data);
    }
    //输出课表信息数组
    static function scheduleDisplay($data)
    {
        echo '-----------------------------------课表-------------------<br>';
        echo '下面打印的是一个三维数组<br>';
        echo '$arr[i][j][k] : i表示第几周，j表示星期几，k表示第几节课';
        self::write($data);
    }

}


