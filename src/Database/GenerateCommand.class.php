<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2020, CitrusGeneration. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Citrus\Database;

use Citrus\Configure\ConfigureException;
use Citrus\Console;

/**
 * データベースエンティティ生成コマンド
 */
class GenerateCommand extends Console
{
    /** @var array command options */
    protected $options = [
        'type::',
        'table_name::',
        'class_prefix:',
    ];



    /**
     * {@inheritDoc}
     *
     * @throws ConfigureException
     */
    public function execute(): void
    {
        parent::execute();

        $type = $this->parameter('type');
        $table_name = $this->parameter('table_name');
        $class_prefix = $this->parameter('class_prefix');

        $generate = Generate::sharedInstance()->loadConfigures($this->configures);

        // 実行
        switch ($type)
        {
            // Property生成処理
            case Generate::TYPE_PROPERTY:
                $generate->property($table_name, $class_prefix);
                break;
            // Dao生成処理
            case Generate::TYPE_DAO:
                $generate->dao($table_name, $class_prefix);
                break;
            // Condition生成処理
            case Generate::TYPE_CONDITION:
                $generate->condition($table_name, $class_prefix);
                break;
            // Property,Dao,Condition生成処理
            case Generate::TYPE_ALL:
                $generate->all($table_name, $class_prefix);
                break;
            default:
        }
    }
}
