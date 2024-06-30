<?php

declare(strict_types=1);

/**
 * generated Citrus Property file at 2024-06-30 04:20:15
 */

namespace Test\Sample\Integration\Property;

/**
 * UserProperty
 */
class UserProperty extends \Citrus\Database\Columns
{
    /** @var int|null  */
    public int|null $user_id = null;

    /** @var string|null  */
    public string|null $name = null;



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
