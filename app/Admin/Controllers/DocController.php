<?php

namespace App\Admin\Controllers;

use App\Models\Doc;

use App\Models\DocClass;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;

class DocController extends Controller
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
            $content->description('管理');

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
            $content->description('文档');

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
            $content->description('文档');

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
        return Admin::grid(Doc::class, function (Grid $grid) {
            $grid->id('ID')->sortable();
            $grid->column('cover','封面')->image(null,50,75);
            $grid->doc_class()->title("所属分类");
            $grid->column("title","文档名称");
            //$grid->column("desc","文档描述");
            $grid->column("source","文档来源");
            $grid->column("is_end","是否完结")->switch();
            $grid->column("is_hot","是否推荐")->switch();
            $grid->column("state","状态")->switch();
            $grid->created_at('创建时间');


            $grid->actions(function (Grid\Displayers\Actions $actions){
                $actions->disableEdit();
                $actions->disableDelete();

                $actions->append("<a href='".admin_url("doc-menu?doc_id=".$actions->getKey())."' class='btn btn-xs'>文档目录</a>");
                $actions->append("<a href='".admin_url("doc/".$actions->getKey()."/edit")."' class='btn btn-xs'>编辑</a>");
            });
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Admin::form(Doc::class, function (Form $form) {

            $form->text("title","文档名称");
            $form->text("desc","文档描述");
            $form->select("doc_class_id","所属分类")->options(DocClass::all()->pluck("title","id"));
            $form->image("cover","文档封面")->help("封面规格 500x800");
            $form->hidden("user_id")->default("0");
            $form->text("source","文档来源");
            $form->switch("is_end","是否完结")->default(0);
            $form->switch("is_hot","是否推荐")->default(0);
            $form->switch("state","状态")->default(1);
            $form->number("order","排序")->default(1);
        });
    }
}
