<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2020, CitrusSqlmap. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Citrus\Sqlmap;

use Citrus\Database\Columns;

/**
 * 共通Entity
 */
trait Entity
{
    /** @var Condition */
    public $condition;

    /** @var string */
    public $condition_class;



    /**
     * Conditionを取得
     *
     * @return Condition|Columns
     */
    public function getCondition(): Columns
    {
        return $this->condition;
    }



    /**
     * Conditionを生成して返却
     *
     * @return Condition|Columns
     */
    public function callCondition(): Columns
    {
        if (true === is_null($this->condition))
        {
            $condition_class = $this->condition_class;
            $this->condition = new $condition_class();
            $this->condition->nullify();
        }
        if (true === method_exists($this, 'callPrimaryKeys'))
        {
            $primary_keys = $this->callPrimaryKeys();
            foreach ($primary_keys as $primary_key)
            {
                $this->condition->$primary_key = $this->$primary_key;
            }
        }

        return $this->condition;
    }
}
