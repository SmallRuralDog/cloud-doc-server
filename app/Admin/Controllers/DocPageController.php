<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Doc;
use App\Models\DocMenu;
use App\Models\DocPage;
use Encore\Admin\Controllers\ModelForm;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;

class DocPageController extends Controller
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
            $content->description('页面');

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
            $content->description('页面');

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
            $content->description('页面');

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
        $menu_id = request("menu_id", 0);
        $doc_id = request("doc_id", 0);
        return Admin::grid(DocPage::class, function (Grid $grid) use ($doc_id, $menu_id) {
            if ($menu_id > 0) {
                $grid->model()->where("menu_id", "=", $menu_id);
            }
            $grid->id('ID')->sortable();
            $grid->column("title", "文档标题");
            $grid->column("menu_title", "目录标题");
            $grid->doc()->title("所属文档");
            $grid->doc_menu()->title("所属目录");
            $grid->column("order", "排序");
            $grid->column("state", "状态")->switch();
            $grid->created_at('创建时间');
            $grid->updated_at('最后更新时间');

            $grid->disableCreation();

            $grid->tools(function (Grid\Tools $tools) use ($doc_id, $menu_id) {
                if ($menu_id > 0) {
                    $tools->append("<a href='" . admin_url("doc-page/create?menu_id=" . $menu_id . "&doc_id=" . $doc_id) . "' class='btn btn-sm btn-success'>添加文档页面</a>");
                    $tools->append("<a href='" . admin_url("doc-menu?doc_id=" . $doc_id) . "' class='btn btn-sm btn-success'>返回目录</a>");
                }
            });

            $grid->actions(function (Grid\Displayers\Actions $actions) use ($menu_id, $doc_id) {
                $actions->disableEdit();
                $actions->disableDelete();
                $actions->append("<a href='" . admin_url("doc-page/" . $actions->getKey() . "/edit?menu_id=" . $menu_id . "&doc_id=" . $doc_id) . "' class='btn btn-xs'>编辑</a>");
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

        $menu_id = request("menu_id", 0);
        $doc_id = request("doc_id", 0);
        if ($menu_id > 0) {
            $doc_menu = DocMenu::query()->find($menu_id);
            $doc_id = $doc_menu->doc_id;
        }
        return Admin::form(DocPage::class, function (Form $form) use ($menu_id, $doc_id) {

            $form->text("title", "文档名称")->setWidth(2);
            $form->text("menu_title", "目录名称")->setWidth(2);
            $form->select("doc_id", "所属文档")->options(Doc::query()->where("id", $doc_id)->pluck("title", "id"))->setWidth(2);
            $form->select("menu_id", "所属目录")->options(DocMenu::query()->where("id", $menu_id)->pluck("title", "id"))->setWidth(2);
            $form->number("order", "排序")->default(1);
            $form->editor('content');

            $form->saving(function ($form){
                $form->content = str_replace("\\n","\r\n",$form->content);
                $form->content = str_replace("{tip}","",$form->content);
                $form->content = str_replace("{note}","",$form->content);
                $form->content = preg_replace("/<a[^>]*><\/a>/is", "", $form->content);
            });

            $form->saved(function ($form) {
                $form->content = str_replace("\\n","\r\n",$form->content);
                $form->content = str_replace("{tip}","",$form->content);
                $form->content = str_replace("{note}","",$form->content);
                $form->content = preg_replace("/<a[^>]*><\/a>/is", "", $form->content);
                // 跳转页面
                return redirect(admin_url("doc-page?menu_id=" . $form->menu_id."&doc_id=".$form->doc_id));
            });
        });
    }
}
