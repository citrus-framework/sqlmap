<?php

declare(strict_types=1);

/**
 * generated Citrus Property file at 2020-07-11 19:02:50
 */

namespace Test\Sample\Integration\Property;

/**
 * UserProperty
 */
class UserProperty extends \Citrus\Database\Columns
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
        return ['user_id'];
    }
}
