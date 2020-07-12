<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2020, CitrusSqlmap. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.citrus.tk/
 */

namespace Citrus\Sqlmap;

use Citrus\Database\Columns;
use Citrus\Database\DSN;
use Citrus\Database\QueryPack;
use Citrus\Sqlmap\Parser\Dynamic;
use Citrus\Sqlmap\Parser\Statement;
use Citrus\Variable\Strings;
use DOMDocument;
use DOMElement;
use DOMNodeList;
use DOMXPath;

/**
 * Sqlmapパーサー
 */
class Parser
{
    /** @var Statement statement */
    public $statement;

    /** @var array parameters */
    public $parameter_list = [];

    /** @var DOMDocument dom document */
    private $dom;

    /** @var DOMXPath dom xpath */
    private $xpath;

    /** @var Columns|Condition parameter */
    private $parameter;

    /** @var string Sqlmapのパス */
    private $path;

    /** @var string Sqlmap内の対象ID */
    private $statement_id;

    /** @var DSN DSN情報 */
    private $dsn;



    /**
     * パースして結果を取得
     *
     * @param string            $sqlmap_path  Sqlmapのパス
     * @param string            $statement_id Sqlmap内の対象ID
     * @param Columns|Condition $parameter    受付パラメタ
     * @param DSN                $dsn          DSN情報
     * @return Parser
     * @throws SqlmapException
     */
    public static function generate(string $sqlmap_path, string $statement_id, Columns $parameter, DSN $dsn): Parser
    {
        $self = new self();
        $self->path = $sqlmap_path;
        $self->statement_id = $statement_id;
        $self->parameter = $parameter;
        $self->dsn = $dsn;
        $self->parse();
        return $self;
    }



    /**
     * Sqlmapのパース
     *
     * @throws SqlmapException
     */
    public function parse(): void
    {
        // DOMの初期化
        $this->dom = new DOMDocument();
        $this->dom->load(realpath($this->path));
        $this->xpath = new DOMXPath($this->dom);
        $nodeList = $this->xpath->query(sprintf("/sqlMap/*[@id='%s']", $this->statement_id));
        // 見つからない場合
        SqlmapException::exceptionIf(
            (0 === $nodeList->length),
            sprintf(' Sqlmapファイル「%s」に「%s」の定義がありません', $this->path, $this->statement_id));
        // 逆に1つ以上見つかった場合
        SqlmapException::exceptionIf(
            (1 < $nodeList->length),
            sprintf(' Sqlmapファイル「%s」に「%s」が複数定義されています', $this->path, $this->statement_id));
        $element = $nodeList->item(0);

        // ステートメントの生成
        $this->statement = new Statement($element->attributes);

        // ノードのパース
        $nodes = $element->childNodes;
        $this->statement->query = $this->_nodes($nodes);

        // キーワードの置換
        if (true === Strings::isEmpty($this->parameter->schema))
        {
            $this->parameter->schema = $this->dsn->schema;
        }
        if (false === Strings::isEmpty($this->parameter->schema))
        {
            $this->statement->query = str_replace(
                '{SCHEMA}',
                '"' . $this->parameter->schema . '".',
                $this->statement->query);
        }
        // スキーマ制度がない場合に除去
        $this->statement->query = str_replace('{SCHEMA}', '', $this->statement->query);

        // パラメータの抽出
        $parameter_list = $this->parameter_list;
        $query = $this->statement->query;

        // 動的パラメータ
        if (false !== strrpos($query, '#'))
        {
            // パラメータ構文を抽出
            preg_match_all('/#[a-zA-Z0-9_\-\>\.]*#/', $query, $matches, PREG_PATTERN_ORDER);

            // マッチしたパラメータの当て込み
            foreach ($matches[0] as $one)
            {
                $match_code = str_replace('#', '', $one);
                $replace_code = (':' . str_replace('.', '__', $match_code));
                $parameter_list[$replace_code] = $this->callNestPropertyValue($match_code);
                // パラメータにリテラルではなく配列が入っていた場合
                if (true === is_array($parameter_list[$replace_code]))
                {
                    $array_replace_codes = [];
                    foreach ($parameter_list[$replace_code] as $ary_ky => $ary_vl)
                    {
                        $replace_key = sprintf('%s_%s', $replace_code, $ary_ky);
                        $array_replace_codes[] = $replace_key;
                        // パラメータとして再設定
                        $parameter_list[$replace_key] = $ary_vl;
                    }
                    // 再設定したので、既存の配列からは削除
                    unset($parameter_list[$replace_code]);
                    // IN句の IN (?, ?, ?)を想定している
                    $replace_code = implode(', ', $array_replace_codes);
                }
                $query = str_replace($one, $replace_code, $query);
            }
        }

        // staticなパラメータ
        if (false !== strrpos($query, '$'))
        {
            // パラメータ構文を抽出
            preg_match_all('/\$[a-zA-Z0-9_\-\>]*\$/', $query, $matches, PREG_PATTERN_ORDER);

            // マッチしたパラメータの置換
            foreach ($matches[0] as $one)
            {
                $match_code = str_replace('$', '', $one);
                $query = str_replace($one, $this->callNestPropertyValue($match_code), $query);
            }
        }

        // 余計な空白などを削除
        $query = strtr($query, ["\r" => ' ', "\n" => ' ', "\t" => ' ', '    ' => ' ', '  ' => ' ']);

        // parameters
        $this->parameter_list = $parameter_list;
        $this->statement->query = $query;
    }



    /**
     * replace sqlmap parameter
     *
     * @param Columns|null $parameter
     * @deprecated
     */
    public function replaceParameter(?Columns $parameter = null): void
    {
        $keys = array_keys($this->parameter_list);
        foreach ($keys as $key)
        {
            $column_key = str_replace(':', '', $key);
            $this->parameter_list[$key] = $parameter->get($column_key);
        }
    }



    /**
     * クエリパックに変換
     *
     * @return QueryPack
     */
    public function toPack(): QueryPack
    {
        return QueryPack::pack($this->statement->query, $this->parameter_list, $this->statement->result_class);
    }



    /**
     * node 要素汎用処理
     *
     * @param DOMNodeList  $nodes
     * @param Dynamic|null $dynamic
     * @return string
     */
    protected function _nodes(DOMNodeList $nodes, ?Dynamic $dynamic = null): string
    {
        $size = $nodes->length;
        for ($i = 0; $i < $size; $i++)
        {
            $item = $nodes->item($i);

            // なければ生成
            $dynamic = ($dynamic ?: new Dynamic());
            switch ($item->nodeName)
            {
                case '#text':
                    $text_query = self::_textQuery($item->nodeValue);
                    $dynamic->concatenateString($text_query);
                    break;
                case '#cdata-section':
                    $cdata_query = self::_cdataQuery($item->nodeValue);
                    $dynamic->concatenateString($cdata_query);
                    break;
                case '#comment':
                    // 処理なし
                    break;
                default:
                    $item_node = $this->{'_'.$item->nodeName}($item);
                    $dynamic->concatenate($item_node);
            }
        }
        return $dynamic->query;
    }



    /**
     * テキストノード処理
     *
     * @param string $text
     * @return Dynamic
     */
    protected static function _text(string $text): Dynamic
    {
        $dynamic = new Dynamic();
        $dynamic->query = (' ' . trim($text));

        return $dynamic;
    }



    /**
     * CDATAノード処理
     *
     * @param string $cdata
     * @return Dynamic
     */
    protected static function _cdata(string $cdata): Dynamic
    {
        $dynamic = new Dynamic();
        $dynamic->query = (' ' . trim($cdata));

        return $dynamic;
    }



    /**
     * テキストノードのクエリ処理
     *
     * @param string $text
     * @return string
     */
    protected static function _textQuery(string $text): string
    {
        return (' ' . trim($text));
    }



    /**
     * CDATAノードのクエリ処理
     *
     * @param string $cdata
     * @return string
     */
    protected static function _cdataQuery(string $cdata): string
    {
        return (' ' . trim($cdata));
    }



    /**
     * ダイナミックノード処理
     *
     * @param DOMElement $element
     * @return Dynamic
     */
    protected function _dynamic(DOMElement $element): Dynamic
    {
        $dynamic = new Dynamic($element->attributes);
        $this->_nodes($element->childNodes, $dynamic);

        return $dynamic;
    }



    /**
     * isNullノード処理
     *
     * @param DOMElement $element
     * @return Dynamic
     */
    protected function _isNull(DOMElement $element): Dynamic
    {
        $dynamic = new Dynamic($element->attributes);
        $property = $this->callNestPropertyValue($dynamic->property);

        if (true === is_null($property) or (true === is_string($property) and 'null' === strtolower($property)))
        {
            $dynamic->query = $this->_nodes($element->childNodes);
        }
        return $dynamic;
    }



    /**
     * isNotNullノード処理
     *
     * @param DOMElement $element
     * @return Dynamic
     */
    protected function _isNotNull(DOMElement $element): Dynamic
    {
        $dynamic = new Dynamic($element->attributes);
        $property = $this->callNestPropertyValue($dynamic->property);

        if (false === is_null($property) or (true === is_string($property) and 'null' !== strtolower($property)))
        {
            $dynamic->query = $this->_nodes($element->childNodes);
        }

        return $dynamic;
    }



    /**
     * isEmptyノード処理
     *
     * @param DOMElement $element
     * @return Dynamic
     */
    protected function _isEmpty(DOMElement $element): Dynamic
    {
        $dynamic = new Dynamic($element->attributes);
        $property = $this->callNestPropertyValue($dynamic->property);

        // emptyかどうかなので'empty()'メソッドを使う
        if (true === empty($property))
        {
            $dynamic->query = $this->_nodes($element->childNodes);
        }

        return $dynamic;
    }



    /**
     * isNotEmptyノード処理
     *
     * @param DOMElement $element
     * @return Dynamic
     */
    protected function _isNotEmpty(DOMElement $element)
    {
        $dynamic = new Dynamic($element->attributes);
        $property = $this->callNestPropertyValue($dynamic->property);

        if (false === empty($property))
        {
            $dynamic->query = $this->_nodes($element->childNodes);
        }

        return $dynamic;
    }



    /**
     * isEqualノード処理
     *
     * @param DOMElement $element
     * @return Dynamic
     */
    protected function _isEqual(DOMElement $element): Dynamic
    {
        $dynamic = new Dynamic($element->attributes);
        $compare = $this->callCompareObject($dynamic);
        $property = $this->callNestPropertyValue($dynamic->property);

        if ($property == $compare)
        {
            $dynamic->query = $this->_nodes($element->childNodes);
        }

        return $dynamic;
    }



    /**
     * isNotEqualノード処理
     *
     * @param DOMElement $element
     * @return Dynamic
     */
    protected function _isNotEqual(DOMElement $element): Dynamic
    {
        $dynamic = new Dynamic($element->attributes);
        $compare = $this->callCompareObject($dynamic);
        $property = $this->callNestPropertyValue($dynamic->property);

        if ($property != $compare)
        {
            $dynamic->query = $this->_nodes($element->childNodes);
        }

        return $dynamic;
    }



    /**
     * isGreaterThanノード処理
     *
     * @param DOMElement $element
     * @return Dynamic
     */
    protected function _isGreaterThan(DOMElement $element): Dynamic
    {
        $dynamic = new Dynamic($element->attributes);
        $compare = $this->callCompareObject($dynamic);

        if ($compare < $this->parameter->{$dynamic->property})
        {
            $dynamic->query = $this->_nodes($element->childNodes);
        }

        return $dynamic;
    }



    /**
     * isGreaterEqualノード処理
     *
     * @param DOMElement $element
     * @return Dynamic
     */
    protected function _isGreaterEqual(DOMElement $element): Dynamic
    {
        $dynamic = new Dynamic($element->attributes);
        $compare = $this->callCompareObject($dynamic);

        if ($compare <= $this->parameter->{$dynamic->property})
        {
            $dynamic->query = $this->_nodes($element->childNodes);
        }

        return $dynamic;
    }



    /**
     * isLessThanノード処理
     *
     * @param DOMElement $element
     * @return Dynamic
     */
    protected function _isLessThan(DOMElement $element): Dynamic
    {
        $dynamic = new Dynamic($element->attributes);
        $compare = $this->callCompareObject($dynamic);

        if ($compare > $this->parameter->{$dynamic->property})
        {
            $dynamic->query = $this->_nodes($element->childNodes);
        }

        return $dynamic;
    }



    /**
     * isLessEqualノード処理
     *
     * @param DOMElement $element
     * @return Dynamic
     */
    protected function _isLessEqual(DOMElement $element)
    {
        $dynamic = new Dynamic($element->attributes);
        $compare = $this->callCompareObject($dynamic);

        if ($compare >= $this->parameter->{$dynamic->property})
        {
            $dynamic->query = $this->_nodes($element->childNodes);
        }

        return $dynamic;
    }



    /**
     * isNumericノード処理
     *
     * @param DOMElement $element
     * @return Dynamic
     */
    protected function _isNumeric(DOMElement $element): Dynamic
    {
        $dynamic = new Dynamic($element->attributes);
        $property = $this->callNestPropertyValue($dynamic->property);

        if (true === is_numeric($property))
        {
            $dynamic->query = $this->_nodes($element->childNodes);
        }

        return $dynamic;
    }



    /**
     * isDatetimeノード処理
     *
     * @param DOMElement $element
     * @return Dynamic
     */
    protected function _isDatetime(DOMElement $element): Dynamic
    {
        $dynamic = new Dynamic($element->attributes);
        $property = $this->callNestPropertyValue($dynamic->property);

        if (false === is_null($property) and false !== strtotime($property))
        {
            $dynamic->query = $this->_nodes($element->childNodes);
        }

        return $dynamic;
    }



    /**
     * isTrue element node parser
     * isTrueノード処理
     *
     * @param DOMElement $element
     * @return Dynamic
     */
    protected function _isTrue(DOMElement $element): Dynamic
    {
        $dynamic = new Dynamic($element->attributes);
        $property = $this->callNestPropertyValue($dynamic->property);

        if (true === $property)
        {
            $dynamic->query = $this->_nodes($element->childNodes);
        }

        return $dynamic;
    }



    /**
     * include element node parser
     * includeノード処理
     *
     * @param DOMElement $element
     * @return Dynamic
     * @throws SqlmapException
     */
    protected function _include(DOMElement $element): Dynamic
    {
        $dynamic = new Dynamic($element->attributes);
        $include = Parser::generate($this->path, $dynamic->refid, $this->parameter, $this->dsn);
        $dynamic->query = $include->statement->query;
        $this->parameter_list += $include->parameter_list;

        return $dynamic;
    }



    /**
     * ネストの深いプロパティーを取得する。
     *
     * @param string $property  ex.) user.condition.user_id
     * @return mixed ex.) user_idの値
     */
    private function callNestPropertyValue(string $property)
    {
        $properties = explode('.', $property);
        $result = $this->parameter;
        foreach ($properties as $one)
        {
            $result = $result->$one;
        }
        return $result;
    }



    /**
     * 比較プロパティ、もしくは、比較血を取得
     *
     * @param Dynamic $dynamic 動的ノード
     * @return mixed
     */
    private function callCompareObject(Dynamic $dynamic)
    {
        return ($dynamic->compare_property ? $this->parameter->{$dynamic->compare_property} : $dynamic->compare_value);
    }
}
