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
    /** @var string|null ステートメントID */
    public string|null $id;

    /** @var string|null パラメータ定義クラス */
    public string|null $parameter_class;

    /** @var string|null 結果定義クラス */
    public string|null $result_class;

    /** @var string|null クエリ文字列 */
    public string|null $query;



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
