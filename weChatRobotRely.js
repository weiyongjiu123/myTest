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
        url:'https://smallsi.com:9504'
    })
}
