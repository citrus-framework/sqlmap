<?php

declare(strict_types=1);

/**
 * generated Citrus Property file at 2020-06-06 04:31:18
 */

namespace Test\Integration\Property;

/**
 * usersProperty
 */
class usersProperty extends \Citrus\Database\Column
{
    /**
     * call primary keys
     *
     * @return string[]
     */
    public function callPrimaryKeys(): array
    {
        return [];
    }



    /**
     * call condition
     *
     * @return \Test\Integration\Condition\usersCondition
     */
    public function callCondition(): \Test\Integration\Condition\usersCondition
    {
        if (true === is_null($this->condition))
        {
            $this->condition = new \Test\Integration\Condition\usersCondition();
            $this->condition->nullify();
        }
        $primary_keys = $this->callPrimaryKeys();
        foreach ($primary_keys as $primary_key)
        {
            $this->condition->$primary_key = $this->$primary_key;
        }

        return $this->condition;
    }
}
