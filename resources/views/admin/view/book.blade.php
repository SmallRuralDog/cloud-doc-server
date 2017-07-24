<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{{$doc->title}}</title>
    <link rel="stylesheet" type="text/css" href="{{asset('admin_assets/jquery-easyui/themes/bootstrap/easyui.css')}}">
    <link rel="stylesheet" type="text/css" href="{{asset('admin_assets/jquery-easyui/themes/icon.css')}}">
    <link rel="stylesheet" type="text/css" href="{{asset('/packages/editor/css/editormd.css')}}">
    <script type="text/javascript" src="{{asset('admin_assets/jquery-easyui/jquery.min.js')}}"></script>
    <script type="text/javascript" src="{{asset('admin_assets/jquery-easyui/jquery.easyui.min.js')}}"></script>
    <script type="text/javascript" src="{{asset('admin_assets/layer/layer.js')}}"></script>

    <script type="text/javascript" src="{{asset('/packages/editor/editormd.min.js')}}"></script>
</head>
<body>
<div class="easyui-layout" data-options="fit:true,border:false">
    <!--div data-options="region:'north'" style="height:30px"></div-->
    <div data-options="region:'west',split:true,collapsible:false,border:true,tools:'#menu_tools'" title="目录"
         style="width:300px;">
        <ul id="doc-menu"></ul>
    </div>
    <div data-options="region:'center',title:false,split:true,border:true">
        <div id="doc-page"></div>
    </div>
</div>
<div id="menu_tools">
    <a href="javascript:void(0)" title="刷新" class="icon-reload" onclick=" DocMenu.tree('reload')"></a>
    <a href="javascript:void(0)" title="添加章节" class="icon-add" onclick="add()"></a>
    <a href="javascript:void(0)" title="采集" class="icon-print" onclick="collect(0)"></a>

</div>
<div id="mm" class="easyui-menu" style="width:120px;">
    <div onclick="append()" data-options="iconCls:'icon-add'">添加章节</div>
    <div onclick="edit()" data-options="iconCls:'icon-edit'">编辑</div>
    <div onclick="remove()" data-options="iconCls:'icon-remove'">删除</div>
</div>

<div id="add_win">
    <form id="ff" method="post" class="">
        <div>
            <label for="name">章节名称:</label>
            <input class="easyui-validatebox" type="text" name="title" data-options="required:true"/>
        </div>
    </form>
</div>
<script>
    var DocMenu = $("#doc-menu");

    DocMenu.tree({
        url: '{{route('book_get_tree',['doc_id'=>$doc_id])}}',
        method: 'get',
        lines: true,
        dnd: true,
        formatter: function (node) {//数据显示
            return node.title;
        },
        onClick: function (node) {//点击节点
            //alert(node.id);
            $("#doc-page").panel({
                href: '{{route('book_edit_content')}}?id=' + node.id,
                loadingMessage: "加载中...",
                fit: true,
                border: false,
                cache: false,
                onLoad: function () {
                }
            });
        },
        onContextMenu: function (e, node) {//右键节点
            e.preventDefault();
            DocMenu.tree('select', node.target);
            $('#mm').menu('show', {
                left: e.pageX,
                top: e.pageY
            });
        },
        onDrop: function (target, source, point) {
            var t_node = DocMenu.tree('getNode', target);
            $.get("{{route('book_set_order')}}", {
                t_id: t_node.id,
                s_id: source.id,
                point: point
            }, function (res) {
                console.log(res)
            });

        },
        /*onDblClick: function (node) {//双击编辑
            $(this).tree('beginEdit', node.target);
        },*/
        onBeforeEdit: function (node) {//编辑前
            node.text = node.title
        },
        onAfterEdit: function (node) {//编辑后
            if (node.text === node.title) {
                return false;
            }
            edit_title(node.id, node.text, function () {
                layer.msg("修改成功");
                DocMenu.tree('update', {
                    target: node.target,
                    title: node.text
                });
            })
        }
    });
    add = function () {
        layer.prompt({title: '请输入章节名称', formType: 2}, function (pass, index) {
            layer.close(index);
            add_page(0, pass)
        });
    };
    append = function () {
        var nodes = DocMenu.tree('getSelected');
        layer.prompt({title: '请输入章节名称', formType: 2}, function (pass, index) {
            layer.close(index);
            add_page(nodes.id, pass)
        });
    };
    remove = function () {
        var nodes = DocMenu.tree('getSelected');
        var msg = "确定要删除吗？";
        if (nodes.children.length > 0) {
            var msg = "当前会将子节点也删除，确定要删除吗？";
        }
        layer.confirm(msg, {
            btn: ['确定', '取消'] //按钮
        }, function () {
            layer.closeAll();
            $.get("{{route('book_del_page')}}", {id: nodes.id}, function (res) {
                if (res.state) {
                    layer.msg("删除成功");

                    DocMenu.tree("remove", nodes.target);
                }
            })
        });
    };
    edit = function () {
        var node = $('#doc-menu').tree('getSelected');
        DocMenu.tree('beginEdit', node.target);
    };
    add_page = function (parent_id, title) {
        $.post("{{route('book_add_page')}}", {
            doc_id: "{{$doc_id}}",
            _token: "{{csrf_token()}}",
            parent_id: parent_id,
            title: title
        }, function (res) {
            if (res.page.id > 0) {
                if (parent_id <= 0) {
                    var node = DocMenu.tree('find', res.s_page.id);
                    DocMenu.tree('insert', {
                        after: node.target,
                        data: {
                            id: res.page.id,
                            title: res.page.title
                        }
                    });
                } else {
                    var node = DocMenu.tree('find', parent_id);
                    DocMenu.tree('append', {
                        parent: node.target,
                        data: {
                            id: res.page.id,
                            title: res.page.title
                        }
                    });
                }
            }
        })
    };
    edit_title = function (id, title, node) {
        $.post("{{route('book_edit_title')}}", {
            id: id,
            _token: "{{csrf_token()}}",
            title: title
        }, function (res) {
            if (res.state) {
                node()
            }
        });
    };
    collect = function (id) {
        var nodes = DocMenu.tree('getSelected');
        var parent_id = (nodes === null) ? 0 : nodes.id;
        layer.prompt({title: '请输入看云链接', formType: 2}, function (pass, index) {
            layer.close(index);
            $.post("{{route("book_collect_ky")}}", {
                id: id,
                parent_id: parent_id,
                doc_id: "{{$doc->id}}",
                _token: "{{csrf_token()}}",
                url: pass
            }, function (res) {
                if (res.id > 0) {
                    if (id <= 0) {
                        DocMenu.tree('reload');
                    } else {
                        var node = DocMenu.tree('find', id);
                        DocMenu.tree('update', {
                            target: node.target,
                            title: res.title
                        });
                        $("#doc-page").panel('open').panel('refresh');
                    }
                }
            })
        });
    }
</script>
</body>
</html>