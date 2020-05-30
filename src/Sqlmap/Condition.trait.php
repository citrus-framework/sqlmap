<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2017, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Citrus\Sqlmap;

/**
 * 共通条件
 */
trait Condition
{
    /** @var string keyword */
    public $keyword;

    /** @var int page */
    public $page;

    /** @var int limit */
    public $limit;

    /** @var int offset */
    public $offset;

    /** @var string orderby */
    public $orderby = null;

    /** @var bool is count */
    public $is_count = false;



    /**
     * constructor.
     */
    public function __construct()
    {
        $properties = get_object_vars($this);
        foreach ($properties as $ky => $vl)
        {
            if (false === in_array($ky, ['schema', 'condition']))
            {
                $this->$ky = null;
            }
        }
    }



    /**
     * page limit offset
     *
     * @param int|null $page
     * @param int|null $limit
     */
    public function pageLimit(int $page = 1, int $limit = 10)
    {
        // page
        $this->page = $page;

        // limit
        $this->limit = $limit;

        // offset
        $this->offset = ($this->offset ?: ($limit * ($page - 1)));
    }



    /**
     * 曖昧一致
     *
     * @param string|array|null $property
     */
    public function toLike($property = null)
    {
        // 配列であれば順次再起
        if (true === is_array($property))
        {
            foreach ($property as $one)
            {
                $this->toLike($one);
            }
            return;
        }
        // 文字列であれば設定
        if (true === is_string($this->$property))
        {
            $this->$property = self::like($this->$property);
        }
    }



    /**
     * 前方一致
     *
     * @param string|null $property
     */
    public function toLikePrefix(string $property = null)
    {
        if (true === is_string($this->$property))
        {
            $this->$property = self::likePrefix($this->$property);
        }
    }



    /**
     * 後方一致
     *
     * @param string|null $property
     */
    public function toLikeSuffix(string $property = null)
    {
        if (true === is_string($this->$property))
        {
            $this->$property = self::likeSuffix($this->$property);
        }
    }



    /**
     * 曖昧一致
     *
     * @param string|null $property
     * @return string|null
     */
    public static function like(string $property = null): ?string
    {
        if (true === is_string($property))
        {
            return str_replace(
                '%%',
                '%',
                ('%' . $property . '%')
            );
        }
        return null;
    }



    /**
     * 前方一致
     *
     * @param string|null $property
     * @return string|null
     */
    public static function likePrefix(string $property = null): ?string
    {
        if (true === is_string($property))
        {
            return str_replace(
                '%%',
                '%',
                ('%' . $property)
            );
        }
        return null;
    }



    /**
     * 後方一致
     *
     * @param string|null $property
     * @return string|null
     */
    public static function likeSuffix(string $property = null): ?string
    {
        if (true === is_string($property))
        {
            return str_replace(
                '%%',
                '%',
                ($property . '%')
            );
        }
        return null;
    }
}
