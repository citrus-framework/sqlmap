<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2020, CitrusSqlmap. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Citrus\Sqlmap;

use Citrus\Database\Columns;
use Citrus\Database\DatabaseException;
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
        try
        {
            return $this->selectQuery($parser->toPack());
        }
        catch (DatabaseException $e)
        {
            throw SqlmapException::convert($e);
        }
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
        try
        {
            return $this->selectQuery($parser->toPack());
        }
        catch (DatabaseException $e)
        {
            throw SqlmapException::convert($e);
        }
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
        try
        {
            /** @var Result $result */
            $result = $this->selectQuery($parser->toPack())->one();
            return $result->count;
        }
        catch (DatabaseException $e)
        {
            throw SqlmapException::convert($e);
        }
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
        try
        {
            return $this->insertQuery($parser->toPack());
        }
        catch (DatabaseException $e)
        {
            throw SqlmapException::convert($e);
        }
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
        try
        {
            return $this->updateQuery($parser->toPack());
        }
        catch (DatabaseException $e)
        {
            throw SqlmapException::convert($e);
        }
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
        try
        {
            return $this->deleteQuery($parser->toPack());
        }
        catch (DatabaseException $e)
        {
            throw SqlmapException::convert($e);
        }
    }
}
