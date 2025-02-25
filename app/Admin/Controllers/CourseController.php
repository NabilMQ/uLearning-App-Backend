<?php

namespace App\Admin\Controllers;

use App\Models\User;
use App\Models\Course;
use App\Models\CourseType;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Layout\Content;
use Encore\Admin\Tree;

class CourseController extends AdminController
{
    protected $title ='Course';

    // public function index(Content $content)
    // {
    //     $tree = new Tree(new Course);
    //     return $content
    //         ->header('Course Types')
    //         ->body($tree);
    // }

    protected function grid()
    {
        $grid = new Grid(new Course());
        // the first argument is the database field
        $grid->column('id', __('Id'));
        $grid->column('user_token', __('Teacher'))
            ->display(function ($token){
                return User::where('token', '=', $token)
                    ->value('name');
            });
        $grid->column('name', __('Name'));
        $grid->column('thumbnail', __('Thumbnail'))->image('', 300, 200);
        $grid->column('description', __('Description'));
        $grid->column('type_id', __('Type id'));
        $grid->column('price', __('Price'));
        $grid->column('lesson_length', __('Lesson length'));
        $grid->column('video_length', __('Video length'));
        $grid->column('downloadable_resources', __('Downloadable Resources'));
        $grid->column('created_at', __('Created at'));

        return $grid;
    }

    protected function detail($id)
    {
        $show = new Show(Course::findOrFail($id));
        // the first argument is the database field
        $show->field('id', __('Id'));
        // dd(User::all()->first()->value('token'));
        // $teacher = User::where('token', '=', Course::findOrFail($id)->first()->value('user_token'))->value('name');
        // dd(Course::findOrFail($id)->first()->value('user_token'));
        $show->field('user_token', __('Teacher'))->as(function ($teacher) {
            $name = User::where('token', '=', $teacher)->value('name');
            return $name;
        });
        $show->field('name', __('Name'));
        $show->field('thumbnail', __('Thumbnail'))->image('', 300, 200);
        $show->field('description', __('Description'));
        $show->field('type_id', __('Type id'));
        $show->field('price', __('Price'));
        $show->field('lesson_length', __('Lesson length'));
        $show->field('video_length', __('Video length'));
        $show->field('created_at', __('Created at'));

        return $show;
    }

    protected function form()
    {
        $form = new Form(new Course());

        $form->text('name', __('Name'));
        
        $result = CourseType::pluck('title', 'id');
        $form->select('type_id', __('Category'))->options($result);
        $form->image('thumbnail', __('Thumbnail'))->uniqueName();
        $form->file('video', __('Video'))->uniqueName();
        $form->textarea('description', __('Description'));
        $form->decimal('price', __('Price'));
        $form->number('lesson_length', __('Lesson Length'));
        $form->number('video_length', __('Video Length'));
        
        $result = User::pluck('name', 'token');
        $form->select('user_token', __('Teacher'))->options($result);
        $form->display('created_at', __('Created At'));
        $form->display('updated_at', __('Updated At'));


        // $form->text('title', __('Title'));
        // $form->number('order', __('Order'));


        // $form->select('parent_id', __('Parent Category'))->options((new Course())::selectOptions());
        // $form->text('title', __('Title'));
        // $form->textarea('description', __('Description'));
        // $form->number('order', __('Order'));

        return $form;
    }
}