<?php


namespace SmallRuralDog\Admin\Grid\Table;


trait TraitPageAttributes
{
    protected $pageSizes = [10, 20, 30, 50, 100];
    protected $perPage = 20;

    protected $pageBackground = false;

    /**
     * 每页显示个数选择器的选项设置
     * @param array $sizes
     * @return $this
     */
    public function setPageSizes($sizes)
    {
        $this->pageSizes = $sizes;

        return $this;
    }

    /**
     * 每页显示条目个数
     * @param int $perPage
     * @return $this
     */
    public function setPerPage($perPage)
    {
        $this->perPage = $perPage;

        return $this;
    }

    /**
     * 是否为分页按钮添加背景色
     * @param bool $pageBackground
     * @return $this
     */
    public function setPageBackground(bool $pageBackground = true)
    {
        $this->pageBackground = $pageBackground;

        return $this;
    }


}