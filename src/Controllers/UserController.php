<?php


namespace SmallRuralDog\Admin\Controllers;


use SmallRuralDog\Admin\Components\Avatar;
use SmallRuralDog\Admin\Components\Checkbox;
use SmallRuralDog\Admin\Components\CheckboxGroup;
use SmallRuralDog\Admin\Components\Input;
use SmallRuralDog\Admin\Components\InputNumber;
use SmallRuralDog\Admin\Components\Link;
use SmallRuralDog\Admin\Components\Radio;
use SmallRuralDog\Admin\Components\RadioGroup;
use SmallRuralDog\Admin\Components\Tag;
use SmallRuralDog\Admin\Form;
use SmallRuralDog\Admin\Grid;

class UserController extends AdminController
{

    protected function showPageHeader()
    {
        return false;
    }

    protected function title()
    {
        return trans('admin.administrator');
    }

    protected function grid()
    {


        $userModel = config('admin.database.users_model');
        $grid = new Grid(new $userModel());
        $grid->pageBackground()->defaultSort('id', 'asc')->with(['roles:id,name', 'roles.permissions', 'roles.menus'])->selection()
            ->stripe(true)->emptyText("暂无用户")->perPage(10);


        $column = $grid->column('id', "ID")->width(80);
        $nameColumn = $grid->column('name', '用户昵称');
        $column->labelClassName('ClassName ClassName');
        $grid->columns([
            $column,
            $grid->column('avatar', '头像')->width(80)->align('center')->displayComponent(Avatar::make()),
            $grid->column('username', trans('admin.username'))->displayComponent(Link::make()),
            $nameColumn,
            $grid->column('roles.name', trans('admin.roles'))->displayComponent(Tag::make()->effect('dark')),
            $grid->column('roles.menus.title', trans('admin.roles'))->displayComponent(Tag::make()->size("mini")->style(['margin-right' => '5px'])),
            $grid->column('created_at', trans('admin.created_at')),
            $grid->column('updated_at', trans('admin.updated_at'))
        ]);


        return $grid;
    }

    protected function form()
    {


        $userModel = config('admin.database.users_model');
        $form = new Form(new $userModel());


        $form->size('small');
        $form->items([
            $form->item('username', '用户名')->displayComponent(Input::make()->prefixIcon('el-icon-eleme')),
            $form->item('name', '名称')->displayComponent(Input::make()->showWordLimit()->maxlength(20)),
            $form->item('avatar', '头像')->displayComponent(RadioGroup::make(null, [Radio::make(1, "启动"), Radio::make(0, "禁用")])),
            $form->item('password', '密码')->displayComponent(Input::make()->password()->showPassword()),
            $form->item('c_password', '确认密码')->displayComponent(Input::make()->password()->showPassword()),
            $form->item('roles', '角色')->help("最多选择一个")->displayComponent(CheckboxGroup::make(null, [Checkbox::make(1, "开启审核"), Checkbox::make(123, "热门")])->max(1)),
            $form->item('ps', '权限')->displayComponent(InputNumber::make()),
        ]);

        return $form;
    }
}
