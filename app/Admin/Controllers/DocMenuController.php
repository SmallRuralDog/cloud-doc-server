<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Doc;
use App\Models\DocMenu;
use Encore\Admin\Controllers\ModelForm;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;

class DocMenuController extends Controller
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
            $content->description('目录');

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
            $content->description('目录');

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
            $content->description('目录');

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
        $doc_id = request("doc_id", 0);
        return Admin::grid(DocMenu::class, function (Grid $grid) use ($doc_id) {
            if ($doc_id > 0) {
                $grid->model()->where("doc_id", "=", $doc_id);
            }
            $grid->id('ID')->sortable();
            $grid->column("title", "目录名称");
            $grid->doc()->title("所属文档");
            $grid->column("order", "排序");
            $grid->created_at("创建时间");
            $grid->disableCreation();

            $grid->tools(function (Grid\Tools $tools) use ($doc_id) {
                if ($doc_id > 0) {
                    $tools->append("<a href='" . admin_url("doc-menu/create?doc_id=" . $doc_id) . "' class='btn btn-sm btn-success'>添加目录</a>");
                }
            });
            $grid->actions(function (Grid\Displayers\Actions $actions) use ($doc_id) {
                $actions->disableEdit();
                $actions->disableDelete();
                $actions->append("<a href='".admin_url("doc-page?menu_id=".$actions->getKey()."&doc_id=" . $doc_id)."' class='btn btn-xs'>页面列表</a>");
                $actions->append("<a href='" . admin_url("doc-menu/" . $actions->getKey() . "/edit?doc_id=" . $doc_id) . "' class='btn btn-xs'>编辑</a>");
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
        $doc_id = request("doc_id", 0);
        return Admin::form(DocMenu::class, function (Form $form) use ($doc_id) {

            $form->text("title", "目录名称")->setWidth(2);
            $form->select("doc_id", "所属文档")->options(Doc::query()->where("id", $doc_id)->pluck("title", "id"))->setWidth(2);
            $form->number("order", "排序")->default(1);
            $form->saved(function ($form) use ($doc_id) {
                // 跳转页面
                return redirect(admin_url("doc-menu?doc_id=" . $form->doc_id));
            });
        });
    }
}
