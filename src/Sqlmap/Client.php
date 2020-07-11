<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2020, CitrusSqlmap. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Citrus\Sqlmap;

use Citrus\Database\Executor;

/**
 * SQLMAPのSQL実行クライアント
 */
class Client extends Executor
{
    /** @var string SQLMAPのパス */
    protected $sqlmap_path;



    /**
     * SQLMAPパスのセットアップ
     *
     * @param string $sqlmap_path SQLMAPファイルのパス
     * @return $this
     * @throws SqlmapException
     */
    public function setupSqlmapPath(string $sqlmap_path): self
    {
        // SQLMAPファイルが見つからない
        SqlmapException::exceptionElse(file_exists($sqlmap_path), 'SQLMAPが指定されていません。');

        // 問題なければ設定
        $this->sqlmap_path = $sqlmap_path;

        return $this;
    }
}
