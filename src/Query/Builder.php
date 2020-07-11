<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2020, CitrusSqlmap. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Citrus\Query;

use Citrus\Database\Columns;
use Citrus\Database\Connection\Connection;
use Citrus\Database\Executor;
use Citrus\Database\QueryPack;
use Citrus\Database\ResultSet\ResultSet;
use Citrus\Intersection;
use Citrus\Sqlmap\Condition;
use Citrus\Sqlmap\Parser\Statement;

/**
 * クエリビルダ
 */
class Builder
{
    use Optimize;

    /** query type select */
    const QUERY_TYPE_SELECT = 'select';

    /** query type insert */
    const QUERY_TYPE_INSERT = 'insert';

    /** query type update */
    const QUERY_TYPE_UPDATE = 'update';

    /** query type delete */
    const QUERY_TYPE_DELETE = 'delete';

    /** @var Statement $statement */
    public $statement = null;

    /** @var array $parameters */
    public $parameters = [];

    /** @var string $query_type */
    public $query_type = self::QUERY_TYPE_SELECT;

    /** @var Connection */
    public $connection;



    /**
     * constructor.
     *
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }



    /**
     * build select statement
     *
     * @param string       $table_name
     * @param Columns|null $condition
     * @param array|null   $columns
     * @return Builder
     */
    public function select(string $table_name, Columns $condition = null, array $columns = null): Builder
    {
        // クエリタイプ
        $this->query_type = self::QUERY_TYPE_SELECT;

        // ステートメント
        $this->statement = new Statement();

        // カラム列挙
        $select_context = (true === is_array($columns) ? implode(', ', $columns) : '*');

        // テーブル名
        if (false === is_null($condition->schema))
        {
            $table_name .= sprintf('%s.%s', $condition->schema, $table_name);
        }
        // ベースクエリー
        $query = sprintf('SELECT %s FROM %s', $select_context, $table_name);

        // 検索条件,取得条件
        $_parameters = [];
        if (false === is_null($condition))
        {
            // 検索条件
            $properties = $condition->properties();
            $wheres = [];
            foreach ($properties as $ky => $vl)
            {
                if (is_null($vl) === true)
                {
                    continue;
                }

                $bind_ky = sprintf(':%s', $ky);
                $wheres[] = sprintf('%s = %s', $ky, $bind_ky);
                $_parameters[$bind_ky] = $vl;
            }
            // 検索条件がある場合
            if (false === empty($wheres))
            {
                $query = sprintf('%s WHERE %s', $query, implode(' AND ', $wheres));
            }

            // 取得条件
            $condition_traits = class_uses($condition);
            if (true === array_key_exists('CitrusSqlmapCondition', $condition_traits))
            {
                /** @var Condition $condition */

                // 順序
                if (false === is_null($condition->orderby))
                {
                    $query = sprintf('%s ORDER BY %s', $query, $condition->orderby);
                }

                // 制限
                if (false === is_null($condition->limit))
                {
                    $ky = 'limit';
                    $query = sprintf('%s LIMIT :%s', $query, $ky);
                    $_parameters[$ky] = $condition->limit;
                }
                if (is_null($condition->offset) === false)
                {
                    $ky = 'offset';
                    $query = sprintf('%s OFFSET :%s', $query, $ky);
                    $_parameters[$ky] = $condition->offset;
                }
            }
        }

        $this->statement->query = $query;
        $this->parameters = $_parameters;

        return $this;
    }



    /**
     * build insert statement
     *
     * @param string  $table_name
     * @param Columns $value
     * @return Builder
     */
    public function insert(string $table_name, Columns $value): Builder
    {
        // クエリタイプ
        $this->query_type = self::QUERY_TYPE_INSERT;

        // ステートメント
        $this->statement = new Statement();

        // 自動補完
        $value->completeCreateColumn();

        // 登録情報
        $columns = [];
        $_parameters = [];
        $properties = $value->properties();
        foreach ($properties as $ky => $vl)
        {
            if (true === is_null($vl))
            {
                continue;
            }

            $bind_ky = sprintf(':%s', $ky);
            $columns[$ky] = $bind_ky;
            $_parameters[$bind_ky] = $vl;
        }

        // テーブル名
        if (false === is_null($value->schema))
        {
            $table_name .= sprintf('%s.%s', $value->schema, $table_name);
        }

        // クエリ
        $query = sprintf('INSERT INTO %s (%s) VALUES (%s);',
            $table_name,
            implode(',', array_keys($columns)),
            implode(',', array_values($columns))
            );

        $this->statement->query = $query;
        $this->parameters = $_parameters;

        return $this;
    }



    /**
     * build update statement
     *
     * @param string  $table_name
     * @param Columns $value
     * @param Columns $condition
     * @return Builder
     */
    public function update(string $table_name, Columns $value, Columns $condition): Builder
    {
        // クエリタイプ
        $this->query_type = self::QUERY_TYPE_UPDATE;

        // ステートメント
        $this->statement = new Statement();

        // 自動補完
        $value->completeUpdateColumn();

        // 登録情報
        $columns = [];
        $_parameters = [];
        $properties = $value->properties();
        foreach ($properties as $ky => $vl)
        {
            if (is_null($vl) === true)
            {
                continue;
            }

            $bind_ky = sprintf(':%s', $ky);
            $columns[$ky] = sprintf('%s = %s', $ky, $bind_ky);
            $_parameters[$bind_ky] = $vl;
        }
        // 登録条件
        $wheres = [];
        $properties = $condition->properties();
        foreach ($properties as $ky => $vl)
        {
            if (is_null($vl) === true)
            {
                continue;
            }

            $bind_ky = sprintf(':condition_%s', $ky);
            $wheres[$ky] = sprintf('%s = %s', $ky, $bind_ky);
            $_parameters[$bind_ky] = $vl;
        }

        // テーブル名
        if (false === is_null($condition->schema))
        {
            $table_name .= sprintf('%s.%s', $condition->schema, $table_name);
        }

        // クエリ
        $query = sprintf('UPDATE %s SET %s WHERE %s;',
            $table_name,
            implode(', ', array_values($columns)),
            implode(' AND ', array_values($wheres))
        );

        $this->statement->query = $query;
        $this->parameters = $_parameters;

        return $this;
    }



    /**
     * build delete statement
     *
     * @param string  $table_name
     * @param Columns $condition
     * @return Builder
     */
    public function delete(string $table_name, Columns $condition): Builder
    {
        // クエリタイプ
        $this->query_type = self::QUERY_TYPE_UPDATE;

        // ステートメント
        $this->statement = new Statement();

        // 登録情報
        $wheres = [];
        $_parameters = [];
        $properties = $condition->properties();
        foreach ($properties as $ky => $vl)
        {
            if (is_null($vl) === true)
            {
                continue;
            }

            $bind_ky = sprintf(':%s', $ky);
            $wheres[$ky] = sprintf('%s = %s', $ky, $bind_ky);
            $_parameters[$bind_ky] = $vl;
        }

        // テーブル名
        if (false === is_null($condition->schema))
        {
            $table_name .= sprintf('%s.%s', $condition->schema, $table_name);
        }

        // クエリ
        $query = sprintf('DELETE FROM %s WHERE %s;',
            $table_name,
            implode(',', array_values($wheres))
        );

        $this->statement->query = $query;
        $this->parameters = $_parameters;

        return $this;
    }



    /**
     * execute
     *
     * @param string|null $result_class
     * @return array|bool|Columns[]|null|ResultSet
     */
    public function execute(string $result_class = null)
    {
        $result = null;

        // optimize parameters
        $_parameters = self::optimizeParameter($this->statement->query, $this->parameters);

        // クエリパック
        $queryPack = QueryPack::pack($this->statement->query, $_parameters, $result_class);

        return Intersection::fetch($this->query_type, [
            // select
            self::QUERY_TYPE_SELECT => function () use ($queryPack) {
                return (new Executor($this->connection))->select($queryPack);
            },
            // insert
            self::QUERY_TYPE_INSERT => function () use ($queryPack) {
                return (new Executor($this->connection))->insert($queryPack);
            },
            // update
            self::QUERY_TYPE_UPDATE => function () use ($queryPack) {
                return (new Executor($this->connection))->update($queryPack);
            },
            // delete
            self::QUERY_TYPE_DELETE => function () use ($queryPack) {
                return (new Executor($this->connection))->delete($queryPack);
            },
        ], true);
    }
}
