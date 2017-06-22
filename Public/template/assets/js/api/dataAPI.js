define(["jquery"], function ($) {
    var dataAPI = {
        baseRequestUrl:"..",
        login: function (data,cb) {
            $.post(this.baseRequestUrl+"/login/dologin",data,function (result) {
                cb(result);
            })
        },
        resetPwd: function (data,cb) {
            $.post(this.baseRequestUrl+"/login/doRestPassword",data,function (result) {
                cb(result);
            })
        },

        getUserinfo:function (data,cb) {
            $.post(this.baseRequestUrl+"/dataSearch/getUserInfo",data,function (result) {
                cb(result);
            })
        },

        //持仓

        getUserOrderinfo:function (data,cb) {

            $.post(this.baseRequestUrl + "/dataSearch/getUserOrderinfo", data, function (result) {
                cb(result);
            })

        },

        //充值

        getRechargeInfo:function (data,cb) {

            $.post(this.baseRequestUrl + "/dataSearch/getRechargeInfo", data, function (result) {
                cb(result);
            })

        },

        //交易失败

        getTransactionInfo:function (data,cb) {

            $.post(this.baseRequestUrl + "/dataSearch/getTransactionInfo", data, function (result) {
                cb(result);
            })
            
        },

        getSuccessInfo:function (data,cb) {

            $.post(this.baseRequestUrl + "/dataSearch/getSuccessInfo", data, function (result) {
                cb(result);
            })
        },




        };
    return dataAPI;
});