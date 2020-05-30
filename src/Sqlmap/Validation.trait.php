<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2017, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Citrus\Sqlmap;

use Citrus\Database\Column;

/**
 * Sqlmapのバリデーション
 */
trait Validation
{
    /**
     * modify実行時の必須チェック
     *
     * @param Column $entity
     * @return bool
     * @throws SqlmapException
     */
    public function validateEssentialModify(Column $entity): bool
    {
        // 全変更の危険を回避
        if (0 === count(get_object_vars($entity->getCondition())))
        {
            throw new SqlmapException(sprintf('変更処理条件が足りていません。'));
        }
        return true;
    }
}
