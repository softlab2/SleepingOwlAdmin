<?php

namespace SleepingOwl\Admin\Display\Extension;

use Illuminate\Support\Collection;
use KodiComponents\Support\HtmlAttributes;
use Request;
use SleepingOwl\Admin\Contracts\ColumnFilterInterface;
use SleepingOwl\Admin\Contracts\Display\Placable;
use SleepingOwl\Admin\Contracts\Initializable;

class ColumnFilters extends Extension implements Initializable, Placable
{
    use HtmlAttributes;

    /**
     * @var ColumnFilterInterface[]
     */
    protected $columnFilters = [];

    /**
     * @var string|\Illuminate\View\View
     */
    protected $view = 'display.extensions.columns_filters_table';

    /**
     * @var string
     */
    protected $placement = 'table.footer';

    /**
     * @param array|ColumnFilterInterface $columnFilters
     *
     * @return $this
     */
    public function set($columnFilters)
    {
        if (! is_array($columnFilters)) {
            $columnFilters = func_get_args();
        }

        $this->columnFilters = [];

        foreach ($columnFilters as $filter) {
            $this->push($filter);
        }

        return $this;
    }

    /**
     * @return Collection|\SleepingOwl\Admin\Contracts\ActionInterface[]
     */
    public function all()
    {
        return $this->columnFilters;
    }

    /**
     * @param ColumnFilterInterface $filter
     *
     * @return $this
     */
    public function push(ColumnFilterInterface $filter = null)
    {
        $this->columnFilters[] = $filter;

        return $this;
    }

    /**
     * @return string|\Illuminate\View\View
     */
    public function getView()
    {
        return $this->view;
    }

    /**
     * @param string|\Illuminate\View\View $view
     *
     * @return $this
     */
    public function setView($view)
    {
        $this->view = $view;

        return $this;
    }

    /**
     * @return string
     */
    public function getPlacement()
    {
        return $this->placement;
    }

    /**
     * @param string $placement
     *
     * @return $this
     */
    public function setPlacement($placement)
    {
        $this->placement = $placement;

        return $this;
    }

    /**
     * @deprecated use getPlacement()
     * @return string
     */
    public function getPosition()
    {
        return $this->getPlacement();
    }

    /**
     * @deprecated use setPlacement(string $placement)
     * @param string $position
     *
     * @return $this
     */
    public function setPosition($position)
    {
        return $this->setPlacement($position);
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'filters' => $this->columnFilters,
            'attributes' => $this->htmlAttributesToString(),
            'tag' => $this->getPlacement() == 'table.header' ? 'thead' : 'tfoot',
        ];
    }

    /**
     * Initialize class.
     */
    public function initialize()
    {
        if (empty($this->columnFilters)) {
            return;
        }

        foreach ($this->all() as $filter) {
            if ($filter instanceof Initializable) {
                $filter->initialize();
            }
        }

        if (! in_array($this->getPlacement(), ['table.footer', 'table.header']) && $this->view == 'display.extensions.columns_filters_table') {
            $this->view = 'display.extensions.columns_filters';
            $this->setHtmlAttribute('class', 'table table-default');
        }

        if (! $this->hasHtmlAttribute('class')) {
            $this->setHtmlAttribute('class', 'panel-footer');
        }
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     */
    public function modifyQuery(\Illuminate\Database\Eloquent\Builder $query)
    {
        $this->applyColumnSearch($query);
    }


    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     */
    protected function applyColumnSearch(\Illuminate\Database\Eloquent\Builder $query)
    {
        $search = Request::input('columns', []);

        $display = $this->getDisplay();

        if (! $display->getExtensions()->has('columns')) {
            return;
        }

        $columns = $display->getColumns()->all();

        if (! is_int(key($search))) {
            $search = [$search];
        }

        foreach ($search as $index => $columnData) {
            $column = $columns->get($index);
            $columnFilter = array_get($this->all(), $index);

            if ($column && $columnFilter) {
                $columnFilter->apply(
                    $column,
                    $query,
                    array_get($columnData, 'search.value'),
                    array_get($columnData, 'search')
                );
            }
        }
    }
}
