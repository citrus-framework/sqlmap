<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2020, CitrusSqlmap. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Citrus\Query;

/**
 * クエリタイプ
 */
enum QueryType: string
{
    /** query type select */
    case SELECT = 'select';

    /** query type insert */
    case INSERT = 'insert';

    /** query type update */
    case UPDATE = 'update';

    /** query type delete */
    case DELETE = 'delete';
}
