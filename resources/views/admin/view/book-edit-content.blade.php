<style>
    .editormd {
        margin: 0;
        border: none;
    }
</style>
<div id="content">
    <textarea class="form-control" name="content">{{$content}}</textarea>
</div>
<script>
    var testEditor, w = $("#doc-page").width(), h = $("#doc-page").height();

    console.log(w)

    testEditor = editormd("content", {
        width: '100%',
        height: '100%',
        syncScrolling: "single",
        path: "/packages/editor/lib/",
        toolbarIconTexts: {
            testIcon2: "测试按钮"
        }
    });

    function keyDown(e) {
        e.preventDefault();
        var currKey = 0, e = e || event || window.event;
        currKey = e.keyCode || e.which || e.charCode;
        if (currKey == 83 && (e.ctrlKey || e.metaKey)) {
            layer.msg("保存成功");
            return false;
        }
    }
    document.onkeydown = keyDown;
</script>