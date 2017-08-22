<style>
    .editormd {
        margin: 0;
        border: none;
    }

    .save_btn {
        background: #f60;
        color: #ffffff;
    }

    #save_msg {
        display: inline;
        position: fixed;
        top: 0;
        right: 0;
        background: #f60;
        padding: 2px 10px;
        color: #ffffff;
        z-index: 99999999;
    }
</style>
<div id="content">
    <textarea class="form-control" name="content">{{$content}}</textarea>
</div>
<div id="save_msg">保存中..</div>
<script>
    var testEditor, w = $("#doc-page").width(), h = $("#doc-page").height(), edit_id = "{{$id}}";
    testEditor = editormd("content", {
        width: '100%',
        height: '100%',
        syncScrolling: "single",
        path: "/packages/editor/lib/",
        toolbarIcons: function () {
            return [
                "undo", "redo", "|",
                "bold", "del", "italic", "quote", "ucwords", "uppercase", "lowercase", "|",
                "h1", "h2", "h3", "h4", "h5", "h6", "|",
                "list-ul", "list-ol", "hr", "|",
                "link", "reference-link", "image", "code", "preformatted-text", "code-block", "table", "datetime", "emoji", "html-entities", "pagebreak", "|",
                "goto-line", "watch", "preview", "fullscreen", "clear", "search", "|",
                "open_collect", "collect_ky", "save"
            ]
        },
        toolbarIconTexts: {
            save: "保存",
            collect_ky: "采集看云",
            open_collect: "查看来源"
        },
        toolbarHandlers: {
            save: function () {
                page_save();
            },
            collect_ky: function () {
                collect("{{$id}}")
            },
            open_collect: function () {
                window.open("{{$collect_url}}");
            }
        }
    });

    function keyDown(e) {
        e.preventDefault();
        var currKey = 0, e = e || event || window.event;
        currKey = e.keyCode || e.which || e.charCode;
        if (currKey == 83 && (e.ctrlKey || e.metaKey)) {
            page_save();
            return false;
        }
    }

    //document.onkeydown = keyDown;
    $("#save_msg").hide();

    page_save = function () {
        var content = testEditor.getMarkdown();
        $("#save_msg").show();
        $.post("{{route('book_save_content')}}", {
            id: "{{$id}}",
            _token: "{{csrf_token()}}",
            content: content
        }, function (res) {
            $("#save_msg").hide();
            if (res.state) {
                layer.msg("保存成功");
            } else {
                layer.msg("保存失败");
            }
        });


    }

</script>