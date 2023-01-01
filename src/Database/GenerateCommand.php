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
            case GenerateType::PROPERTY->value:
                // Property生成処理
                $generate->property($class_prefix, $table_name);
                break;
            case GenerateType::DAO->value:
                // Dao生成処理
                $generate->dao($class_prefix, $table_name);
                break;
            case GenerateType::CONDITION->value:
                // Condition生成処理
                $generate->condition($class_prefix);
                break;
            case GenerateType::ALL->value:
                // Property,Dao,Condition生成処理
                $generate->all($class_prefix, $table_name);
                break;
            default:
        }
    }
}
