<?php
/**
 * generated Citrus Property file at 2018-03-27 02:43:01
 */

namespace Test\Sample\Integration\Property;

use Citrus\Database\Column;
use Test\Sample\Integration\Condition\UserCondition;

class UserProperty extends Column
{
    /** @var int ユーザーID */
    public $user_id;

    /** @var string 名前 */
    public $name;



    /**
     * call primary keys
     *
     * @return string[]
     */
    public function callPrimaryKeys() : array
    {
        return ['user_id'];
    }



    /**
     * call condition
     *
     * @return UserCondition
     */
    public function callCondition() : UserCondition
    {
        if (true === is_null($this->condition))
        {
            $this->condition = new UserCondition();
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
