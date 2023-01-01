<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2020, CitrusSqlmap. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Citrus\Query;

use Citrus\Collection;
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

    /** @var Statement|null $statement */
    public Statement|null $statement = null;

    /** @var array $parameters */
    public array $parameters = [];

    /** @var QueryType $queryType */
    public QueryType $queryType = QueryType::SELECT;



    /**
     * constructor.
     *
     * @param Connection $connection 接続情報
     */
    public function __construct(
        private readonly Connection $connection
    ) {
    }

    /**
     * SELECT文の生成
     *
     * @param string                 $table_name
     * @param Columns|Condition|null $condition
     * @param array|null             $columns
     * @return Builder
     */
    public function select(string $table_name, Columns|Condition|null $condition = null, array|null $columns = null): Builder
    {
        // クエリタイプ
        $this->queryType = QueryType::SELECT;

        // カラム列挙
        $select_context = (true === is_array($columns) ? implode(', ', $columns) : '*');

        // テーブル名
        $table_name = $this->tableNameWithSchema($table_name, $condition);
        // ベースクエリー
        $query = sprintf('SELECT %s FROM %s', $select_context, $table_name);

        // 検索条件,取得条件
        $parameters = [];
        if (false === is_null($condition))
        {
            // 検索条件
            $wheres = [];
            $properties = Collection::stream($condition->properties())->notNull()->toList();
            foreach ($properties as $ky => $vl)
            {
                $bind_ky = sprintf(':%s', $ky);
                $wheres[] = sprintf('%s = %s', $ky, $bind_ky);
                $parameters[$bind_ky] = $vl;
            }
            // 検索条件がある場合
            if (0 < count($wheres))
            {
                $query = sprintf('%s WHERE %s', $query, implode(' AND ', $wheres));
            }

            // 取得条件
            $condition_traits = class_uses($condition);
            if (true === array_key_exists('Condition', $condition_traits))
            {
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
                    $parameters[$ky] = $condition->limit;
                }
                if (false === is_null($condition->offset))
                {
                    $ky = 'offset';
                    $query = sprintf('%s OFFSET :%s', $query, $ky);
                    $parameters[$ky] = $condition->offset;
                }
            }
        }

        // ステートメント
        $this->statement = new Statement();
        $this->statement->query = $query;
        $this->parameters = $parameters;

        return $this;
    }

    /**
     * INSERT文の生成
     *
     * @param string  $table_name
     * @param Columns $value
     * @return Builder
     */
    public function insert(string $table_name, Columns $value): Builder
    {
        // クエリタイプ
        $this->queryType = QueryType::INSERT;

        // 自動補完
        $value->completeCreateColumn();

        // 登録情報
        $columns = [];
        $parameters = [];
        $properties = Collection::stream($value->properties())->notNull()->toList();
        foreach ($properties as $ky => $vl)
        {
            $bind_ky = sprintf(':%s', $ky);
            $columns[$ky] = $bind_ky;
            $parameters[$bind_ky] = $vl;
        }

        // テーブル名
        $table_name = $this->tableNameWithSchema($table_name, $value);

        // クエリ
        $query = sprintf(
            'INSERT INTO %s (%s) VALUES (%s);',
            $table_name,
            implode(',', array_keys($columns)),
            implode(',', array_values($columns)),
        );

        // ステートメント
        $this->statement = new Statement();
        $this->statement->query = $query;
        $this->parameters = $parameters;

        return $this;
    }

    /**
     * UPDATE文の生成
     *
     * @param string  $table_name
     * @param Columns $value
     * @param Columns $condition
     * @return Builder
     */
    public function update(string $table_name, Columns $value, Columns $condition): Builder
    {
        // クエリタイプ
        $this->queryType = QueryType::UPDATE;

        // 自動補完
        $value->completeUpdateColumn();

        // 登録情報
        $columns = [];
        $parameters = [];
        $properties = Collection::stream($value->properties())->notNull()->toList();
        foreach ($properties as $ky => $vl)
        {
            $bind_ky = sprintf(':%s', $ky);
            $columns[$ky] = sprintf('%s = %s', $ky, $bind_ky);
            $parameters[$bind_ky] = $vl;
        }
        // 登録条件
        $wheres = [];
        $properties = Collection::stream($condition->properties())->notNull()->toList();
        foreach ($properties as $ky => $vl)
        {
            $bind_ky = sprintf(':condition_%s', $ky);
            $wheres[$ky] = sprintf('%s = %s', $ky, $bind_ky);
            $parameters[$bind_ky] = $vl;
        }

        // テーブル名
        $table_name = $this->tableNameWithSchema($table_name, $condition);

        // クエリ
        $query = sprintf(
            'UPDATE %s SET %s WHERE %s;',
            $table_name,
            implode(', ', array_values($columns)),
            implode(' AND ', array_values($wheres)),
        );

        // ステートメント
        $this->statement = new Statement();
        $this->statement->query = $query;
        $this->parameters = $parameters;

        return $this;
    }

    /**
     * DELETE文の生成
     *
     * @param string  $table_name
     * @param Columns $condition
     * @return Builder
     */
    public function delete(string $table_name, Columns $condition): Builder
    {
        // クエリタイプ
        $this->queryType = QueryType::UPDATE;

        // 登録情報
        $wheres = [];
        $parameters = [];
        $properties = Collection::stream($condition->properties())->notNull()->toList();
        foreach ($properties as $ky => $vl)
        {
            $bind_ky = sprintf(':%s', $ky);
            $wheres[$ky] = sprintf('%s = %s', $ky, $bind_ky);
            $parameters[$bind_ky] = $vl;
        }

        // テーブル名
        $table_name = $this->tableNameWithSchema($table_name, $condition);

        // クエリ
        $query = sprintf(
            'DELETE FROM %s WHERE %s;',
            $table_name,
            implode(',', array_values($wheres)),
        );

        // ステートメント
        $this->statement = new Statement();
        $this->statement->query = $query;
        $this->parameters = $parameters;

        return $this;
    }

    /**
     * 実行
     *
     * @param string|null $result_class
     * @return array|bool|Columns[]|ResultSet|null
     */
    public function execute(string|null $result_class = null)
    {
        // optimize parameters
        $parameters = self::optimizeParameter($this->statement->query, $this->parameters);

        // クエリパック
        $queryPack = QueryPack::pack($this->statement->query, $parameters, $result_class);

        return Intersection::fetch($this->queryType->value, [
            // select
            QueryType::SELECT->value => function () use ($queryPack) {
                return (new Executor($this->connection))->selectQuery($queryPack);
            },
            // insert
            QueryType::INSERT->value => function () use ($queryPack) {
                return (new Executor($this->connection))->insertQuery($queryPack);
            },
            // update
            QueryType::UPDATE->value => function () use ($queryPack) {
                return (new Executor($this->connection))->updateQuery($queryPack);
            },
            // delete
            QueryType::DELETE->value => function () use ($queryPack) {
                return (new Executor($this->connection))->deleteQuery($queryPack);
            },
        ], true);
    }

    /**
     * スキーマ指定がある場合は、テーブル名に付与する
     *
     * @param string  $table_name テーブル名
     * @param Columns $columns    カラム定義/条件定義
     * @return string スキーマ付きテーブル名
     */
    private function tableNameWithSchema(string $table_name, Columns $columns): string
    {
        if (false === is_null($columns->schema))
        {
            return sprintf('%s.%s', $columns->schema, $table_name);
        }
        return $table_name;
    }
}
