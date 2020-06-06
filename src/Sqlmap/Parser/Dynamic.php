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
 * 動的ノード
 */
class Dynamic
{
    /** @var string エレメントID */
    public $id;

    /** @var string 参照ID */
    public $refid;

    /** @var string 先頭につける要素 */
    public $prepend;

    /** @var string プロパティ */
    public $property;

    /** @var string プロパティ比較 */
    public $compare_property;

    /** @var string 値比較 */
    public $compare_value;

    /** @var string 中身のクエリー */
    public $query = '';



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

        // 設定表
        $bind_keys = [
            'id' => 'id',
            'refid' => 'refid',
            'prepend' => 'prepend',
            'property' => 'property',
            'compareProperty' => 'compare_property',
            'compareValue' => 'compare_value',
        ];

        $items = Xmls::toList($attributes);
        foreach ($items as $name => $value)
        {
            // 設定キー
            $bind_key = $bind_keys[$name];
            // 設定
            $this->$bind_key = $value;
        }
    }



    /**
     * concatenate this
     *
     * @param Dynamic $dynamic
     */
    public function concatenate(Dynamic $dynamic): void
    {
        $_prepend = $dynamic->getPrepend();
        $_query = $dynamic->getQuery();

        $this_query = trim($this->query ?: '');
        $param_query = trim($_query);

        // 空文字なら終了
        if ('' === $param_query)
        {
            return;
        }

        // this と arg のオブジェクト内に query が存在したら、 prepend でコンカチする
        if ('' !== $this_query)
        {
            $_prepend = ('' === $_prepend ? '' : (' ' . $_prepend . ' '));
            $_query = ($_prepend . $_query);
        }

        $this->concatenateString($_query);
    }



    /**
     * concatenate this
     *
     * @param string $query
     */
    public function concatenateString(string $query): void
    {
        $param_query = trim($query);

        if (false === empty($param_query))
        {
            $this->query .= $query;
        }
    }



    /**
     * combine other to other
     *
     * @param Dynamic $dynamic
     * @param Dynamic $var
     * @return string
     */
    public static function combine(Dynamic $dynamic, Dynamic $var): string
    {
        if (trim($dynamic->query) && trim($var->query))
        {
            if (false === empty($var->prepend))
            {
                $var->prepend = ' '.$var->prepend.' ';
            }
            return $dynamic->query . $var->prepend . $var->query;
        }
        return '';
    }



    /**
     * prependの取得
     *
     * @return string
     */
    public function getPrepend(): string
    {
        return ($this->prepend ?: '');
    }



    /**
     * クエリの取得
     *
     * @return string
     */
    public function getQuery(): string
    {
        return ($this->query ?: '');
    }
}
