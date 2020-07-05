<?php

declare(strict_types=1);

/**
 * generated Citrus Property file at 2020-07-05 14:54:31
 */

namespace Test\Sample\Integration\Property;

/**
 * UserProperty
 */
class UserProperty extends \Citrus\Database\Column
{
    /** @var int  */
    public $user_id;

    /** @var string  */
    public $name;



    /**
     * call primary keys
     *
     * @return string[]
     */
    public function callPrimaryKeys(): array
    {
        return [user_id];
    }



    /**
     * call condition
     *
     * @return \Test\Sample\Integration\Condition\UserCondition
     */
    public function callCondition(): \Test\Sample\Integration\Condition\UserCondition
    {
        if (true === is_null($this->condition))
        {
            $this->condition = new \Test\Sample\Integration\Condition\UserCondition();
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
