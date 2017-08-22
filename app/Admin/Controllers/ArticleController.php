<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Tag;
use Encore\Admin\Controllers\ModelForm;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;

class ArticleController extends Controller
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

            $content->header('文章');
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
            $content->description('文章');

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
            $content->description('文章');

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
        return Admin::grid(Article::class, function (Grid $grid) {

            $grid->model()->orderBy("source_time","desc")->orderBy("id","desc");

            $grid->id('ID')->sortable();
            $grid->column('title', '文章标题');
            $grid->column('desc', '文章描述')->limit(80);

            $grid->tags('文章标签')->display(function ($tags) {

                $tags = array_map(function ($tags) {
                    return "<span class='label label-success'>{$tags['name']}</span>";
                }, $tags);

                return join('&nbsp;', $tags);
            });

            $grid->column('view_count','浏览数');

            $grid->created_at('创建时间');


            $grid->filter(function (Grid\Filter $filter){
                $filter->disableIdFilter();
                $filter->like("title","名称");
                $filter->like("source_hash","source_hash");
                $filter->is("state","状态")->select([0=>"禁用",1=>"正常"]);
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
        return Admin::form(Article::class, function (Form $form) {

            $form->text('title');
            $form->textarea('desc');
            $form->multipleSelect('tags')->options(Tag::all()->pluck('name', 'id'));
            $form->image('cover');
            $form->editor('content');
        });
    }
}
