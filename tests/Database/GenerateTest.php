<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2020, CitrusGeneration. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Test\Database;

use Citrus\CitrusException;
use Citrus\Database\Generate;
use Citrus\Migration;
use PHPUnit\Framework\TestCase;
use Test\TestFile;

/**
 * データベースオブジェクト生成処理のテスト
 */
class GenerateTest extends TestCase
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
        $this->output_dir = __DIR__ . '/../Sample/Integration';
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
            'integration' => [
                'database' => [
                    'type'      => 'sqlite',
                    'hostname'  => $this->sqlite_file,
                ],
                'output_dir' => $this->output_dir,
                'mode' => 0755,
                'owner' => posix_getpwuid(posix_geteuid())['name'],
                'group' => posix_getgrgid(posix_getegid())['name'],
                'namespace' => 'Test\\Sample',
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
//        $this->forceRemove($this->output_dir);
    }



    /**
     * @test
     * @throws CitrusException
     */
    public function 設定ファイル通りにディレクトリを生成()
    {
        // インスタンス生成と実行
        Migration::sharedInstance()->loadConfigures($this->configures);

        // ディレクトリができている
        $this->assertTrue(is_dir($this->output_dir));
    }



    /**
     * @test
     * @throws CitrusException
     */
    public function 各種ファイルを生成できる()
    {
        // テーブル生成
        $name = '20190102000000';
        $migration = Migration::sharedInstance()->loadConfigures($this->configures);
        $migration->up($name);

        // ファイル生成
        $generate = Generate::sharedInstance()->loadConfigures($this->configures);
        $generate->all('User', 'users');

        // ファイル生成されている
        $this->assertTrue(file_exists($this->output_dir . '/Condition/UserCondition.php'));
        $this->assertTrue(file_exists($this->output_dir . '/Dao/UserDao.php'));
        $this->assertTrue(file_exists($this->output_dir . '/Property/UserProperty.php'));
    }
}
