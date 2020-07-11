<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2020, CitrusSqlmap. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Citrus\Sqlmap;

use Citrus\Database\Columns;
use Citrus\Database\Result;
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
     * @param Columns $condition
     * @return ResultSet
     * @throws SqlmapException
     */
    public function summary(Columns $condition): ResultSet
    {
        $parser = Parser::generate($this->sqlmap_path, 'summary', $condition, $this->connection->dsn);
        return $this->selectQuery($parser);
    }



    /**
     * 詳細クエリの実行結果
     *
     * @param Columns $condition
     * @return ResultSet
     * @throws SqlmapException
     */
    public function detail(Columns $condition): ResultSet
    {
        $parser = Parser::generate($this->sqlmap_path, 'detail', $condition, $this->connection->dsn);
        return $this->selectQuery($parser);
    }



    /**
     * 件数クエリの実行結果
     *
     * @param Columns $condition
     * @return int
     * @throws SqlmapException
     */
    public function count(Columns $condition): int
    {
        $parser = Parser::generate($this->sqlmap_path, 'count', $condition, $this->connection->dsn);
        /** @var Result $result */
        $result = $this->selectQuery($parser)->one();
        return $result->count;
    }



    /**
     * 登録クエリ
     *
     * @param Columns $entity
     * @return int
     * @throws SqlmapException
     */
    public function create(Columns $entity): int
    {
        $parser = Parser::generate($this->sqlmap_path, 'create', $entity, $this->connection->dsn);
        return $this->insertQuery($parser);
    }



    /**
     * 編集クエリ
     *
     * @param Columns $entity
     * @return int
     * @throws SqlmapException
     */
    public function update(Columns $entity): int
    {
        // 全変更の危険を回避
        if (false === $this->validateEssentialModify($entity))
        {
            return 0;
        }

        $parser = Parser::generate($this->sqlmap_path, 'update', $entity, $this->connection->dsn);
        return $this->updateQuery($parser);
    }



    /**
     * 削除クエリ
     *
     * @param Columns $condition
     * @return int
     * @throws SqlmapException
     */
    public function remove(Columns $condition): int
    {
        $parser = Parser::generate($this->sqlmap_path, 'remove', $condition, $this->connection->dsn);
        return $this->deleteQuery($parser);
    }
}
