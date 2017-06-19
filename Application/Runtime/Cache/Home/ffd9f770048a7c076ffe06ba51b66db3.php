<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html;charset=utf-8">
    <meta name="description" content="">
    <meta name="keywords" content="">
    <title></title>
    <link rel="stylesheet" href="/htdocs/Public/template/assets/css/login.min.css">
  </head>
  <body>
    <div class="wrap">
      <div class="main">
        <h5>后台管理系统</h5>
        <form>
          <div class="form-control">
            <label>账号</label>
            <input id="username" name="username" type="text" placeholder="账户名/手机号">
          </div>
          <div class="form-control">
            <label>密码</label>
            <input id="password" name="password" type="password" placeholder="登录密码">
          </div>
          <!--.form-control-->
          <!--    label 验证码-->
          <!--    input#smsCode(name="checkCode" type="text" placeholder="手机验证码")-->
          <p class="error-tips"></p>
        </form><a href="javascript:;" class="submit">登录</a>
      </div>
    </div>
    <script src="/htdocs/Public/template/assets/js/vendor/require.js" data-main="/htdocs/Public/template/assets/js/common"></script>
    <script>
      require(['common'], function () {
          require(['page/login']);
      });
    </script>
  </body>
</html>