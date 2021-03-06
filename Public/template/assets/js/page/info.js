/**
 * Created by Administrator on 2017/6/7.
 */
define([
    "jquery",
    "utils",
    "config",
    "api/infoAPI",
    "layer",
    "pagination",
    "remodal"
], function ($, utils, config, infoAPI) {
    var addInfoModal = $('[data-remodal-id=addInfoModal]').remodal();

    var body = $("body");
    var page = {
        init: function () {
            this.render();
            this.bindEvents();
        },

        render: function () {
            this.initModal();
            this.fnGetList({}, true);
        },

        bindEvents: function () {
            this.onAdd();
            this.onDel();
            this.onSearch();
            this.onStarInfo();
        },

        initModal: function () {
            $(".J_showAdd").on("click", function () {
                addInfoModal.open();
            });
            body.on("click", ".J_showEdit", function () {
                var $this = $(this);
                var id = $this.parents('tr').attr('data-id');
                var oTd = $this.parents('tr').find('td');
                //checkTd + news_time + subject_name + remarks + pic_url + controlTd
                var subject_name= oTd.eq(2).text();
                var remarks = oTd.eq(3).text();
                var pic_url = $this.parents("tr").attr("data-src");
                var local_pic = $this.parents("tr").attr("data-local");
                var link = $this.parents("tr").attr("data-link");

                var oForm = $(".addInfoModal .modalForm");
                oForm.find("input[name=id]").val(id);
                oForm.find("input[name=subject_name]").val(subject_name);
                oForm.find("input[name=showpic_url]").val(pic_url);
                oForm.find("input[name=local_pic]").val(local_pic);
                oForm.find("input[name=link_url]").val(link);
                oForm.find("input[name=remarks]").val(remarks);
                addInfoModal.open();
            });

            $(document).on('closed', '.remodal', function (e) {
                $(this).find(".modalForm")[0].reset();
            });
        },
        onStarInfo: function () {
            var _this = $(".addInfoModal input[name='starname']");
            _this.on("blur", function () {
                infoAPI.getStarInfo({starname: _this.val()}, function (result) {
                    $(".addInfoModal input[name='starname']").attr("value", result.star_name);
                    $(".addInfoModal input[name='starcode']").attr("value", result.star_code);
                })
            })
        },
        onAdd: function () {
            var _this = this;
            var confirmBtn = $(".addInfoModal .remodal-confirm");
            var oForm = $(".addInfoModal form");
            confirmBtn.on("click", function (e) {
                e.preventDefault();
                var $this = $(this);
                var id = oForm.find('[name=id]').val();

                var data = {
                    id : id,
                    subject_name: oForm.find('[name=subject_name]').val(),
                    showpic_url: oForm.find('[name=showpic_url]').val(),
                    local_pic: oForm.find('[name=local_pic]').val(),
                    remarks: oForm.find('[name=remarks]').val(),
                    link_url: oForm.find('[name=link_url]').val()
                };

                if (id > 0) {
                    infoAPI.editInfo(data, function (result) {
                        if (result.code == 0) {
                            layer.msg('修改成功');
                            addInfoModal    .close();
                            $this.removeClass("disabled");
                            _this.fnGetList({}, true);
                        } else if(result.code == -2){
                            layer.msg(result.message);
                        }else {
                            layer.msg("操作失败");
                        }
                    })
                    return false;
                }

                infoAPI.addInfo(data, function (result) {
                    if (result.code == 0) {
                        layer.msg('添加成功');
                        addInfoModal    .close();
                        $this.removeClass("disabled");
                        _this.fnGetList({}, true);
                    } else if(result.code == -2){
                        layer.msg(result.message);
                    }else {
                        layer.msg("操作失败");
                    }
                })
            })
        },

        onDel: function () {
            var _this = this;
            $(".J_onDel").on("click", function () {
                var idArr = utils.getCheckedArr();

                if (idArr.length > 0) {
                    layer.confirm('确定删除选中的列表项吗？', {icon: 3}, function (index) {
                        var data = {ids : idArr};
                        infoAPI.delInfo(data, function (result) {
                            _this.fnGetList({}, true);
                        });
                        layer.close(index)
                    });
                } else {
                    layer.alert("请先选择要删除的列表项", {icon: 0});
                }
            })
        },

        onSearch: function () {
            var _this = this;
            $(".J_search").on("click", function () {
                var oForm = $(".search-bar");
                var data = {
                    page: 1,
                    superMemberid: oForm.find("select[name=level]").val(),
                    name: oForm.find("input[name=orgName]").val() || ""
                };
                _this.fnGetList(data, true);
            });
        },

        fnGetList: function (data, initPage) {
            var _this = this;
            var table = $(".data-container table");

            infoAPI.search(data, function (result) {

                if (!result.list || result.list.length == "0") {
                    table.find("tbody").empty().html("<tr><td colspan='10'>暂无记录</td></tr>");
                    $(".pagination").hide();
                    return false;
                }
                var oTr,
                    checkTd = '<td><input type="checkbox"></td>',
                    controlTd = "<td><a class='J_showEdit text-blue' href='javascript:;'>修改</a></td>";
                $.each(result.list, function (i, v) {
                    var news_time = '<td>' + v.news_time + '</td>';
                    var subject_name = '<td>' + v.subject_name + '</td>';
                    var remarks = '<td>' + v.remarks + '</td>';
                    var src = publicUrl + '/uploads/info/'+ v.local_pic;
                    var url = v.showpic_url;
                    var pic_url = '<td><img src="'+src +'" class="icon-star-img"></td>';
                    //var opt = '<td>'+controlTd+'</td>';
                    oTr += '<tr class="fadeIn animated" data-id="' + v.id + '" data-src="'+ url +'" data-link="' +v.link_url+ '" data-local="'+ v.local_pic +'">' + checkTd + news_time + subject_name + remarks + pic_url + controlTd + '</tr>';
                });
                table.find("tbody").empty().html(oTr);
                if (initPage) {
                    var pageCount = result.totalPages;
                    if (pageCount > 0) {
                        console.log("页数：" + pageCount);
                        $(".pagination").show().html("").createPage({
                            pageCount: pageCount,
                            current: 1,
                            backFn: function (p) {
                                var newData = data;
                                newData.page = p;
                                _this.fnGetList(data)
                            }
                        })
                    }
                }
            });
        }
    };
    page.init();
});
