@if(request('menu_id') > 0 && request('doc_id') > 0)
    <div class="box">
        <div class="box-body">
            <div class="input-group input-group-sm col-xs-4">
                <input type="url" id="ky_dec_id" class="form-control" placeholder="请输入看云文档URL">
                <span class="input-group-btn"><button type="button" onclick="cj_ky(this)"
                                                      class="btn btn-success btn-flat">采集看云</button></span>
            </div>
        </div>
    </div>
    <script>
        cj_ky = function (dom) {
            var id = $("#ky_dec_id").val();
            if (id > 0) {

                $(dom).html("正在采集");
                $(dom).attr('disabled', "true");

                $.get("{{route('collect_ky')}}", {
                    id: id,
                    menu_id: '{{request('menu_id')}}',
                    doc_id: '{{request('doc_id')}}'
                }, function (res) {
                    if (res.id > 0) {
                        $.pjax.reload('#pjax-container');
                        toastr.success("采集成功");
                    } else {
                        toastr.error("采集失败");
                        $(dom).html("采集看云");
                        $(dom).attr('disabled', "false");
                    }
                });
            } else {
                toastr.error("请输入ID")
            }
        }
    </script>
@endif
