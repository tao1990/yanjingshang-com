<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8" />
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=no" />
<meta http-equiv="Cache-Control" content="no-siteapp" />
<meta name="apple-mobile-web-app-capable" content="yes" />
<meta name="format-detection" content="telephone=no" />
<meta name="screen-orientation" content="portrait" />
<meta name="x5-orientation" content="portrait" />
<meta name="full-screen" content="yes" />
<meta name="x5-fullscreen" content="true" />
<title><?php echo $this->_var['page_title']; ?></title>
<meta name="Keywords" content="<?php echo $this->_var['keywords']; ?>" />
<meta name="Description" content="<?php echo $this->_var['description']; ?>" />
<!--<link rel="stylesheet" type="text/css" href="css/common.css"/>-->
<link rel="stylesheet" type="text/css" href="css/style.css"/>
<link rel="stylesheet" type="text/css" href="http://file.yunjingshang.com/js/mlayer.css"/>
<body>
<?php echo $this->fetch('library/header_login.lbi'); ?>
<style>
.mlayer-container.fromBottom{width:100%;left:0 !important;margin-left:0 !important;}
body{background:#fff;}
@font-face {font-family:'iconfont';
    src:url('font/iconfont.eot');
    src:url('font/iconfont.woff') format('woff'),url('font/iconfont.ttf') format('truetype'),url('font/iconfont.svg#iconfont') format('svg');
}
.iconfont{
    font-family:"iconfont" !important;
    font-size:16px;font-style:normal;
    -webkit-font-smoothing: antialiased;
    -webkit-text-stroke-width: 0.2px;
    -moz-osx-font-smoothing: grayscale;
}
/*login*/
.login{width: 282px;margin: 0 auto;padding: 20px 19px;overflow: hidden;}
.login ul{width:100%}
.login li{width:100%;margin-bottom: 10px;float: left;}
.user-info input {width: 241px;height: 42px;padding-left: 41px;font-size: 12px;color: #afafaf;background-color: transparent;line-height: 42px;-webkit-appearance: none;box-sizing: content-box;border: 0;border-radius: 0;box-shadow: none;outline: none;}

.user-info.name{width: 282px;height: 42px;background: url(<?php echo $this->_var['image_url']; ?>wap/images/user_input.png) no-repeat;}
.user-info.pwd{width: 282px;height: 42px;background: url(<?php echo $this->_var['image_url']; ?>wap/images/pw_input.png) no-repeat;}

.user_lg_submit{width:100%;background-color: #76d5e9;border: 0;border-radius: 5px;height:3rem;font-size:1.2rem;color:white; font-weight: bold;}

.zc_button{width: 66px;height: 34px;background: url(<?php echo $this->_var['image_url']; ?>wap/images/zc_button.png) no-repeat;line-height: 34px;text-align: center;color: #7c7c7c;float: left;display: inline-block;float:right}
.otherlg>a{display:inline-block;vertical-align:middle;margin:0 .25rem;width:4rem;height:4rem;background:url(http://file.easeeyes.com/wap/images/zhifubao.png) no-repeat center;background-size:100% auto;}
.otherlg>a.alipay{}
.otherlg>a.qq{background-image:url(http://file.easeeyes.com/wap/images/qq2.png)}
.otherlg>a.wb{background-image:url(http://file.easeeyes.com/wap/images/sina.png)}
/*zhuce*/
.register{margin: 0 auto;padding:2rem;overflow: hidden;}
.register ul{width:100%}
.register li{width:100%;margin-bottom: 10px;float: left;}
.user-info.yz_code{position:relative;}
.user-info input {width: 241px;height: 42px;padding-left: 41px;font-size: 12px;color: #afafaf;background-color: transparent;line-height: 42px;-webkit-appearance: none;box-sizing: content-box;border: 0;border-radius: 0;box-shadow: none;outline: none;}

.user-info.name{width: 282px;height: 42px;background: url(<?php echo $this->_var['image_url']; ?>wap/images/user_input.png) no-repeat;}
.user-info.pwd{width: 282px;height: 42px;background: url(<?php echo $this->_var['image_url']; ?>wap/images/pw_input.png) no-repeat;}

.us_Submit_reg{width:100%;background-color: #76d5e9;border: 0;border-radius: 5px;height:3rem;font-size:1.2rem;color:white; font-weight: bold;  margin-top:2rem;}

.zc_button{width: 66px;height: 34px;background: url(<?php echo $this->_var['image_url']; ?>wap/images/zc_button.png) no-repeat;line-height: 34px;text-align: center;color: #7c7c7c;float: left;display: inline-block;float:right}

/*forget pwd*/
.for_pwd{margin: 0 auto;padding:2rem;overflow: hidden;}
.for_pwd ul{width:100%}
.for_pwd li{width:100%;margin-bottom:.5rem;}
.user-info input {width: 24rem;height:2.2rem;padding:1rem 0 1rem 4rem;font-size: 12px;color: #afafaf;background-color: transparent;line-height:2.2rem;-webkit-appearance: none;box-sizing: content-box;border: 0;border-radius: 0;box-shadow: none;outline: none;}
.user-info input+img{position:absolute;top:1rem;right:1rem;height:2.2rem;}
.user-info.name{width: 100%;height:4.2rem;background: url(<?php echo $this->_var['image_url']; ?>wap/images/user_input.png) no-repeat;background-size:100% auto;position:relative;}
.user-info #username_r{}
.user-info #get_code{
    overflow: hidden;
    width: 30%;
    height:3rem;
    border-left: 1px solid #ccc;
    line-height:3rem;
    text-align: center;
    cursor: pointer;
    font-size: 1.1rem;
    background:none;
	color:#76d5e9;
    padding: 0;
    position:absolute;
    right:.5rem;
    top:.5rem;
}
.user-info.pwd{width: 100%;height:4.2rem;background: url(<?php echo $this->_var['image_url']; ?>wap/images/pw_input.png) no-repeat;background-size:100% auto;}
.user-info.yz_code{width: 100%;height:4.2rem;background: url(<?php echo $this->_var['image_url']; ?>wap/images/yz_input.png) no-repeat;background-size:100% auto;}
.step_2{display: none}
.step_3{display: none}

/*account_list*/
.account_list li,.account_list_div{border-top: 1px solid #fff;border-bottom: 1px solid #ccc;padding-left: 5%;}
.account_list li{background:#fff;}
.account_list_div{
    line-height:4rem;
    font-size:1.4rem;
}

.ui-checkbox{
    display:inline-block;
    width:15px;
    height:15px;
    vertical-align:middle;
    position:relative;
    border:1px solid #ccc;
    border-radius:50%;
}
.ui-checkbox input[type="checkbox"]{
    opacity:0;
    width:100%;
    height:100%;
}
.ui-checkbox input[type="checkbox"]:checked+.icon-checked{
    position:absolute;
    left:0;
    top:0;
    display:table;
    background:#2BBDD6;
    border-radius:50%;
    color:#fff;
    width:100%;
    height:100%;
    text-align:center;
}
.icon-checked:after{
    content:"\2713";
    display:table-cell;
    vertical-align:middle;
    width:100%;
    line-height:1;
}
.ui-checkbox input[type="checkbox"]+.icon-checked{
    display:none;
}
</style>
<input type="hidden" id="ur_here" value="<?php echo $this->_var['ur_here']; ?>" />
<!--|登录界面start|-->
<?php if ($this->_var['action'] == 'login'): ?>
<div class="container">

    
    <div class="avatar-wrapper">
      <img src="http://file.yunjingshang.com/wap/images/member/01.jpg" alt="">  
    </div>
    
    <div class="box-form-lp">
        <form name="formLogin" action="user.php" method="post" id="loginForm" autocomplete="off">
            <p class="box-underline box-ipt">
                <?php if ($_GET['uname']): ?>
                <input class="ipt" value="<?php echo $_GET['uname']; ?>" placeholder="请输入用户名/邮箱/已验证手机" type="text" name="username" id="username" />
                <input name="account_choose" value="<?php echo $_GET['account_choose']; ?>" type="hidden" />
                <?php else: ?>
                <input class="ipt" value="" placeholder="请输入用户名/邮箱/已验证手机" type="text" name="username" id="username" />
                <?php endif; ?>
            </p>
            <p class="box-underline box-pw">
                <input type="password" class="pw" name="password" id="password" value="" placeholder="请输入您的密码" />
            </p>
            <p style="padding: 1rem; color:#f00; font-size: 1.09rem;display:none; " id="err_msg"></p>
            <p class="box-check">
                <label for="autologin">
                    <input type="checkbox" id="autologin" checked="checked" />
                    <span class="checkbox"></span>
                    一个月内自动登录
                </label>
            </p>
            <input type="hidden" name="act" value="act_login" />
            <input type="hidden" name="back_act" value="<?php echo $this->_var['back_act']; ?>" />
            <p><button class="g-btn-large" id="login_sub">登 陆</button></p>
            <p style="margin:1rem 0;"><a href="user.php?act=register" class="g-btn-large g-btn-nobg">手机快速注册</a></p>
            <p class="text-right"><a href="user.php?act=get_password"><span class="icon-help"></span>忘记密码</a></p>
        </form>
    </div>

    <div class="box-otherlogin">
        <div class="box-title">
            <span>第三方快速登陆</span>
        </div>
        <div class="box-icons">
            <a href="http://m.easeeyes.com/api/alipay/alipay_auth_authorize.php?defined_url=<?php echo $this->_var['back_act']; ?>" class="icon icon-alipay"></a>
            <a href="http://m.easeeyes.com/api/qq2.1/oauth/index.php?back_act=<?php echo $this->_var['back_act']; ?>" class="icon icon-qq"></a>
            <a href="http://m.easeeyes.com/api/weibodemo/index.php?defined_url=<?php echo $this->_var['back_act']; ?>" class="icon icon-sina"></a>
        </div>
    </div>
</div>


<script src="http://file.easeeyes.com/wap/js/fastclick.min.js"></script>
<script src="http://file.easeeyes.com/js/response.js"></script>
<script>
    /** fastClick **/
    window.addEventListener('load',function(){
        //UC浏览器要双击才能提交 ==!
        //FastClick.attach(document.body);
    },false);

    // 登陆验证
    $("#login_sub").click(function(e){
        var $this=$(this);
        e.preventDefault();
        var username = $('#username').val(),
                password = $('#password').val(),
                url = "user.php?act=check_login";
        if(username == '' || password == ''){
            $("#err_msg").text('用户名或密码不能为空！').show();
            return false;
        }
        
        $.ajax({
            url:url,
            type:'GET',
            data:{username:username,password:password},
            beforeSend:function(){
                $('#login_sub').text('登陆中...')
            },
            success:function(res){
                if(res=='1'){
                    $('#login_sub').text('登陆成功')
                    //$("#loginForm").submit();
                    $("#err_msg").hide();
                    $('#loginForm').submit();
                }else{
                    $('#login_sub').text('登 陆')
                    $("#err_msg").text('用户名或密码不匹配！').show();
                    //alert(33333);
                    return false;
                }
            }
        })
        /*
        $.get(url,{username:username,password:password},function(data){

            if(data=='1'){
                //$("#loginForm").submit();
                $("#err_msg").hide();
                $('#loginForm').submit();
            }else{
                $("#err_msg").text('用户名或密码不匹配！').show();
                //alert(33333);
                return false;
            }
        });
        */
    });


</script>
<?php endif; ?>



<?php if ($this->_var['action'] == 'register'): ?>
<div class="container">
    
    <div class="box-form-lp">
        <form action="user.php" method="post" name="formUser" onsubmit="return register();">
            <p class="box-underline box-phonenum">
                <input type="tel" class="num" value="" placeholder="请输入正确的手机号码" name="username" id="username_r" />
            </p>
            <p class="box-underline box-yzm">
                <input type="text" class="yzm" name="yz_code" id="yz_code" value="" placeholder="请输入验证码" />
            </p>
            <span class="g-btn-radius" id="get_code">获取验证码</span>
            <p class="box-underline box-pw">
                <input type="password" class="pw" name="password" id="pwd1" value="" placeholder="请输入6~12字符密码" />
            </p>
            <p class="box-underline box-pw">
                <input type="password" class="pw" value="" name="password2" id="pwd2" placeholder="请再次输入登录密码" />
            </p>
            <p style="padding: 1rem; color:#f00; font-size: 1.09rem; " id="err_msg"></p>
            <?php if ($this->_var['from']): ?><input type="hidden" name="from" value="<?php echo $this->_var['from']; ?>"/><?php endif; ?>
            <input type="hidden" name="act" value="act_register" />
            <input type="hidden" name="back_act" value="<?php echo $this->_var['back_act']; ?>" />
            <p style="margin-top:3rem;"><button class="g-btn-large" id="reg_usersub">完成注册</button></p>
            <p style="margin:1rem 0;color:#ccc;position:relative;">点击完成注册表示您已同意<span style="color:#00529b;" id="readMe-xy">易视网用户协议</span></p>
        </form>
    </div>
</div>

<script type="text/tpl" id="mlayer-01">
    <div class="box-tpl-main" id="tpl-bottom">
        <div class="title">用户协议</div>
        <div class="text">
            <h4>一、服务条款的接受及更新</h4>
            <p>欢迎来到易视网。使用易视网及其服务必须遵守以下用户协议。用户协议规定了您在易视网拥有的权利、享受的服务以及应履行的义务。易视网保留在任何时间对该协议的任何一条进行添加、删除、更改的权利。请您留意协议的变动，因为协议更新后，您对易视网的继续使用将表明您对新条款的接受。如果您对协议有任何疑问，请与我们联系。您继续使用本网站会被视同为同意遵守本协议的条款及其修改。</p>

            <h4>二、用户的权利</h4>
            <p>用户账号及密码</p>
            <p>您在易视网注册成功后，将获得一个用户账号及相应的密码。您有权在任何时间和地点利用该账号和密码登录易视网。</p>
            <p>用户隐私制度</p>
            <p>易视网承诺尊重和保护用户的个人隐私。请您仔细阅读隐私条款 ，该规则详细说明了我们是如何获取、使用和保护用户个人隐私的。您在注册时输入的个人资料将在合理的范围内进行披露和使用。</p>
            <p>享受服务</p>
            <p>用户有权根据本用户协议的规定以及易视网上发布的相关条例来发布买家秀，购买商品，评论提问，管理账户，以及使用易视网提供的任何服务。每项服务的具体内容请参照帮助信息。</p>
            <p>用户肖像权</p>
            <p>您对您的买家秀拥有全部的所有权。但是，依据非独家许可协议，通过提交买家秀的方式，您授权易视网有永久的、世界性的使用、复制、发行、展示您的作品的非排他性许可使用权。对于侵犯他人版权或其他知识产权的用户，易视网将终止他的账号。</p>
            <p>如果您认为您的买家秀被复制，已经构成对您权益的侵犯，请和我们联系，并提供下列材料：</p>
            <p>涉嫌侵权您权益的URL或产品编号。</p>
            <p>您的姓名、住址、电话和电子邮件地址。</p>
            <p>声明：您具有善意的确信，您进行投诉所使用的材料不是经权利人、代理人或法律授权。</p>
            <p>声明：您在通知中所提供的信息是精确的，您被授权代表被侵犯的所有人，否则您将接受罚款。</p>
            <p>投诉</p>
            <p>用户如发现其他用户有违法或违反本用户协议的行为，可以向易视网进行反映要求处理。如用户因肖像权益问题与其他用户产生诉讼的，用户有权通过司法部门要求易视网提供相关资料。</p>
            <h4>三、用户的义务</h4>
            <p>用户账号及密码的保管</p>
            <p>用户账号和密码的安全由用户负责；在用户账号下进行的所有活动都应由拥有该账号的用户负法律责任。您同意：</p>
            <p>1)如果发现未经授权使用您的账户或密码的行为，立即通知易视网；</p>
            <p>2)请在每次关闭易视网页之前，确保已从您的账号中退出；</p>
            <p>3)不得盗取他人账户，不得假冒其他个人或实体，或伪造与其他个人或实体的关系。</p>
            <p>如果您未能遵守以上三条条款，易视网不会对由此产生的损失负责。</p>
            <p>2.对传播内容的规定</p>
            <p>1)不得以上传、下载、发布、发送邮件或其他方式在易视网上传播违反国家相关法律法规的任何文字，图片。</p>
            <p>3.网站安全</p>
            <p>1)不得利用含有病毒的软件、程式或技术来干扰、破坏易视网的软件、硬件及电信设备功能；</p>
            <p>2)不得滥用网站服务、系统资源和账户，或者进行任何可能对服务器、网络连接或相关网站的正常运转造成不利影响的行为；</p>
            <p>3)未经授权不得进入、篡改或使用网站中的非公开区域。</p>
            <p>补偿</p>
            <p>由以下事项引起的判决、裁决、损失、负债、成本和费用，您应该对易视网，以及易视网公司的董事、雇员、代理商和许可使用人进行补偿。</p>
            <p>1)您通过易视网提交、发布或是传播的内容；</p>
            <p>2)您对易视网的使用；</p>
            <p>3)您对易视网的链接；</p>
            <p>4)您对该用户协议的违反；</p>
            <p>5)您对第三方权利的侵犯。</p>
            <p>版权及商标权政策</p>
            <p>易视网尊重他人的知识产权，我们对用户也提出这样的要求。对于侵犯他人版权或其他知识产权的用户，易视网将终止他的账号。</p>
            <p>公共声誉</p>
            <p>用户不应在易视网上恶意评价其他用户、诋毁他人名誉。您确认并同意，您不会在公共场合（包括互联网），利用任何从易视网订购的产品，来损害易视网及其董事、雇员、代理商、许可使用人或是合作伙伴的公共声誉。如果您违反了该项协议，易视网有权要求您立即归还产品以及在法律上采取一切手段进行补救。</p>
            <p>用户年龄规定</p>
            <p>本公司的服务仅提供给能够签订具有法律效力的合同的个人。在上述规定的前提下，本公司的服务不向18周岁以下用户提供。</p>
            <h4>四、易视网的权利和义务</h4>
            <p>网站运行及维护</p>
            <p>易视网有义务在现有技术上维护整个网上发布平台的正常运行，并努力提升和改进技术，使用户网上各项活动得以顺利进行。</p>
            <p>客户服务</p>
            <p>用户在注册、购买、定制、发布等各个环节遇到的问题，易视网都有责任及时回复解决。用户在产权问题上遇到纠纷或向易视网投诉，易视网有权调查情况，并协助解决。</p>
            <p>编辑网站内容</p>
            <p>您同意易视网对您提交的内容进行审查，使其符合我们的原则以及用户协议，并且易视网有权利删除任何我们认为不妥的内容。易视网在对您提交的内容进行技术上的使用和操作过程中，可能会为适应网络传输和设备的需要而对您提交的内容进行修改。</p>
            <p>产品定价</p>
            <p>易视网上所有商品的价格及其变更都由易视网公司自由决定，网站上有价格的明确标示。</p>
            <p>易视网产权</p>
            <p>易视网自身或通过易视网发表的内容，包括但不限于文字、数据、图片、图表等，是受到商标、专利、版权或其他专有权利和法律保护的。未经权利所有人的授权，您不得对网站上的信息作商业性利用，包括但不限于在未经易视事先书面批准的情况下，以出版、印刷、复制的方式，或者以上传、下载、发布、发送邮件、出售或者其他方式传播在易视网站上展示的资料。</p>
            <p>易视网使用的任何软件所包含的专利及机密资料，都是受到知识产权法和其他法律保护的。未经授权，不得对易视网的所有内容进行全部或部分的修改。</p>
            <p>您提交给易视网的任何说明、电子邮件、信函、意见、建议或其他书面材料的知识产权，将在提交时自动视为授予易视网行使，成为易视网的财产。易视网有权，并且可以选择以任何方式和目的使用、复制、发布、实施、转让及以其他方式处理这些材料以及相关的知识产权。</p>
            <p>不经易视网公司书面同意，用户不能利用易视网各项服务进行复制、出售、转售或其他商业性行为。</p>
        </div>
        <div class="box-btn">
            <button id="mlayer-close01">确 定</button>
        </div>
    </div>
</script>


<script src="http://file.easeeyes.com/wap/js/fastclick.min.js"></script>
<script src="http://file.easeeyes.com/js/response.js"></script>
<script src="http://file.easeeyes.com/wap/js/mlayer.js"></script>
<script>
// 验证码倒计时
function countDown(box,init,end,step,interval,callback){
  var timer=setTimeout(function(){
    countDown(box,init,end,step,interval,callback);
  },interval);
  if(init<=end){
    clearTimeout(timer);
    callback();
  }else{
    init-=step;
    box.innerHTML=init;
  }
}
$(function(){
    var $get_code_btn=$('#get_code'),
        clicked=false;
    $get_code_btn.click(function(){
        if(clicked) return;
        var mobile = $('#username_r').val();
        var user_name = $("input[name='username']:checked").val();
        var url = "user.php?act=is_registered_new";
        var myreg = /^^[1][34578][0-9]{9}$/;
        if(myreg.test(mobile)){
            $.get(url,{mobile:mobile},function(data){
                if(data=='1'){
                    document.getElementById("m-pop-text").innerHTML="我们将发送短信验证码至<br /><span style='color:#2bbdd6;'>"+mobile+"</span>";
                    $('#captcha').val('');  // 清空输入框
                    $('#code_img').trigger('click');  // 触发图片点击事件
                    myPop.set(document.getElementById('myPop'));
                }else{
                    $("#err_msg").html("该手机号已绑定账号，<a href='user.php' style='color:#2bbdd5;text-decoration:underline;'>请直接登录</a>");
                    return;
                }
            })

        }else{
            $("#err_msg").text("请输入有效的手机号");
        }
    })

    $('#m-pop-submit').click(function(){
        myPop.unset(document.getElementById("myPop"));
        // 需要添加验证码验证
        var that=$get_code_btn.get(0);
        var mobile = $('#username_r').val();
        var url = "user.php?act=msgSendReg";
        var captcha_val = $("#captcha").val();//验证码输入内容获取
        $.get(url,{mobile:mobile,captcha:captcha_val},function(data){
            if(data=='1'){
                $("#err_msg").text("该手机号已绑定账号");
                return;
            }else if(data=='2'){
                clicked=true;
                $("#err_msg").text("");
                that.style.color="#999";
                that.style.borderColor="#ccc";
                that.innerHTML="<i id='countDown'></i>秒后重新发送";
                var box=document.getElementById('countDown');
                countDown(box,120,1,1,1000,function(){
                    clicked=false;
                    that.innerHTML="获取验证码";
                    that.style.color="";
                    that.style.borderColor="";
                })
            }else if(data=='3'){
                $("#err_msg").text("请输入图形中的验证码");
                return;
            }else if(data=='4'){
                $("#err_msg").text("图形中的验证码填写不正确");
                return;
            }else{
                document.write(data);
                return;
            }
        })
    });

});

    // 提交验证
    $('#reg_usersub').click(function(){
        var myreg = /^(((1[0-9][0-9]{1}))+\d{8})$/;
        var percode=$('#username_r').val();
        var pwd=$('#pwd1').val();
        var repwd=$('#pwd2').val();
        if(percode=='' || myreg.test(percode)==false){
            $("#err_msg").text("请输入正确的手机号");
            return false;
        }
        var yzcode=$('#yz_code').val();
        if(yzcode==''){
            $("#err_msg").text("请输入验证码");
            return false;
        }else{
            var result=checkYzcode(percode,yzcode);
            if(result==0){
                $("#err_msg").text('验证码错误');
                return false;
            }
        }
        if(pwd==''){
            $("#err_msg").text("请设置密码");
            return false;
        }else{
            if(pwd.length < 6){
                $("#err_msg").text("密码长度不得少于6位");
                return false;
            }
        }
        if(repwd==''){
            $("#err_msg").text("请确认密码");
            return false;
        }else{
            if(pwd!=repwd){
                $("#err_msg").text("两次输入的密码不一致");
                return false;
            }
        }
        if($("#remerber").attr('checked')==false){
            $("#err_msg").text("请勾选易视网眼镜协议");
            return false;
        }
        $("#err_msg").text("");
        return true;
    })
    // 检测验证码是否正确
    function checkYzcode(mobile,code){
        var yz_url="user.php?act=check_code_reg";
        var bool='';
        $.ajax({
            type:"GET",
            dataType:'json',
            async:false,
            url:yz_url,
            data:{mobile:mobile,code:code},
            success:function(data){
                //alert(data);
                if(data!='1'){
                    bool=0;
                }else{
                    bool=1;
                }
            }
        })
        return bool;
    }

    /** fastClick **/
    var mlayer01; 
    window.addEventListener('load',function(){
        FastClick.attach(document.body);

        var btn=document.getElementById('readMe-xy'),
        	temp=document.getElementById('mlayer-01').innerHTML;
        btn.addEventListener('click',function(){
        	layer.open({
        		content:temp,
        		shadow:1,
        		position:['center','end'],
        		effect:3,
        		closeBtns:['mlayer-close01']
        	})
        },false);
        /*
        window.pop01=Pop.create({
            id:"tpl-bottom",
            fullpage:document.getElementById("tpl-fullpage")
        })
        document.getElementById("tpl-fullpage").addEventListener("click",function(e){
            if(e.target==this){
                pop01.close();
            }
        },false);
        */
    },false);
</script>

<?php endif; ?>



<?php if ($this->_var['action'] == 'account_list'): ?>

<div class="account_list_div">请选择以下对应账号：</div>
<ul class="account_list">
<?php $_from = $this->_var['account_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('key', 'item');if (count($_from)):
    foreach ($_from AS $this->_var['key'] => $this->_var['item']):
?>

<li>
    <a href="user.php?act=act_login&uname=<?php echo $this->_var['item']; ?>&account_choose=1&pwd=<?php echo $this->_var['pwd']; ?>">
        <h3 class="menu_h3" id="menu_h3_0">
            <?php echo $this->_var['item']; ?>
        </h3>
    </a>
</li>

<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?> 
</ul>
<?php endif; ?>



<?php if ($this->_var['action'] == 'get_password'): ?>
<div class="container step_1" >
    
    <div class="box-form-lp">
        <p class="box-underline box-ipt">
            <input type="text" id="username_r" class="ipt" value="" placeholder="请输入已验证手机" />
        </p>
        <p style="padding: 1rem; color:#f00; font-size: 1.09rem; " id="err_msg"></p>
        <p style="margin-top:3rem;"><button class="g-btn-large" onclick="get_users();">下一步</button></p>
    </div>
</div>

<script type="text/tpl" id="mlayer-02">
    <div class="box-tpl-main" id="tpl-bottom2">
        <div class="title">请选择以下对应账号</div>
        <div class="text text2" id="user_info">
            {{user_info}}
        </div>
        <div class="box-btn">
            <button id="next_1">确 定</button>
        </div>
    </div>
</script>

<div class="container step_2">
    
    <div class="box-form-lp">
        <p class="box-underline title-zhmm2">请确认验证方式</p>
        <p class="box-underline box-phonenum phoneNum-zhmm2">
            <span class="blue" id="user_name_xing">136****1234</span>
            <span class="pull-right g-btn-close-circle" onclick="window.location.href='user.php?act=get_password';">&times;</span>
        </p>
        <p style="margin-top:3rem;"><button class="g-btn-large" id="next_2">下一步</button></p>
    </div>
</div>
<div class="container step_3">
    <div style="padding:2rem 2rem 0;font-size:1.3rem;" id="code_send">验证码发送至：11111111</div>
    
    <div class="box-form-lp">
        <p class="box-underline box-yzm"><input type="text" value="" placeholder="请输入验证码" id="yz_code" /></p>
        <span class="g-btn-radius" id="get_code">获取验证码</span>
        <p class="box-underline box-pw">
            <input type="password" name="password" id="password1" placeholder="请输入新密码" />
        </p>
        <p class="box-underline box-pw">
            <input type="password" name="password2" id="password2" placeholder="请确认新密码" />
        </p>
        <p style="padding: 1rem; color:#f00; font-size: 1.09rem; " id="err_msg_3"></p>
        <p style="margin-top:3rem;"><button class="g-btn-large" id="CPWD_From">提 交</button></p>
    </div>
</div>

<script type="text/tpl" id="mlayer-03">
    <div class="tpl-main" id="successTip">
        <div class="title">提示消息<span class="close" onclick="window.location.href = 'user.php';">&times;</span></div>
        <div class="text">
            设置新密码成功...
        </div>
    </div>
</script>

<script src="http://file.easeeyes.com/js/response.js"></script>
<script src="http://file.easeeyes.com/wap/js/fastclick.min.js"></script>
<script src="http://file.easeeyes.com/wap/js/mlayer.js"></script>
<script>
    /** fastClick **/
    var mlayer02;
    window.addEventListener('load',function(){
        FastClick.attach(document.body);
        /*
        window.poper01=Pop.create({
            id:"poper-01",
            fullpage:document.getElementById("bg-poper-01")
        })
        window.pop02=Pop.create({
            id:"tpl-bottom2",
            fullpage:document.getElementById("tpl-fullpage2")
        })
        document.getElementById("tpl-fullpage2").addEventListener("click",function(e){
            if(e.target==this){
                pop02.close();
            }
        },false);
        */
    },false);

var binded=false;  //弹窗内部添加事件变量
function get_users(){
    var myreg = /^(((1[0-9][0-9]{1}))+\d{8})$/;
    var percode=$('#username_r').val();
    var url="user.php?act=get_users";
    if(percode=='' || myreg.test(percode)==false){
        $("#err_msg").text("请输入正确的手机号");
        return false;
    }
    $.ajax({
        type:"GET",
        url:url,
        data:{username:percode},
        beforeSend:function(){
            $('#loadingBox').show();
        },
        success:function(data){
            $('#loadingBox').hide();
            if(data == ""){
                $("#err_msg").text("未找到相应的账户信息！");
                return false;
            }else{
            		var str=document.getElementById('mlayer-02').innerHTML;
                mlayer02=layer.open({
                	content:str.replace(/{{user_info}}/,data),
                	shadow:1,
                	effect:3,
                	position:['center','end']
                })
                if(!binded){
                	binded=true;
                	$('.mlayer-container').delegate('li','click',function(){
                		$(this).addClass('selected').siblings('li').removeClass('selected');
                	})
                }
                $("#err_msg").text("");
            }
        }
    })
}

// 验证码倒计时
function countDown(box,init,end,step,interval,callback){
  var timer=setTimeout(function(){
    countDown(box,init,end,step,interval,callback);
  },interval);
  if(init<=end){
    clearTimeout(timer);
    callback();
  }else{
    init-=step;
    box.innerHTML=init;
  }
}
$(function(){
    var $get_code_btn=$('#get_code'),
        clicked=false;
    $get_code_btn.click(function(){
        if(clicked) return;
        var mobile = $('#username_r').val();
        var myreg = /^^[1][34578][0-9]{9}$/;
        if(myreg.test(mobile)){
            document.getElementById("m-pop-text").innerHTML="我们将发送短信验证码至<br /><span style='color:#2bbdd6;'>"+mobile+"</span>";
            myPop.set(document.getElementById('myPop'));    
        }else{
            $("#err_msg_3").text("请输入有效的手机号");
        } 
    });

    $("#m-pop-submit").click(function(){
        // 需要添加验证码验证
        var that=$get_code_btn.get(0);   
        var mobile = $('#username_r').val();
        var url = "user.php?act=msgSend";
        var user_name = $("input[name='username']:checked").val();
        var captcha_val = $("#captcha").val();//验证码输入内容获取
        myPop.unset(document.getElementById("myPop"));
        $.get(url,{mobile:mobile,user_name:user_name,captcha:captcha_val},function(data){
            if(data==1){
               $("#err_msg_3").text(""); 
            }else if(data == 2){
                clicked=true;
                $("#err_msg_3").text("");
                that.style.color="#999";
                that.style.borderColor="#ccc";
                that.innerHTML="<i id='countDown'></i>秒后重新发送";
                var box=document.getElementById('countDown');
                countDown(box,120,1,1,1000,function(){
                    clicked=false;
                    that.innerHTML="获取验证码";
                    that.style.color="";
                    that.style.borderColor="";
                })
            }else if(data=='3'){
                $("#err_msg_3").text("请输入图形中的验证码");
                return;
            }else if(data=='4'){
                $("#err_msg_3").text("图形中的验证码填写不正确");
                return;
            }else{
                document.write(data);
                return;
            }
        })
    });
})
  


// 检测验证码是否正确
function checkYzcode(mobile,code){
    var yz_url="user.php?act=check_code";
    var bool='';
    $.ajax({
        type:"GET",
        dataType:'json',
        async:false,
        url:yz_url,
        data:{mobile:mobile,code:code},
        success:function(data){
            //alert(data);
            if(data!='1'){
                bool=0;
            }else{
                bool=1;
            }
        }
    })
    return bool;
}

$('body').delegate('#next_1','click',function(){
		layer.close(mlayer02);
    //var username = $("input[name='username']:checked").val();
    // 记录获取验证码的手机号，并加*显示
    var mobile = $('#username_r').val();
    var mobile_xing = mobile.replace(/(.{3}).*(.{4})/,"$1******$2");
    $("#user_name_xing").text(mobile_xing);
    $(".step_1").hide();
    $(".step_2").show();
})
/*
$("#next_1").click(function(){
    pop02.close();
    //var username = $("input[name='username']:checked").val();
    // 记录获取验证码的手机号，并加*显示
    var mobile = $('#username_r').val();
    var mobile_xing = mobile.replace(/(.{3}).*(.{4})/,"$1******$2");
    $("#user_name_xing").text(mobile_xing);
    $(".step_1").hide();
    $(".step_2").show();
});
*/
$("#next_2").click(function(){
    // 记录获取验证码的手机号，并加*显示
    var mobile = $('#username_r').val();
    var mobile_xing = mobile.replace(/(.{3}).*(.{4})/,"$1******$2");
    $("#code_send").text("验证码发送至："+mobile_xing);
    $(".step_2").hide();
    $(".step_3").show();
});

$("#CPWD_From").click(function(){
    var percode=$('#username_r').val();
    var yzcode=$('#yz_code').val();
    if(yzcode==''){
        $("#err_msg_3").text("请输入验证码");
        return false;
    }else{
        var result=checkYzcode(percode,yzcode);
        if(result==0){
            $("#err_msg_3").text('验证码错误');
            return false;
        }
    }
    var pwd=$('#password1').val();
    var repwd=$('#password2').val();
    if(pwd==''){
        $("#err_msg_3").text("请设置密码");
        return false;
    }else{
        if(pwd.length<6){
            $("#err_msg_3").text("密码长度不得少于6位");
            return false;
        }
    }
    if(repwd==''){
        $("#err_msg_3").text("请确认密码");
        return false;
    }else{
        if(pwd!=repwd){
            $("#err_msg_3").text("确认密码与密码不一致");
            return false;
        }
    }
    $("#err_msg_3").text("");
    var url = "user.php?act=get_password";
    var user_name = $("input[name='username']:checked").val();
    $.post(url,{password:pwd,user_name:user_name},function(d){
        if(d == 1){
        	layer.open({
        		content:document.getElementById('mlayer-03').innerHTML
        	})
            //poper01.open();return false;
        }else{
            $("#err_msg_3").text("修改失败，请刷新页面重试");
        }
    })
});
</script>
<?php endif; ?>


<?php if ($this->_var['action'] == 'forget_list'): ?>
<div class="account_list_div">请选择以下对应账号：</div>
<ul class="account_list">
    <?php $_from = $this->_var['account_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('key', 'item');if (count($_from)):
    foreach ($_from AS $this->_var['key'] => $this->_var['item']):
?>
    <li>
        <a href="user.php?act=msgSend&user_name=<?php echo $this->_var['item']['user_name']; ?>">
            <h3 class="menu_h3" id="menu_h3_0">
                <?php echo $this->_var['item']['user_name']; ?>
            </h3>
        </a>
    </li>
    <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
</ul>
<?php endif; ?>



    <?php if ($this->_var['action'] == 'qpassword_name'): ?>
<div class="usBox">
  <div class="usBox_2 clearfix">
    <form action="user.php" method="post">
        <br />
        <table width="70%" border="0" align="center">
          <tr>
            <td colspan="2" align="center"><strong><?php echo $this->_var['lang']['get_question_username']; ?></strong></td>
          </tr>
          <tr>
            <td width="29%" align="right"><?php echo $this->_var['lang']['username']; ?></td>
            <td width="61%"><input name="user_name" type="text" size="30" class="inputBg" /></td>
          </tr>
          <tr>
            <td></td>
            <td><input type="hidden" name="act" value="get_passwd_question" />
              <input type="submit" name="submit" value="<?php echo $this->_var['lang']['submit']; ?>" class="bnt_blue" style="border:none;" />
              <input name="button" type="button" onclick="history.back()" value="<?php echo $this->_var['lang']['back_page_up']; ?>" style="border:none;" class="bnt_blue_1" />
	    </td>
          </tr>
        </table>
        <br />
      </form>
  </div>
</div>
<?php endif; ?>


    <?php if ($this->_var['action'] == 'get_passwd_question'): ?>
<div class="usBox">
  <div class="usBox_2 clearfix">
    <form action="user.php" method="post">
        <br />
        <table width="70%" border="0" align="center">
          <tr>
            <td colspan="2" align="center"><strong><?php echo $this->_var['lang']['input_answer']; ?></strong></td>
          </tr>
          <tr>
            <td width="29%" align="right"><?php echo $this->_var['lang']['passwd_question']; ?>：</td>
            <td width="61%"><?php echo $this->_var['passwd_question']; ?></td>
          </tr>
          <tr>
            <td align="right"><?php echo $this->_var['lang']['passwd_answer']; ?>：</td>
            <td><input name="passwd_answer" type="text" size="20" class="inputBg" /></td>
          </tr>
          <?php if ($this->_var['enabled_captcha']): ?>
          <tr>
            <td align="right"><?php echo $this->_var['lang']['comment_captcha']; ?></td>
            <td><input type="text" size="8" name="captcha" class="inputBg" />
            <img src="captcha.php?is_login=1&<?php echo $this->_var['rand']; ?>" alt="captcha" style="vertical-align: middle;cursor: pointer;" onClick="this.src='captcha.php?is_login=1&'+Math.random()" /> </td>
          </tr>
          <?php endif; ?>
          <tr>
            <td></td>
            <td><input type="hidden" name="act" value="check_answer" />
              <input type="submit" name="submit" value="<?php echo $this->_var['lang']['submit']; ?>" class="bnt_blue" style="border:none;" />
              <input name="button" type="button" onclick="history.back()" value="<?php echo $this->_var['lang']['back_page_up']; ?>" style="border:none;" class="bnt_blue_1" />
	    </td>
          </tr>
        </table>
        <br />
      </form>
  </div>
</div>
<?php endif; ?>

<?php if ($this->_var['action'] == 'reset_password'): ?>
<script type="text/javascript">
<?php $_from = $this->_var['lang']['password_js']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('key', 'item');if (count($_from)):
    foreach ($_from AS $this->_var['key'] => $this->_var['item']):
?>
	var <?php echo $this->_var['key']; ?> = "<?php echo $this->_var['item']; ?>";
<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
</script>

<div style="width:988px; height:282px; border:1px #dcdcdc solid; text-align:center;">
<form action="user.php" method="post" name="getPassword2" onSubmit="return submitPwd()">
	<div class="space10" style="height:63px;"></div>
    <div style="width:435px; height:32px;">
    	<div style="width:90px; height:32; line-height:32px; text-align:right; float:left; color:#666; font-size:14px;">新密码：</div>
        <div style="width:345px; float:right;"><input name="new_password" type="password" size="25" class="inputcon" /></div>
    </div>
    <div class="space10" style="height:16px;"></div>
    <div style="width:435px; height:32px;">
    	<div style="width:90px; height:32; line-height:32px; text-align:right; float:left; color:#666; font-size:14px;">确认新密码：</div>
        <div style="width:345px; float:right;"><input name="confirm_password" type="password" size="25"  class="inputcon"/></div>
    </div>
    <div style="margin-top:20px;">
        <input type="hidden" name="act" value="act_edit_password" />
        <input type="hidden" name="uid" value="<?php echo $this->_var['uid']; ?>" />
        <input type="hidden" name="code" value="<?php echo $this->_var['code']; ?>" />    
    	<input type="submit" name="submit" value="" style="width:115px; height:34px; background-color:#ffffff; border:0px #ffffff solid; background-image:url(themes/default/images/login/submit.gif); cursor:pointer;"/>
    </div> 
</form>
</div>
<?php endif; ?>



<div id="loadingBox" style="display:none;position:fixed;left:0;top:0;width:100%;height:100%;background:rgba(0,0,0,.5);">
    <span style="position:fixed;left:50%;top:50%;width:4rem;height:4rem;background:#fff;border-radius:.5rem;text-align:center;line-height:4rem;font-size:0;margin-left:-2rem;margin-top:-2rem;"><img src="http://file.easeeyes.com/wap/images/loading.gif" alt="" style="height:2rem;"></span>
</div>


<div class="m-pop-box" id="myPop" style="display:none;">
    <div class="m-pop m-pop-confirm" style="height:14.5rem;">
        <div class="m-pop-text" id="m-pop-text">
            我们将发送短信验证码至<br />12321212121
        </div>
        <?php if ($this->_var['enabled_captcha'] && ( $this->_var['ur_here'] == '注册' || $this->_var['ur_here'] == '找回密码' )): ?>
        <div id="captcha_info" style="padding:0 1.5rem;height: 4rem;font-size: 1.2rem;">
        	<input type="text" style="width:10rem;height:2rem;line-height:2rem;padding:.25rem;border:1px solid #dfdfdf;" name="captcha" id="captcha" class="inputBg" /> 
        	<img src="api/securimage/securimage_show.php?<?php echo $this->_var['rand']; ?>" alt="captcha" style="vertical-align:middle;cursor:pointer;height:2.5rem;" onClick="this.src='api/securimage/securimage_show.php?sid='+Math.random()" id="code_img" />
        </div>
        <?php endif; ?>
        <div class="m-pop-btns">
            <a href="javascript:myPop.cancal(document.getElementById('myPop'));" id="m-pop-cancal">取消</a>
            <a href="javascript:;" id="m-pop-submit">确定</a>
        </div>
    </div>
</div>
<script>
// 弹窗
var myPop={
    
    cancal:function(obj){
        this.unset(obj);
    },
    set:function(obj){
        obj.style.display="block";
    },
    unset:function(obj){
        obj.style.display="none";
    }
};  
</script>

<?php echo $this->fetch('library/footer_login.lbi'); ?>
</body>
<script>
    // 将click改为focus解决用键盘切换无法实现的问题   zhang：20150821
/*
$(".password1").focus(function(){
    $(".password1").hide();
    $("#password1").show().select();
});
$("#password1").blur(function(){
   if($("#password1").val()==''||$("#password1").val()=='密码'){
       $(".password1").show();
       $("#password1").hide();
   }
});

$(".confirm_password").focus(function(){
    $(".confirm_password").hide();
    $("#confirm_password").show().select();
});
$("#confirm_password").blur(function(){
   if($("#confirm_password").val()==''||$("#confirm_password").val()=='再次输入密码'){
       $(".confirm_password").show();
       $("#confirm_password").hide();
   }
});
*/
// input focus状态下底部fixed失效
try{
    $('input[type=text],input[type=password]').focus(function(){
        $('footer').hide()
    }).blur(function(){
        $('footer').show()
    })
}catch(e){

}


</script>
</html>