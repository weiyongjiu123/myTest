//微信接入机器人的js代码

/**
 * @author wyj
 * @description 微信网页版接入机器人
 * 1.登陆微信网页版
 * 2.选择聊天窗口
 * 3.控制台输出_chatContent变量 ，找到自己选择的窗口的Id
 * 4.在下列代码找到 _chatContent.filehelper = new ArrayOfMine(); ，把filehelper改为窗口Id
 * 5.本代码需要外网能访问的后台服务器，把对应的php下载并部署。如果没有后台地址，保持默认也可以
 * 6.需改后，将代码复制到控制台上并运行，然后在聊天窗口发一条信息，就可以看到机器人回复了
 *
 */




var test_isRobotSend = true;
function inheritObject(o) {
    function F() {
    }
    F.prototype = o;
    return new F();
}
function inheritPrototype(subClass, superClass) {
    var p = inheritObject(superClass.prototype);
    p.constructor = subClass;
    subClass.prototype = p;
}
function ArrayOfMine() {
    var args = arguments
        , len = args.length
        , i = 0
        , args$1 = [];
    for (; i < len; i++) {
        if (Array.isArray(args[i])) {
            args$1 = args$1.concat(args[i]);
        }
        else {
            args$1.push(args[i])
        }
    }
    var arr = Array.apply(null, args$1);
    arr.__proto__ = ArrayOfMine.prototype;
    return arr;
}
inheritPrototype(ArrayOfMine, Array);
ArrayOfMine.prototype.push = function (value) {
    console.log(value.Content);       //打印聊天信息
    if (test_isRobotSend) {           //判断机器人是否该回复
        test_getRobotRely(value.Content);
        test_isRobotSend = false;
    }else{
        test_isRobotSend = true;
    }
    return Array.prototype.push.apply(this, arguments);
};
//监听对应窗口的聊天信息
_chatContent.filehelper = new ArrayOfMine();
//模拟人工发送消息
function test_send(resFromPHP) {
    $('#editArea').text(resFromPHP.text);
    $('#editArea').trigger("input");
    $('a.btn.btn_send').click();
}
//用jsonp方法跨域获取机器人恢复的内容
function test_getRobotRely(content) {
    $.ajax({
        type:'GET',
        dataType:'jsonp',
        data:{"content":content},
        jsonp:'callback',
        jsonpCallback:'getName',
        url:'https://smallsi.com:9504'          //这个是后台的地址，如果你有远程服务器的后台地址，可以把这地址改为你的,并下载php代码部署
    })                                          //如果没有后台地址，默认就可以了，无效修改，也不需要下载php代码
}
 
