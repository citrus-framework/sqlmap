<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2020, CitrusSqlmap. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Citrus\Sqlmap;

use Citrus\Database\DatabaseException;

/**
 * Sqlmap用例外
 */
class SqlmapException extends DatabaseException
{
    /**
     * {@inheritDoc}
     *
     * @throws SqlmapException
     */
    public static function exceptionIf($expr, string $message): void
    {
        parent::exceptionIf($expr, $message);
    }



    /**
     * {@inheritDoc}
     *
     * @throws SqlmapException
     */
    public static function exceptionElse($expr, string $message): void
    {
        parent::exceptionElse($expr, $message);
    }
}
