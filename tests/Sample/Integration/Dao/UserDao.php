<?php

declare(strict_types=1);

/**
 * generated Citrus Dao file at 2024-06-30 04:20:15
 */

namespace Test\Sample\Integration\Dao;

/**
 * UserDao
 */
class UserDao extends \Citrus\Sqlmap\Crud
{
    use \Citrus\Variable\Singleton;



    /** @var string SQLMAP path */
    protected string $sqlmap_path = __DIR__ . '/../Sqlmap/Users.xml';
}
