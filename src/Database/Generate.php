<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2020, CitrusGeneration. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Citrus\Database;

use Citrus\Configure\Configurable;
use Citrus\Console\ConsoleOutput;
use Citrus\Database\Catalog\CatalogManager;
use Citrus\Variable\Dates;
use Citrus\Variable\Klass;
use Citrus\Variable\Klass\KlassFileComment;
use Citrus\Variable\Klass\KlassMethod;
use Citrus\Variable\Klass\KlassProperty;
use Citrus\Variable\Klass\KlassReturn;
use Citrus\Variable\Klass\KlassTrait;
use Citrus\Variable\Klass\KlassVisibility;
use Citrus\Variable\Singleton;
use Citrus\Variable\Strings;

/**
 * データベースオブジェクト生成処理
 */
class Generate extends Configurable
{
    use ConsoleOutput;
    use Singleton;

    /** @var CatalogManager カタログマネージャ */
    protected $catalogManager;



    /**
     * {@inheritDoc}
     */
    public function loadConfigures(array $configures = []): Configurable
    {
        // 設定配列の読み込み
        parent::loadConfigures($configures);

        // 出力ファイル出力パスの設定
        self::setupOutputDirectory();

        // DSN情報
        $dsn = DSN::getInstance()->loadConfigures($this->configures);

        // カタログマネージャ
        $this->catalogManager = new CatalogManager($dsn);

        return $this;
    }

    /**
     * Conditionクラスの生成
     *
     * @param string $class_prefix クラス接頭辞
     */
    public function condition(string $class_prefix): void
    {
        // 生成クラス名など
        $namespace = $this->configures['namespace'] . '\\Integration\\Condition';
        $class_name = $class_prefix . 'Condition';
        $extend_name = '\\' . $this->configures['namespace'] . '\\Integration\\Property\\' . $class_prefix . 'Property';
        // 出力ディレクトリ
        $output_dir = $this->configures['output_dir'];

        // クラス生成
        $klass = (new Klass($class_name))
            ->setStrictTypes(true)
            ->setFileComment(KlassFileComment::newRaw(
                sprintf('generated Citrus Condition file at %s', Dates::now()->formatTimestamp())
            ))
            ->setNamespace($namespace)
            ->setClassComment($class_name)
            ->setExtends($extend_name)
            ->addTrait(new KlassTrait('\\Citrus\\Sqlmap\\Condition'))
            ->addTrait(new KlassTrait('\\Citrus\\Variable\\PathBinders'))
        ;

        $generate_class_path = sprintf('%s/Condition/%s.php', $output_dir, $class_name);
        file_put_contents($generate_class_path, $klass->toString());
        $this->success(sprintf('generate class file => %s', $generate_class_path));
    }

    /**
     * Daoクラスの生成
     *
     * @param string $class_prefix クラス接頭辞
     * @param string $table_name   テーブル名
     */
    public function dao(string $class_prefix, string $table_name): void
    {
        // 生成クラス名など
        $namespace = $this->configures['namespace'] . '\\Integration\\Dao';
        $class_name = $class_prefix . 'Dao';
        $extend_name = '\\Citrus\\Sqlmap\\Crud';
        $sqlmap_path = '__DIR__ . \'/../Sqlmap/' . Strings::upperCamelCase($table_name) . '.xml\'';

        // 出力ディレクトリ
        $output_dir = $this->configures['output_dir'];

        // クラス生成
        $klass = (new Klass($class_name))
            ->setStrictTypes(true)
            ->setFileComment(
                KlassFileComment::newRaw(sprintf('generated Citrus Dao file at %s', Dates::now()->formatTimestamp()))
            )
            ->setNamespace($namespace)
            ->setClassComment($class_name)
            ->setExtends($extend_name)
            ->addTrait(new KlassTrait('\\Citrus\\Variable\\Singleton'))
            ->addProperty(KlassProperty::newProtectedString('sqlmap_path', $sqlmap_path, 'SQLMAP path'));

        $generate_class_path = sprintf('%s/Dao/%s.php', $output_dir, $class_name);
        file_put_contents($generate_class_path, $klass->toString());
        $this->success(sprintf('generate class file => %s', $generate_class_path));
    }

    /**
     * Propertyクラスの生成
     *
     * @param string $class_prefix クラス接頭辞
     * @param string $table_name   テーブル名
     */
    public function property(string $class_prefix, string $table_name): void
    {
        // カラム定義の取得
        $columns = $this->catalogManager->tableColumns($table_name);
        // コメント定義の取得
        $comments = $this->catalogManager->columnComments($table_name);
        // デフォルトカラム
        $default_columns = array_keys(get_class_vars(Columns::class));
        // プライマリキー文字列
        $primary_keys = '\'' . implode('\', \'', $this->catalogManager->primaryKeys($table_name)) . '\'';
        $primary_keys = sprintf('\'%s\'', $primary_keys);
        $primary_keys = str_replace('\'\'', '\'', $primary_keys);

        // 生成クラス名など
        $namespace = $this->configures['namespace'] . '\\Integration\\Property';
        $class_name = $class_prefix . 'Property';
        $extend_name = '\\Citrus\\Database\\Columns';

        // 出力ディレクトリ
        $output_dir = $this->configures['output_dir'];

        // クラス生成
        $klass = (new Klass($class_name))
            ->setStrictTypes(true)
            ->setFileComment(KlassFileComment::newRaw(
                sprintf('generated Citrus Property file at %s', Dates::now()->formatTimestamp())
            ))
            ->setNamespace($namespace)
            ->setClassComment($class_name)
            ->setExtends($extend_name)
            ->addMethod(
                (new KlassMethod(KlassVisibility::TYPE_PUBLIC, 'callPrimaryKeys', false, 'call primary keys'))
                    ->setReturn(new KlassReturn('string[]'))
                    ->setBody(
                        <<<BODY
        return [{$primary_keys}];
BODY
                    )
            );

        foreach ($columns as $columnDef)
        {
            // データ型
            $data_type = self::convertToPHPType($columnDef->data_type);
            // カラム名
            $column_name = $columnDef->column_name;
            // デフォルトカラムはスルー
            if (true === in_array($column_name, $default_columns))
            {
                continue;
            }
            // コメント
            $comment = (true === array_key_exists($column_name, $comments) ? $comments[$column_name]->comment : '');
            // プロパティ追加
            $klass->addProperty(new KlassProperty($data_type . '|null', $column_name, 'null', $comment));
        }

        $generate_class_path = sprintf('%s/Property/%s.php', $output_dir, $class_name);
        file_put_contents($generate_class_path, $klass->toString());
        $this->success(sprintf('generate class file => %s', $generate_class_path));
    }

    /**
     * クラスの一括生成
     *
     * @param string $class_prefix クラス接頭辞
     * @param string $table_name   テーブル名
     */
    public function all(string $class_prefix, string $table_name): void
    {
        $this->condition($class_prefix);
        $this->dao($class_prefix, $table_name);
        $this->property($class_prefix, $table_name);
    }

    /**
     * {@inheritDoc}
     */
    protected function configureKey(): string
    {
        return 'integration';
    }

    /**
     * {@inheritDoc}
     */
    protected function configureDefaults(): array
    {
        return [
            'mode'  => 0755,
            'owner' => posix_getpwuid(posix_geteuid())['name'],
            'group' => posix_getgrgid(posix_getegid())['name'],
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function configureRequires(): array
    {
        return [
            'database',
            'mode',
            'owner',
            'group',
            'output_dir',
            'namespace',
        ];
    }

    /**
     * テーブルカラムの型からPHPの型に変換
     *
     * @param string $data_type カラムデータタイプ
     * @return string PHPの型
     */
    private static function convertToPHPType(string $data_type): string
    {
        $data_type = strtolower($data_type);
        switch ($data_type)
        {
            case 'character varying':
            case 'text':
            case 'date':
            case 'timestamp without time zone':
            case 'timestamp with time zone':
                // 文字列
                $data_type = 'string';
                break;
            case 'integer':
            case 'bigint':
                // 整数
                $data_type = 'int';
                break;
            case 'numeric':
                // 浮動小数点
                $data_type = 'float|string';
                break;
            default:
        }
        return $data_type;
    }

    /**
     * 出力ファイル格納ディレクトリパスの設定
     */
    private function setupOutputDirectory(): void
    {
        // 出力ディレクトリ
        $parent_dir = $this->configures['output_dir'];

        // 各ディレクトリ
        $dirs = [
            '/Condition',
            '/Dao',
            '/Property',
        ];

        foreach ($dirs as $dir)
        {
            // 各出力ディレクトリ
            $output_dir = ($parent_dir . $dir);

            // ディレクトリがなければ生成
            if (false === file_exists($output_dir))
            {
                mkdir($output_dir);
                chmod($output_dir, $this->configures['mode']);
                chown($output_dir, $this->configures['owner']);
                chgrp($output_dir, $this->configures['group']);
            }
        }
    }
}
