<?php

declare(strict_types=1);

/**
 * generated Citrus Property file at 2020-06-06 04:26:47
 */

namespace Test\Integration\Property;

/**
 * UserProperty
 */
class UserProperty extends \Citrus\Database\Column
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
     * @return \Test\Integration\Condition\UserCondition
     */
    public function callCondition(): \Test\Integration\Condition\UserCondition
    {
        if (true === is_null($this->condition))
        {
            $this->condition = new \Test\Integration\Condition\UserCondition();
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
