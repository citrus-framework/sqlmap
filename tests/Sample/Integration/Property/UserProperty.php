<?php

declare(strict_types=1);

/**
 * generated Citrus Property file at 2024-12-07 05:08:58
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
