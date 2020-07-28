<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2020, CitrusSqlmap. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Citrus\Sqlmap;

use Citrus\Database\Columns;

/**
 * Sqlmapのバリデーション
 */
trait Validation
{
    /**
     * update実行時の必須チェック
     *
     * @param Columns $entity
     * @return bool
     * @throws SqlmapException
     */
    public function validateEssentialModify(Columns $entity): bool
    {
        // 全変更の危険を回避
        if (true === method_exists($entity, 'callCondition') and
            0 === count(get_object_vars($entity->callCondition())))
        {
            throw new SqlmapException(sprintf('変更処理条件が足りていません。'));
        }
        return true;
    }
}
