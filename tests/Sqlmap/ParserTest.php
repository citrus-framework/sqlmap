<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2020, CitrusSqlmap. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Test\Sqlmap;

use Citrus\Configure\ConfigureException;
use Citrus\Database\Connection\Connection;
use Citrus\Database\DSN;
use Citrus\Sqlmap\SqlmapException;
use PDO;
use PHPUnit\Framework\TestCase;
use Test\Sample\Business\Entity\UserEntity;
use Test\Sample\Integration\Condition\UserCondition;
use Test\Sample\Integration\Dao\UserDao;
use Test\TestFile;

/**
 * Sqlmapパース処理のテスト
 */
class ParserTest extends TestCase
{
    use TestFile;

    /** @var string 出力ディレクトリ */
    private $output_dir;

    /** @var string SQLITEファイル */
    private $sqlite_file;

    /** @var array 設定配列 */
    private $configures;

    /** @var Connection */
    private $connection;



    /**
     * {@inheritDoc}
     * @throws ConfigureException
     */
    public function setUp(): void
    {
        parent::setUp();

        // 出力ディレクトリ
        $this->output_dir = __DIR__ . '/temp';
        $this->sqlite_file = $this->output_dir . '/test.sqlite';

        // 設定配列
        $database = [
            'type'      => 'sqlite',
            'hostname'  => $this->sqlite_file,
        ];
        $this->configures = [
            'default' => [
                'database' => $database,
            ],
        ];
        // ディレクトリ生成
        mkdir($this->output_dir);
        chmod($this->output_dir, 0755);
        chown($this->output_dir, posix_getpwuid(posix_geteuid())['name']);
        chgrp($this->output_dir, posix_getgrgid(posix_getegid())['name']);

        // データ生成
        $pdo = new PDO(sprintf('sqlite:%s', $this->sqlite_file));
        $pdo->query('CREATE TABLE users (user_id INT, name TEXT, status INT, rowid INT, rev INT);');
        $pdo->query('INSERT INTO users VALUES (1, "hogehoge", 0, 1, 1);');
        $pdo->query('INSERT INTO users VALUES (2, "fugafuga", 0, 2, 1);');

        $dsn = DSN::getInstance()->loadConfigures($this->configures);
        $this->connection = new Connection($dsn);
    }



    /**
     * {@inheritDoc}
     */
    public function tearDown(): void
    {
        parent::tearDown();

        // ディレクトリがあったら削除
        $this->forceRemove($this->output_dir);
    }



    /**
     * @test
     * @throws SqlmapException
     */
    public function SELECT文を実行できる()
    {
        $dao = new UserDao($this->connection);

        // SELECT
        $condition = new UserCondition();
        $condition->user_id = 1;
        $resultSet = $dao->summary($condition);
        // 件数チェック
        $this->assertCount(1, $resultSet);
        /** @var UserEntity $entity */
        $entity = $resultSet->getIterator()->current();
        // 結果チェック
        $this->assertInstanceOf(UserEntity::class, $entity);
        $this->assertSame((1 * 10), $entity->user_id); // bindColumnで10倍にしてる
        $this->assertSame('hogehoge', $entity->name);
    }



    /**
     * @test
     * @throws SqlmapException
     */
    public function INSERT文を実行できる()
    {
        $dao = new UserDao($this->connection);

        // INSERT
        $entity = new UserEntity();
        $entity->user_id = 3;
        $entity->name = 'sansan';
        $dao->create($entity);
        // 再取得
        $resultSet = $dao->summary(new UserCondition());
        // 件数チェック
        $this->assertCount(3, $resultSet);
    }



    /**
     * @throws SqlmapException
     */
    public function UPDATE文を実行できる()
    {
        $dao = new UserDao($this->connection);

        // UPDATE
        $entity = new UserEntity();
        $entity->user_id = 3;
        $entity->name = 'sansan';
        $entity->getCondition()->user_id = 2;
        $dao->modify($entity);
        // 再取得
        $resultSet = $dao->summary(new UserCondition());
        // 件数チェック
        $this->assertCount(2, $resultSet);
        /** @var UserEntity $row */
        foreach ($resultSet as $row)
        {
            // 10 or 30
            $this->assertNotSame(2, $row->user_id);
        }
    }



    /**
     * @test
     * @throws SqlmapException
     */
    public function DELETE文を実行できる()
    {
        $dao = new UserDao($this->connection);

        // DELETE
        $condition = new UserCondition();
        $condition->user_id = 1;
        $dao->remove($condition);
        // 再取得
        $resultSet = $dao->summary(new UserCondition());
        // 件数チェック
        $this->assertCount(1, $resultSet);
        /** @var UserEntity $entity */
        $entity = $resultSet->getIterator()->current();
        $this->assertSame(20 , $entity->user_id);
    }
}
