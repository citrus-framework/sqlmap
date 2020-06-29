<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2020, CitrusSqlmap. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Citrus\Sqlmap;

use Citrus\Database\Connection\Connection;
use Citrus\Database\Connection\ConnectionPool;
use Citrus\Database\DatabaseException;
use Citrus\Database\ResultSet\ResultSet;
use PDOStatement;

/**
 * SQLMAPのSQL実行クライアント
 */
class Client
{
    /** @var Connection */
    protected $connection;

    /** @var string SQLMAPのパス */
    protected $sqlmap_path;



    /**
     * constructor.
     *
     * @param Connection|null $connection 接続情報
     * @param string|null $sqlmap_path SQLMAPのファイルパス
     * @throws SqlmapException
     */
    public function __construct(?Connection $connection = null, ?string $sqlmap_path = null)
    {
        // 指定がなければデフォルト
        $connection = ($connection ?: ConnectionPool::callDefault());
        $sqlmap_path = ($sqlmap_path ?? $this->sqlmap_path);
        // 設定して接続もしてしまう
        if (false === is_null($connection))
        {
            $this->connection = $connection;
            try
            {
                $this->connection->connect();
            }
            catch (DatabaseException $e)
            {
                /** @var SqlmapException $e */
                $e = SqlmapException::convert($e);
                throw $e;
            }
        }

        // SQLMAPパスのセットアップ
        $this->setupSqlmapPath($sqlmap_path);
    }



    /**
     * SELECT
     *
     * @param Parser $parser
     * @return ResultSet
     * @throws SqlmapException
     */
    public function selectQuery(Parser $parser): ResultSet
    {
        // プリペアとパラメータ設定
        $statement = $this->prepareAndBind($parser);

        return new ResultSet($statement, $parser->statement->result_class);
    }



    /**
     * INSERT
     *
     * @param Parser $parser
     * @return int
     * @throws SqlmapException
     */
    public function insertQuery(Parser $parser): int
    {
        // プリペアとパラメータ設定
        $statement = $this->prepareAndBind($parser);

        // 実行
        $statement->execute();

        return $statement->rowCount();
    }



    /**
     * UPDATE
     *
     * @param Parser $parser
     * @return int
     * @throws SqlmapException
     */
    public function updateQuery(Parser $parser): int
    {
        // プリペアとパラメータ設定
        $statement = $this->prepareAndBind($parser);

        // 実行
        $statement->execute();

        return $statement->rowCount();
    }



    /**
     * DELETE
     *
     * @param Parser $parser
     * @return int
     * @throws SqlmapException
     */
    public function deleteQuery(Parser $parser): int
    {
        // 削除全実行はフレームワークとして許容しない(全実行する場合は条件を明示的につける ex.)WHERE 1=1)
        SqlmapException::exceptionIf((0 === count($parser->parameter_list)), '削除条件が足りません、削除要求をキャンセルしました。');

        // プリペアとパラメータ設定
        $statement = $this->prepareAndBind($parser);

        // 実行
        $statement->execute();

        return $statement->rowCount();
    }



    /**
     * SQLMAPパスのセットアップ
     *
     * @param string $sqlmap_path SQLMAPファイルのパス
     * @throws SqlmapException
     */
    public function setupSqlmapPath(string $sqlmap_path): void
    {
        // SQLMAPファイルが見つからない
        SqlmapException::exceptionIf((false === file_exists($sqlmap_path)), 'SQLMAPが指定されていません。');

        // 問題なければ設定
        $this->sqlmap_path = $sqlmap_path;
    }



    /**
     * プリペアとパラメータ設定
     *
     * @param Parser $parser
     * @return PDOStatement
     * @throws SqlmapException
     */
    private function prepareAndBind(Parser $parser): PDOStatement
    {
        // ハンドル
        $handle = null;
        try
        {
            $handle = $this->connection->callHandle();
        }
        catch (DatabaseException $e)
        {
            /** @var SqlmapException $e */
            $e = SqlmapException::convert($e);
            throw $e;
        }

        // プリペア実行
        $statement = $handle->prepare($parser->statement->query);
        if (false === $statement)
        {
            /** @var SqlmapException $e */
            $e = SqlmapException::pdoErrorInfo($handle->errorInfo());
            throw $e;
        }

        // パラメータ設定
        foreach ($parser->parameter_list as $ky => $vl)
        {
            $statement->bindValue($ky, $vl);
        }

        return $statement;
    }
}
