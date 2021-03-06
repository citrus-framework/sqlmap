<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2020, CitrusSqlmap. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Citrus\Sqlmap\Parser;

use Citrus\Variable\Xmls;
use DOMNamedNodeMap;

/**
 * Sqlmapステートメント
 */
class Statement
{
    /** @var string ステートメントID */
    public $id;

    /** @var string パラメータ定義クラス */
    public $parameter_class;

    /** @var string 結果定義クラス */
    public $result_class;

    /** @var string クエリ文字列 */
    public $query;



    /**
     * constructor.
     *
     * @param DOMNamedNodeMap|null $attributes
     */
    public function __construct(?DOMNamedNodeMap $attributes = null)
    {
        if (true === is_null($attributes))
        {
            return;
        }

        $this->id = Xmls::getNamedItemValue($attributes, 'id');
        $this->result_class = Xmls::getNamedItemValue($attributes, 'resultClass');
        $this->parameter_class = Xmls::getNamedItemValue($attributes, 'parameterClass');
    }
}
