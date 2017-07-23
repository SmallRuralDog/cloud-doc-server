<style>
    .editormd{
        margin: 0;
        border: none;
    }
</style>
<div id="content">
    <textarea class="form-control" name="content">{{$content}}</textarea>
</div>
<script>
    var testEditor,w = $("#doc-page").width(),h = $("#doc-page").height();

    console.log(w)

    testEditor = editormd("content", {
        width: '100%',
        height: '100%',
        syncScrolling: "single",
        path: "/packages/editor/lib/"
    });
</script>