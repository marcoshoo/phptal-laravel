<?php

namespace MarcosHoo\LaravelPHPTAL;

use PHPTAL_Filter;

class PHPTALFilterChain implements PHPTAL_Filter
{
    /**
     *
     * @var array
     */
    protected $filters = [];

    /**
     *
     * @param PHPTAL_Filter $filter
     */
    public function add(PHPTAL_Filter $filter)
    {
        $this->filters[] = $filter;
    }

    /**
     *
     * @param string $source
     */
    public function filter($source)
    {
        foreach ($this->filters as $filter) {
            $source = $filter->filter($source);
        }
        return $source;
    }
}
