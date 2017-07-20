<?php

namespace App\Admin\Extensions;

use Encore\Admin\Form\Field;

/**
 * Created by PhpStorm.
 * User: ZhangWei
 * Date: 2017/7/19
 * Time: 18:02
 */
class Editor extends Field
{
    protected $view = 'admin.form.editor';

    protected static $css = [
        '/packages/editor/css/editormd.css',
    ];

    protected static $js = [
        '/packages/editor/editormd.min.js',
    ];

    public function render()
    {
        $this->script = <<<EOT
    var testEditor;
    $(function () {

        testEditor = editormd("{$this->id}", {
            width: "100%",
            height: 640,
            syncScrolling: "single",
            path: "/packages/editor/lib/"
        });
         get_wx_app_data = function(){
            var data = testEditor.getPreviewedHTML();
            $("textarea[name={$this->id}_html]").val(data);
            console.log(data)
        }
    });
EOT;
        return parent::render();

    }

}