<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html;charset=utf-8">
    <meta name="description" content="">
    <meta name="keywords" content="">
    <title><?php echo (!empty($title)) ? $title : '星享后台' ?></title>
    <link rel="stylesheet" href="/xh/Public/template/assets/css/index.min.css">
    <!--<link rel="stylesheet" href="/xh/Public/template/assets/css/bootstrap/css/bootstrap.css">-->

    <script src="/xh/Public/template/assets/js/vendor/jquery.min.js" data-main="/xh/Public/template/assets/js/common"></script>
</head>
<body>
<div class="wrap">
    <div class="header">
        <div class="clearfix layout">
            <h1><a href="/xh/index.php/Home/AdminBacker/index">星享管理系统</a></h1>
            <div>
                <span class="spantext" >管理员：<?php echo ($user['uname']); ?>,<a style="color:#FF0000;" href="/xh/index.php/Home/login/doLoginout" onclick="return confirm('确定退出本系统?')" >系统退出</a></span>
            </div>
        </div>
    </div>
    <div class="main">
        <style>
    .submenu{display: none;}
    .submenu li{color: #CCCCCC;padding-left: 20px;width: 70%;}
    .select{border-bottom: 2px dashed red;}

    .pli a.main {
        background-color: #55abed;
    }

    .submenu li:hover {
        color: #55abed;
    }

</style>
<div class="sidebar">
    <ul class="nav-list">
        <li class="pli">
            <a href="/xh/index.php/Home/AdminBacker/index" <?php if(CONTROLLER_NAME == 'AdminBacker'){echo 'class="active"';} ?>><i class="icon-home"></i><strong> 后台管理</strong></a>
        </li>
        <li class="pli">
            <a href="javascript:;" <?php if(CONTROLLER_NAME == 'Star'){echo 'class="active"';} ?>><i class="icon-star"></i><strong> 明星管理</strong></a>
            <ul class="submenu">
                <li><a href="/xh/index.php/Home/Star/carousel">轮播列表</a></li>
                <li><a href="#">资讯列表</a></li>
                <li><a href="#">明星列表</a></li>
                <li><a href="#">约见管理</a></li>
                <li><a href="#">约见类型管理</a></li>
                <li><a href="#">明星时间管理</a></li>
            </ul>
        </li>
        <li class="pli">
            <a href="javascript:;" <?php if(CONTROLLER_NAME == 'Customer'){echo 'class="active"';} ?>><i class="icon-tags"></i><strong> 消费者管理</strong></a>
            <ul class="submenu">
                <li><a href="#">消费者列表</a></li>
                <li><a href="#">分享统计列表</a></li>
            </ul>
        </li>
        <li class="pli">
            <a href="javascript:;"
            <?php if($action == 'dataSearch'){echo 'class="main"';} ?>
            <i class="icon-align-right"></i><strong> 数据查询</strong></a>
            <ul class="submenu">
                <li><a
                    <?php if($actionUrl == 'fundList'){echo 'class="active"';} ?>
                    href="/xh/index.php/Home/DataSearch/fundList">资金查询</a></li>
                <li><a
                    <?php if($actionUrl == 'position'){echo 'class="active"';} ?>
                        href="/xh/index.php/Home/DataSearch/position">持仓汇总查询</a></li>
                <li><a href="#">出入金查询</a></li>
                <li><a href="#">交易额明细查询</a></li>
                <li><a href="#">成交明细查询</a></li>
            </ul>
        </li>
        <?php if($user['identity_id']<4){ ?>
            <li class="pli">
                <a href="javascript:;"
                    <?php if($action == 'accountManage'){echo 'class="main"';} ?>  >
                    <i class="icon-user"></i>
                    <strong>
                        系统账户管理
                    </strong>
                </a>
                <ul class="submenu">

                    <li><a href="/xh/index.php/Home/accountmanage/userManage"
                        <?php if($actionUrl == 'userManage'){echo 'class="active"';} ?>
                        >账户权限</a>
                    </li>

                    <!--<li><a href="javascript:;"-->
                        <!--<?php if($actionUrl == '#'){echo 'class="active"';} ?>-->
                        <!--&gt;账户角色</a></li>-->
                    <!--
                    <li><a href="#">创建系统账户</a></li>
                    -->

                    <?php if($user['identity_id']<2){ ?>
                        <li><a href="/xh/index.php/Home/accountmanage/orgManage"
                            <?php if($actionUrl == 'orgManage'){echo 'class="active"';} ?>
                        >区域总经销列表</a>
                        </li>
                    <?php } ?>

                    <?php if($user['identity_id']<3){ ?>
                        <li><a href="/xh/index.php/Home/accountmanage/brokerManage"
                            <?php if($actionUrl == 'brokerManage'){echo 'class="active"';} ?>
                        >经销商列表</a>
                        </li>
                    <?php } ?>

                    <li><a href="/xh/index.php/Home/accountmanage/brokerSubManage"
                        <?php if($actionUrl == 'brokerSubManage'){echo 'class="active"';} ?>
                        >零售商列表</a>
                    </li>

                </ul>
            </li>
        <?php } ?>

    </ul>
</div>
<script>
    $(function () {
        $(".pli").each(function () {

            //是否已有选中的菜单
            var isActive   = $(this).children('a').hasClass('active');
            var isMain   = $(this).children('a').hasClass('main');
            var box = $(this).children("ul");

            //添加点击事件
            $(this).children('a').on("click", function () {

              //  alert(box);


                //只留一个选中样式的菜单
                $(".pli a").removeClass('active');
                $(this).children('a').addClass('active');

                //是否已是选中状态 | 取消选中
                var isOpen   = (box).hasClass('open');
                if (box != undefined && isOpen == false) {
                    box.show();
                    box.addClass("open");
                } else {
                    box.hide();
                    box.removeClass("open");
                }
            });

            //默认选中并展开子菜单
            if (isMain) {
                box.show();
                box.addClass("open");
            }

        });
    });
</script>



<!--<li><i class="fa fa-globe"></i>系统账户管理<i class="fa fa-chevron-down"></i></li>-->
<!--<ul>-->
<!--</ul>-->
        <div class="content">
<style>
    .thumbnail {
        line-height: 160px;
        text-align: center;
        border:1px solid red;
        display: inline-block;
        float: left;
        width: 40%;
        padding: 20px;
        margin: 20px;
        border: 1px solid #dedede;
        border-radius:3px;
    }
</style>
<ul class="thumbnails">
    <li class="span4">
        <a href="/xh/index.php/Home/Star/carousel" class="thumbnail">
            明星管理
        </a>
    </li>
    <li class="span4">
        <a href="/xh/index.php/Home/Customer/customerList" class="thumbnail">
            消费者管理
        </a>
    </li>
    <li class="span4">
        <a href="/xh/index.php/Home/DataSearch/fund" class="thumbnail">
            数据查询
        </a>
    </li>
    <li class="span4">
        <a href="/xh/index.php/Home/SystemAccount/accontRights" class="thumbnail">
            系统账户管理
        </a>
    </li>

</ul>
</html>