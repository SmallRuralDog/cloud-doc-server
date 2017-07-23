<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Basic Panel - jQuery EasyUI Demo</title>
    <link rel="stylesheet" type="text/css" href="{{asset('admin_assets/jquery-easyui/themes/bootstrap/easyui.css')}}">
    <link rel="stylesheet" type="text/css" href="{{asset('admin_assets/jquery-easyui/themes/icon.css')}}">
    <script type="text/javascript" src="{{asset('admin_assets/jquery-easyui/jquery.min.js')}}"></script>
    <script type="text/javascript" src="{{asset('admin_assets/jquery-easyui/jquery.easyui.min.js')}}"></script>
    <script type="text/javascript" src="{{asset('admin_assets/layer/layer.js')}}"></script>
</head>
<body>
<div class="easyui-layout" data-options="fit:true,border:false">
    <div data-options="region:'north'" style="height:30px"></div>
    <div data-options="region:'west',split:true,collapsible:false,border:true,tools:[{
					iconCls:'icon-add',
					handler:function(){add()}
				}]" title="目录" style="width:300px;">
        <ul id="doc-menu"></ul>
    </div>
    <div data-options="region:'center',title:false,split:true,border:false">

    </div>
</div>
<div id="mm" class="easyui-menu" style="width:120px;">
    <div onclick="append()" data-options="iconCls:'icon-add'">添加子章节</div>
    <div onclick="edit()" data-options="iconCls:'icon-edit'">修改</div>
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

        }
    });
    add = function () {
        layer.prompt({title: '请输入章节名称', formType: 2}, function (pass, index) {
            layer.close(index);
            add_page(0, pass)
        });
    };
    append = function () {
        console.log(123)
        var nodes = $('#doc-menu').tree('getSelected');
        $('#add_win').window({
            width: 300,
            height: 200,
            title: '添加' + nodes.title + '子章节',
            modal: true,
            resizable: false,
            collapsible: false,
            minimizable: false,
            maximizable: false
        });
    };
    edit = function () {
        var node = $('#doc-menu').tree('getSelected');
        //DocMenu.tree('beginEdit',node.target);
    };


    add_page = function (parent_id, title) {
        $.post("{{route('book_add_page')}}", {
            doc_id: "{{$doc_id}}",
            _token: "{{csrf_token()}}",
            parent_id: parent_id,
            title: title
        }, function (res) {
            if (res.page.id > 0) {
                var node = DocMenu.tree('find', res.s_page.id);
                DocMenu.tree('append', {//append  insert
                    after: node.target,
                    data: {
                        id: res.page.id,
                        title: res.page.title
                    }
                });
            }
        })
    }
</script>
</body>
</html>