<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2020, CitrusGeneration. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Test\Sample;

use Citrus\Migration\Item;

/**
 * テスト用マイグレーションクラス
 */
class Citrus_20190101000000_CreateTableUsers extends Item
{
    /** @var string object name */
    public $object_name = 'users';



    /**
     * migration up
     *
     * @return string
     */
    public function up(): string
    {
        return <<<SQL
CREATE TABLE users (
    `user_id` int NOT NULL PRIMARY KEY,
    `name` TEXT
);
SQL;
    }



    /**
     * migration down
     *
     * @return string
     */
    public function down(): string
    {
        return <<<SQL
DROP TABLE users;
SQL;
    }
}