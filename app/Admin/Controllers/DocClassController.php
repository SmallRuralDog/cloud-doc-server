<?php

namespace App\Admin\Controllers;

use App\Models\DocClass;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;

class DocClassController extends Controller
{
    use ModelForm;

    /**
     * Index interface.
     *
     * @return Content
     */
    public function index()
    {
        return Admin::content(function (Content $content) {

            $content->header('文档');
            $content->description('分类');

            $content->body($this->grid());
        });
    }

    /**
     * Edit interface.
     *
     * @param $id
     * @return Content
     */
    public function edit($id)
    {
        return Admin::content(function (Content $content) use ($id) {

            $content->header('编辑');
            $content->description('');

            $content->body($this->form()->edit($id));
        });
    }

    /**
     * Create interface.
     *
     * @return Content
     */
    public function create()
    {
        return Admin::content(function (Content $content) {

            $content->header('添加');
            $content->description('');

            $content->body($this->form());
        });
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Admin::grid(DocClass::class, function (Grid $grid) {

            $grid->id('ID')->sortable();
            $grid->column("title","分类名称");
            $grid->column("desc","分类描述");

            $grid->created_at('添加时间');
            $grid->updated_at("更新时间");
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Admin::form(DocClass::class, function (Form $form) {

            $form->text("title","分类名称");
            $form->select('parent_id','上级分类')->options(DocClass::selectOptions());
            $form->text("desc","分类描述");
            $form->number("order","排序")->default(1);
            $form->image("icon","图标");

        });
    }
}
