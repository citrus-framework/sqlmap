<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2019, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Citrus\Sqlmap;

use Citrus\Database\Column;
use Citrus\Database\ResultSet\ResultSet;

/**
 * CRUD処理
 */
class Crud extends Client
{
    use Validation;



    /**
     * サマリークエリの実行結果
     *
     * @param Column $condition
     * @return ResultSet
     * @throws SqlmapException
     */
    public function summary(Column $condition): ResultSet
    {
        $parser = Parser::generate($this->sqlmap_path, 'summary', $condition, $this->connection->dsn);
        return $this->select($parser);
    }



    /**
     * 詳細クエリの実行結果
     *
     * @param Column $condition
     * @return ResultSet
     * @throws SqlmapException
     */
    public function detail(Column $condition): ResultSet
    {
        $parser = Parser::generate($this->sqlmap_path, 'detail', $condition, $this->connection->dsn);
        return $this->select($parser);
    }



    /**
     * 登録クエリ
     *
     * @param Column $entity
     * @return int
     * @throws SqlmapException
     */
    public function regist(Column $entity): int
    {
        $parser = Parser::generate($this->sqlmap_path, 'regist', $entity, $this->connection->dsn);
        return $this->insert($parser);
    }



    /**
     * 編集クエリ
     *
     * @param Column $entity
     * @return int
     * @throws SqlmapException
     */
    public function modify(Column $entity): int
    {
        // 全変更の危険を回避
        if (false === $this->validateEssentialModify($entity))
        {
            return 0;
        }

        $parser = Parser::generate($this->sqlmap_path, 'modify', $entity, $this->connection->dsn);
        return $this->update($parser);
    }



    /**
     * 削除クエリ
     *
     * @param Column $condition
     * @return int
     * @throws SqlmapException
     */
    public function remove(Column $condition): int
    {
        $parser = Parser::generate($this->sqlmap_path, 'remove', $condition, $this->connection->dsn);
        return $this->delete($parser);
    }
}
