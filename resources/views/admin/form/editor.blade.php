<div class="form-group {!! !$errors->has($errorKey) ?: 'has-error' !!}">

    <label for="{{$id}}" class="col-sm-{{$width['label']}} control-label">{{$label}}</label>

    <div class="col-sm-{{$width['field']}}">

        @include('admin::form.error')
        <div id="{{$id}}">

            <textarea class="form-control" name="{{$name}}"
                      placeholder="{{ $placeholder }}" {!! $attributes !!} >{{ old($column, $value) }}</textarea>
        </div>
        @include('admin::form.help-block')

        <div>
            <a class="btn btn-success" onclick="get_wx_app_data()">渲染微信小程序数据</a>
        </div>
    </div>
</div>