<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2020, CitrusDatabase. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Test\Database\Catalog;

use Citrus\CitrusException;
use Citrus\Database\Catalog\CatalogManager;
use Citrus\Database\DSN;
use Citrus\Migration;
use Citrus\Migration\VersionManager;
use PHPUnit\Framework\TestCase;
use Test\TestFile;


/**
 * データベースのカタログ取得関係のテスト
 */
class CatalogManagerTest extends TestCase
{
    use TestFile;

    /** @var string 出力ディレクトリ */
    private $output_dir;

    /** @var string SQLITEファイル */
    private $sqlite_file;

    /** @var array 設定配列 */
    private $configures;



    /**
     * {@inheritDoc}
     */
    public function setUp(): void
    {
        parent::setUp();

        // 出力ディレクトリ
        $this->output_dir = __DIR__ . '/.migration';
        $this->sqlite_file = $this->output_dir . '/test.sqlite';

        // 設定配列
        $this->configures = [
            'migration' => [
                'database' => [
                    'type'      => 'sqlite',
                    'hostname'  => $this->sqlite_file,
                ],
                'output_dir' => $this->output_dir,
                'mode' => 0755,
                'owner' => posix_getpwuid(posix_geteuid())['name'],
                'group' => posix_getgrgid(posix_getegid())['name'],
            ],
        ];
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
     * @throws CitrusException
     */
    public function 生成したテーブル情報が取得できる()
    {
        // インスタンスの生成
        $migration = Migration::sharedInstance()->loadConfigures($this->configures);
        /** @var DSN $dsn */
        $dsn = DSN::getInstance()->loadConfigures($migration->configures);
        // バージョンマネージャー
        $versionManager = new VersionManager($dsn);

        // マイグレーションの正方向実行
        include_once(__DIR__ . '/../../Sample/Citrus_20190102000000_CreateTableUsers.php');
        $class_name = 'Citrus_20190102000000_CreateTableUsers';
        $migrationItem = new $class_name();
        $versionManager->up($migrationItem);

        // テーブル名
        $table_name = $migrationItem->object_name;

        // カタログマネージャ
        $catalogManager = new CatalogManager($dsn);
        // カラム取得
        $tableColumns = $catalogManager->tableColumns($table_name);
        $this->assertCount(2, $tableColumns);
        foreach ($tableColumns as $column_name => $tableColumn)
        {
            if ('user_id' === $column_name)
            {
                $this->assertSame('INT', $tableColumn->data_type);
            }
            else if ('name' === $column_name)
            {
                $this->assertSame('TEXT', $tableColumn->data_type);
            }
        }

        // プライマリキー取得
        $primaryKeys = $catalogManager->primaryKeys($table_name);
        $this->assertSame('user_id', $primaryKeys[0]);
    }
}
