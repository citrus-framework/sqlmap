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
use Citrus\Variable\Singleton;

/**
 * データベースオブジェクト生成処理
 */
class Generate extends Configurable
{
    use ConsoleOutput;
    use Singleton;

    /** @var string Propertyクラス */
    public const TYPE_PROPERTY = 'property';

    /** @var string Daoクラス */
    public const TYPE_DAO = 'dao';

    /** @var string Conditionクラス */
    public const TYPE_CONDITION = 'condition';

    /** @var string 全て */
    public const TYPE_ALL = 'all';

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
     * @param string $table_name   テーブル名
     * @param string $class_prefix クラス接頭辞
     */
    public function condition(string $table_name, string $class_prefix): void
    {
        // 生成クラス名
        $class_name = $class_prefix;
        // 出力ディレクトリ
        $output_dir = $this->configures['output_dir'];

        // propertyファイル内容
        $file_string = <<<EOT
<?php

declare(strict_types=1);

/**
 * generated Citrus Condition file at {#date#}
 */
 
namespace {#namespace#}\Integration\Condition;

use {#namespace#}\Integration\Property\{#class_name#}Property;
use Citrus\Sqlmap\Condition;

class {#class_name#}Condition extends {#class_name#}Property
{
    use Condition;
}

EOT;

        $file_string = str_replace('{#date#}', Dates::now()->formatTimestamp(), $file_string);
        $file_string = str_replace('{#namespace#}', $this->configures['namespace'], $file_string);
        $file_string = str_replace('{#table_name#}', $table_name, $file_string);
        $file_string = str_replace('{#class_name#}', $class_name, $file_string);

        $generate_class_path = sprintf('%s/Condition/%sCondition.class.php', $output_dir, $class_name);
        file_put_contents($generate_class_path, $file_string);
        $this->success(sprintf('generate class file => %s', $generate_class_path));
    }



    /**
     * Daoクラスの生成
     *
     * @param string $table_name   テーブル名
     * @param string $class_prefix クラス接頭辞
     */
    public function dao(string $table_name, string $class_prefix): void
    {
        // 生成クラス名
        $class_name = $class_prefix;
        // 出力ディレクトリ
        $output_dir = $this->configures['output_dir'];

        // ファイル内容
        $file_string = <<<EOT
<?php

declare(strict_types=1);

/**
 * generated Citrus Dao file at {#date#}
 */
 
namespace {#namespace#}\Integration\Dao;

use Citrus\Sqlmap\Crud;

class {#class_name#}Dao extends Crud
{
    /** @var string sqlmap_id */
    protected \$sqlmap_id = '{#sqlmap_id#}';
    
    /** @var string target */
    protected \$target = '{#table_name#}';
}

EOT;
        $sqlmap_id = implode('', array_map(function ($key)
        {
            return ucfirst($key);
        }, explode('_', $table_name)));

        $file_string = str_replace('{#date#}', Dates::now()->formatTimestamp(), $file_string);
        $file_string = str_replace('{#namespace#}', $this->configures['namespace'], $file_string);
        $file_string = str_replace('{#class_name#}', $class_name, $file_string);
        $file_string = str_replace('{#sqlmap_id#}', $sqlmap_id, $file_string);
        $file_string = str_replace('{#table_name#}', $table_name, $file_string);

        $generate_class_path = sprintf('%s/Dao/%sDao.class.php', $output_dir, $class_name);
        file_put_contents($generate_class_path, $file_string);
        $this->success(sprintf('generate class file => %s', $generate_class_path));
    }



    /**
     * Propertyクラスの生成
     *
     * @param string $table_name   テーブル名
     * @param string $class_prefix クラス接頭辞
     */
    public function property(string $table_name, string $class_prefix): void
    {
        // 生成クラス名
        $class_name = $class_prefix;
        // 出力ディレクトリ
        $output_dir = $this->configures['output_dir'];
        // カラム定義の取得
        $columns = $this->catalogManager->tableColumns($table_name);
        // コメント定義の取得
        $comments = $this->catalogManager->columnComments($table_name);
        // プライマリキーの取得
        $primary_keys = $this->catalogManager->primaryKeys($table_name);
        // デフォルトカラム
        $default_columns = array_keys(get_class_vars(Column::class));

        // propertyファイル内容
        $file_string = <<<EOT
<?php

declare(strict_types=1);

/**
 * generated Citrus Property file at {#date#}
 */
 
namespace {#namespace#}\Integration\Property;

use Citrus\Database\Column;
use {#namespace#}\Integration\Condition\{#class_name#}Condition;

class {#class_name#}Property extends Column
{
{#property#}


    /**
     * call primary keys
     *
     * @return string[]
     */
    public function callPrimaryKeys(): array
    {
        return [{#primary_keys#}];
    }



    /**
     * call condition
     *
     * @return {#class_name#}Condition
     */
    public function callCondition(): {#class_name#}Condition
    {
        if (is_null(\$this->condition) === true)
        {
            \$this->condition = new {#class_name#}Condition();
            \$this->condition->nullify();
        }
        \$primary_keys = \$this->callPrimaryKeys();
        foreach (\$primary_keys as \$primary_key)
        {
            \$this->condition->\$primary_key = \$this->\$primary_key;
        }

        return \$this->condition;
    }
}

EOT;
        $file_string = str_replace('{#date#}', Dates::now()->formatTimestamp(), $file_string);
        $file_string = str_replace('{#namespace#}', $this->configures['namespace'], $file_string);
        $file_string = str_replace('{#class_name#}', $class_name, $file_string);
        $file_string = str_replace('{#primary_keys#}', sprintf('\'%s\'', implode('\', \'', $primary_keys)), $file_string);

        // column property
        $properties = [];
        foreach ($columns as $column_name => $columnDef)
        {
            // データ取得
            $data_type = self::convertToPHPType($columnDef->data_type);
            $column_name = $columnDef->column_name;
            $comment = '';
            if (true === array_key_exists($column_name, $comments))
            {
                $comment = $comments[$column_name]->comment;
            }
            $property_name = '$' . $columnDef->column_name;

            // デフォルトカラムはスルー
            if (true === in_array($column_name, $default_columns))
            {
                continue;
            }

            // ベース文字列
            $property = <<<EOT
    /** @var {#class_name#} {#comment#} */
    public {#property_name#};

EOT;
            // 置換
            $property = str_replace('{#class_name#}', $data_type, $property);
            $property = str_replace('{#comment#}', $comment, $property);
            $property = str_replace('{#property_name#}', $property_name, $property);
            $properties[] = $property;
        }

        $file_string = str_replace('{#property#}', implode(PHP_EOL, $properties), $file_string);

        $generate_class_path = sprintf('%s/Property/%sProperty.class.php', $output_dir, $class_name);
        file_put_contents($generate_class_path, $file_string);
        $this->success(sprintf('generate class file => %s', $generate_class_path));
    }



    /**
     * クラスの一括生成
     *
     * @param string $table_name   テーブル名
     * @param string $class_prefix クラス接頭辞
     */
    public function all(string $table_name, string $class_prefix): void
    {
        $this->condition($table_name, $class_prefix);
        $this->dao($table_name, $class_prefix);
        $this->property($table_name, $class_prefix);
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
            'mode' => 0755,
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
        switch ($data_type)
        {
            case 'character varying':
            case 'text':
            case 'date':
            case 'timestamp without time zone':
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
                $data_type = 'double';
                break;
            default:
        }
        return $data_type;
    }




    /**
     * 出力ファイル格納ディレクトリパスの設定
     *
     * @return void
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
