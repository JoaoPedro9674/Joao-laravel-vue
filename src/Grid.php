<?php


namespace SmallRuralDog\Admin;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Eloquent\Relations;
use Illuminate\Support\Str;
use SmallRuralDog\Admin\Components\Component;
use SmallRuralDog\Admin\Grid\Actions;
use SmallRuralDog\Admin\Grid\Column;
use SmallRuralDog\Admin\Grid\Concerns\HasDefaultSort;
use SmallRuralDog\Admin\Grid\Concerns\HasFilter;
use SmallRuralDog\Admin\Grid\Concerns\HasGridAttributes;
use SmallRuralDog\Admin\Grid\Concerns\HasPageAttributes;
use SmallRuralDog\Admin\Grid\Concerns\HasQuickSearch;
use SmallRuralDog\Admin\Grid\Filter;
use SmallRuralDog\Admin\Grid\Model;
use SmallRuralDog\Admin\Grid\Table\Attributes;
use SmallRuralDog\Admin\Grid\Toolbars;


class Grid extends Component implements \JsonSerializable
{
    use HasGridAttributes, HasPageAttributes, HasDefaultSort, HasQuickSearch, HasFilter;

    protected $componentName = 'Grid';
    /**
     * @var Model
     */
    protected $model;
    /**
     * @var Column[]
     */
    protected $columns = [];
    protected $rows;
    protected $columnAttributes = [];


    protected $keyName = 'id';
    protected $tree = false;
    protected $dataUrl;
    protected $isGetData = false;
    private $actions;
    private $toolbars;


    public function __construct(Eloquent $model)
    {
        $this->attributes = new Attributes();
        $this->dataUrl = request()->getUri();
        $this->model = new Model($model, $this);
        $this->keyName = $model->getKeyName();


        $this->defaultSort($model->getKeyName(), "asc");

        $this->isGetData = request('get_data') == "true";


        //$this->actions = new Actions();

        $this->toolbars = new Toolbars();
        $this->filter = new Filter($this->model);

    }

    /**
     * 获取自定义数据模型
     * @return Model|Builder
     */
    public function model()
    {
        return $this->model;
    }


    /**
     *设置with
     * @param array $withs
     * @return $this
     * @deprecated
     */
    public function with(array $withs)
    {
        $this->model()->with($withs);
        return $this;
    }

    /**
     * 设置树形表格
     * @param bool $tree
     * @return $this
     */
    public function tree($tree = true)
    {
        $this->tree = $tree;
        $this->componentName = "Tree";
        return $this;
    }


    /**
     * Grid添加字段
     * @param string $name 对应列内容的字段名
     * @param string $label 显示的标题
     * @param string $columnKey 排序查询等数据操作字段名称
     * @return Column
     */
    public function column($name, $label = '', $columnKey = null)
    {
        if (Str::contains($name, '.')) {
            $this->addRelationColumn($name, $label);
        }

        return $this->addColumn($name, $label, $columnKey);
    }

    /**
     * @param string $name
     * @param string $label
     * @param $columnKey
     * @return Column
     */
    protected function addColumn($name = '', $label = '', $columnKey = null)
    {
        $column = new Column($name, $label, $columnKey);
        $column->setGrid($this);
        $this->columns[] = $column;
        return $column;
    }

    /**
     * Add a relation column to grid.
     *
     * @param string $name
     * @param string $label
     *
     * @return $this|bool|Column
     */
    protected function addRelationColumn($name, $label = '')
    {
        list($relation, $column) = explode('.', $name);

        $model = $this->model()->eloquent();


        if (!method_exists($model, $relation) || !$model->{$relation}() instanceof Relations\Relation) {
        } else {
            $this->model()->with($relation);
        }


    }

    /**
     * @param Column[] $columns
     * @deprecated
     */
    public function columns($columns)
    {
        /*if ($this->selection) {
            $column = $this->addColumn($this->model->getModel()->getKey());
            $column->type("selection");
            $column->align("center");
            $column->width(50);
            $columns = collect($columns)->prepend($column)->all();
        }*/
        $this->columnAttributes = collect($columns)->map(function (Column $column) {
            return $column->getAttributes();
        })->toArray();
    }

    public function getColumns()
    {
        return $this->columns;
    }

    protected function applyQuery()
    {
        //快捷搜索
        $this->applyQuickSearch();

        $this->applyFilter(false);

    }

    /**
     * 自定义toolbars
     * @param $closure
     * @return $this
     */
    public function toolbars($closure)
    {
        call_user_func($closure, $this->toolbars);
        return $this;
    }

    /**
     * 自定义行操作
     * @param $closure
     * @return $this
     */
    public function actions($closure)
    {
        $this->actions = $closure;
        return $this;
    }

    /**
     * 获取行操作
     * @param $row
     * @param $key
     * @return mixed
     */
    public function getActions($row, $key)
    {
        $actions = new Actions();
        $actions->row($row)->key($key);
        if ($this->actions) call_user_func($this->actions, $actions);
        return $actions->builderActions();
    }


    /**
     * 隐藏行操作
     * @return $this
     */
    public function hideActions()
    {
        $this->actions->hideActions();
        return $this;
    }

    /**
     * data
     * @return array
     */
    protected function data()
    {

        $this->applyQuery();

        $data = $this->model->buildData();
        return [
            'code' => 200,
            'data' => $data
        ];
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        if (count($this->columnAttributes) <= 0) {
            $this->columns($this->columns);
        }
        if ($this->isGetData) {
            return $this->data();
        } else {
            $viewData['componentName'] = $this->componentName;
            $viewData['routers'] = [
                'resource' => url(request()->getPathInfo())
            ];
            $viewData['keyName'] = $this->keyName;
            $viewData['selection'] = $this->attributes->selection;
            $viewData['tree'] = $this->tree;
            $viewData['defaultSort'] = $this->defaultSort;
            $viewData['columnAttributes'] = $this->columnAttributes;
            $viewData['attributes'] = (array)$this->attributes;
            $viewData['dataUrl'] = $this->dataUrl;
            $viewData['pageSizes'] = $this->pageSizes;
            $viewData['perPage'] = $this->perPage;
            $viewData['pageBackground'] = $this->pageBackground;
            //$viewData['actions'] = $this->actions->builderActions();
            $viewData['toolbars'] = $this->toolbars->builderData();
            $viewData['quickSearch'] = $this->quickSearch;
            $viewData['filter'] = $this->filter->buildFilter();
            return $viewData;
        }
    }
}
