<?php
namespace App\Extend\OpenSearch;
/*
 * Licensed to the Apache Software Foundation (ASF) under one
 * or more contributor license agreements.  See the NOTICE file
 * distributed with this work for additional information
 * regarding copyright ownership.  The ASF licenses this file
 * to you under the Apache License, Version 2.0 (the
 * "License"); you may not use this file except in compliance
 * with the License.  You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing,
 * software distributed under the License is distributed on an
 * "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY
 * KIND, either express or implied.  See the License for the
 * specific language governing permissions and limitations
 * under the License.
 */

/**
 * opensearch 搜索接口。
 *
 * 此接口提供给用户通过简单的方式来生成问天3的语法，并提交服务进行查询。
 *
 * 此接口生成的http 请求串的参数包含：query、client_id、index_name、fetch_fields、
 * formula_name和summary。
 *
 * example：
 * <code>
 * $search = new CloudsearchSearch($client);
 * $search->search(array('indexes' => 'my_indexname'));
 * </code>
 * 或
 *
 * <code>
 * $search = new CloudsearchSearch($client);
 * $search->addIndex('my_indexname');
 * $search->search();
 * </code>
 *
 */
class CloudsearchSearch {

  /**
   * 设定搜索结果集升降排序的标志，"+"为升序，"-"为降序。
   *
   * @var string
   */
  const SORT_INCREASE = '+';
  const SORT_DECREASE = '-';

  const SEARCH_TYPE_SCAN = "scan";

  /**
   * 和API服务进行交互的对象。
   * @var CloudsearchClient
   */
  private $client;

  /**
   * 此次检索指定的应用名称。
   *
   * 可以指定单个应用名称，也可以指定多个应用名称结合。
   *
   * @var array
   */
  private $indexes = array();

  /**
   * 指定某些字段的一些summary展示规则。
   *
   * 这些字段必需为可分词的text类型的字段。
   *
   * 例如:
   * 指定title字段为： summary_field=>title
   * 指定title长度为50：summary_len=>50
   * 指定title飘红标签：summary_element=>em
   * 指定title省略符号：summary_ellipsis=>...
   * 指定summary缩略段落个数：summary_snipped=>1
   * 那么当前的字段值为：
   * <code>
   * array('title' => array(
   *   'summary_field' => 'title',
   *   'summary_len' => 50,
   *   'summary_element' => 'em',
   *   'summary_ellipsis' => '...',
   *   'summary_snipped' => 1,
   *   'summary_element_prefix' => 'em',
   *   'summary_element_postfix' => '/em')
   * );
   * </code>
   * @var array
   */
  private $summary = array();

  /**
   * config 子句。
   *
   * config子句只能接收三个参数（start, format, hit），其中：
   * start为当前结果集的偏移量；
   * format为当前返回结果的格式，有json，xml和protobuf三种格式；
   * hit为当前获取结果条数。
   *
   * 例如 "start:0,format:xml,hit:20"
   *
   * @var string
   */
  private $clauseConfig = '';

  /**
   * 返回的数据的格式，有json、xml，protobuf三种类型可选；默认为XML格式。
   * @var string
   */
  private $format = 'xml';

  /**
   * 设定返回结果集的offset，默认为0。
   * @var int
   */
  private $start = 0;

  /**
   * 设定返回结果集的个数，默认为20。
   * @var int
   */
  private $hits = 20;

  /**
   * 设定排序规则。
   * @var array
   */
  private $sort = array();

  /**
   * 设定过滤条件。
   * @var string
   */
  private $filter = '';

  /**
   * aggregate设定规则。
   * @var array
   */
  private $aggregate = array();

  /**
   * distinct 排序。
   * @var array
   */
  private $distinct = array();

  /**
   * 返回字段过滤。
   *
   * 如果设定了此字段，则只返回此字段里边的field。
   * @var array
   */
  private $fetches = array();

  /**
   * rerankSize表示参与精排算分的文档个数，一般不用使用默认值就能满足，不用设置,会自动使用默认值200
   * @var int
   */
  private $rerankSize = 200;

  /**
   * query 子句。
   *
   * query子句可以为query='鲜花'，也可以指定索引来搜索，例如：query=title:'鲜花'。
   * 详情请浏览setQueryString($query)方法。
   *
   * @var string
   */
  private $query;

  /**
   * 指定表达式名称，表达式名称和结构在网站中指定。
   *
   *
   * @var string
   */
  private $formulaName = '';

  /**
   * 指定粗排表达式名称，表达式名称和结构在网站中指定。
   * @var string
   */
  private $firstFormulaName = '';

  /**
   * 指定kvpairs子句的内容，内容为k1:v1,k2:v2的方式表示。
   * @var string
   */
  private $kvpair = '';

  /**
   * 指定qp 名称。
   * @var array
   */
  private $QPName = array();

  /**
   * 指定关闭的方法名称。
   * @var unknown
   */
  private $functions = array();

  /**
   * 设定自定义参数。
   *
   * 如果api有新功能（参数）发布，用户不想更新sdk版本，则可以自己来添加自定义的参数。
   *
   * @var string
   */
  private $customParams = array();

  private $scrollId = null;

  private $searchType = '';

  private $scroll = null;

  /**
   * 请求API的部分path。
   * @var string
   */
  private $path = '/search';

  /**
   * 构造函数
   *
   * @param CloudsearchClient $client 此对象由CloudsearchClient类实例化。
   */
  public function __construct($client) {
    $this->client = $client;
  }

  /**
   * 设置scroll扫描起始id
   *
   * @param scrollId 扫描起始id
   */
  public function setScrollId($scrollId) {
    $this->scrollId = $scrollId;
  }

  /**
   * 获取scroll扫描起始id
   *
   * @return string 扫描起始id
   */
  public function getScrollId() {
    return $this->scrollId;
  }

  /**
   * 请求scroll api。
   *
   * 类似search接口，但是不支持sort, aggregate, distinct, formula_name, summary及qp,
   * start 等功能。
   *
   * scroll实现方式：
   * 第一次正常带有指定的子句和参数调用scroll接口，此接口会返回scroll_id信息。
   * 第二次请求时只带此scroll_id信息和scroll参数即可。
   *
   * 类似第一次请求：
   * $search = new CloudsearchSearch($client);
   * $search->addIndex("juhuasuan");
   * $search->setQueryString("default:'酒店'");
   * $search->setFormat('json');
   * $search->setHits(10);
   * $search->setScroll("1m");
   * $result = $search->scroll();
   *
   * $array = json_decode($result, true);
   * $scrollId = $array['result']['scroll_id'];
   *
   * 第二次请求：
   * $search = new CloudsearchSearch($client);
   * $search->setScroll("1m");
   * $search->setScrollId($scrollId);
   * $result = $search->scroll();
   *
   * @param array $opts 扫描请求所需参数
   * @return string 扫描结果
   */
  public function scroll($opts = array()) {
    $this->extract($opts, "scroll");
    return $this->call('scroll');
  }

  /**
   * 执行搜索
   *
   * 执行向API提出搜索请求。
   * 更多说明请参见 [API 配置config子句]({{!api-reference/query-clause&config-clause!}})
   * @param array $opts 此参数如果被复制，则会把此参数的内容分别赋给相应的变量。此参数的值可能有以下内容：
   * @subparam string query 指定的搜索查询串，可以为query=>"索引名:'鲜花'"。
   * @subparam array indexes 指定的搜索应用，可以为一个应用，也可以多个应用查询。
   * @subparam array fetch_fields 设定返回的字段列表，如果只返回url和title，则为 array('url', 'title')。
   * @subparam string format 指定返回的数据格式，有json,xml和protobuf三种格式可选。默认值为：'xml'
   * @subparam string formula_name 指定的表达式名称，此名称需在网站中设定。
   * @subparam array summary 指定summary字段一些标红、省略、截断等规则。
   * @subparam int start 指定搜索结果集的偏移量。默认为0。
   * @subparam int hits 指定返回结果集的数量。默认为20。
   * @subparam array sort 指定排序规则。默认值为：'self::SORT_DECREASE' (降序)
   * @subparam string filter 指定通过某些条件过滤结果集。
   * @subparam array aggregate 指定统计类的信息。
   * @subparam array distinct 指定distinct排序。
   * @subparam string kvpair 指定的kvpair。
   *
   * @return string 返回搜索结果。
   *
   */
  public function search($opts = array()) {
    $this->extract($opts);
    return $this->call();
  }

  /**
   * 增加新的应用来进行检索
   * @param string\array $indexName 应用名称或应用名称列表.
   */
  public function addIndex($indexName) {
    if (is_array($indexName)) {
      $this->indexes = $indexName;
    } else {
      $this->indexes[] = $indexName;
    }
    $this->indexes = array_unique($this->indexes);
  }

  /**
   * 删除待搜索的应用
   *
   * 在当前检索中删除此应用的检索结果。
   * @param string $indexName 待删除的应用名称
   */
  public function removeIndex($indexName) {
    $flip = array_flip($this->indexes);
    unset($flip[$indexName]);
    $this->indexes = array_values(array_flip($flip));
  }

  /**
   * 获得请求应用列表
   *
   * 当前请求中所有的应用名列表。
   *
   * @return array 返回当前搜索的所有应用列表。
   */
  public function getSearchIndexes() {
    return $this->indexes;
  }

  /**
   * 设置表达式名称
   * 此表达式名称和结构需要在网站中已经设定。
   * @param string $formulaName 表达式名称。
   */
  public function setFormulaName($formulaName) {
    $this->formulaName = $formulaName;
  }

  /**
   * 获取表达式名称
   *
   * 获得当前请求中设置的表达式名称。
   *
   * @return string 返回当前设定的表达式名称。
   */
  public function getFormulaName() {
    return $this->formulaName;
  }

  /**
   * 清空精排表达式名称设置
   */
  public function clearFormulaName() {
    $this->formulaName = '';
  }

  /**
   * 设置粗排表达式名称
   *
   * 此表达式名称和结构需要在网站中已经设定。
   *
   * @param string $FormulaName 表达式名称。
   */
  public function setFirstFormulaName($formulaName) {
    $this->firstFormulaName = $formulaName;
  }

  /**
   * 获取粗排表达式设置
   *
   * 获取当前设置的粗排表达式名称。
   *
   * @return string 返回当前设定的表达式名称。
   */
  public function getFirstFormulaName() {
    return $this->firstFormulaName;
  }

  /**
   * 清空粗排表达式名称设置
   */
  public function clearFirstFormulaName() {
    $this->firstFormulaName = '';
  }

  /**
   * 添加一条summary信息
   * @param string $fieldName 指定的生效的字段。此字段必需为可分词的text类型的字段。
   * @param string $len 指定结果集返回的词字段的字节长度，一个汉字为2个字节。
   * @param string $element 指定命中的query的标红标签，可以为em等。
   * @param string $ellipsis 指定用什么符号来标注未展示完的数据，例如“...”。
   * @param string $snipped 指定query命中几段summary内容。
   * @param string $elementPrefix 如果指定了此参数，则标红的开始标签以此为准。
   * @param string $elementPostfix 如果指定了此参数，则标红的结束标签以此为准。
   */
  public function addSummary($fieldName, $len = 0, $element = '',
      $ellipsis = '', $snipped = 0, $elementPrefix = '', $elementPostfix = '') {
    if (empty($fieldName)) {
      return false;
    }

    $summary = array();
    $summary['summary_field'] = $fieldName;
    empty($len) || $summary['summary_len'] = (int) $len;
    empty($element) || $summary['summary_element'] = $element;
    empty($ellipsis) || $summary['summary_ellipsis'] = $ellipsis;
    empty($snipped) || $summary['summary_snipped'] = $snipped;
    empty($elementPrefix) || $summary['summary_element_prefix'] = $elementPrefix;
    empty($elementPostfix) || $summary['summary_element_postfix'] = $elementPostfix;

    $this->summary[$fieldName] = $summary;
  }

  /**
   * 获取当前的summary信息
   * 可以通过指定字段名称返回指定字段的summary信息
   *
   * @param string $field 指定的字段，如果此字段为空，则返回整个summary信息，否则返回指定field的summary信息。
   * @return array 返回summary信息。
   */
  public function getSummary($field = '') {
    return (!empty($field)) ? $this->summary[$field] : $this->summary;
  }

  /**
   * 获取summary字符串
   *
   * 把summary信息生成字符串并返回。
   *
   * @return string 返回字符串的summary信息。
   */
  public function getSummaryString() {
    $summary = array();
    if (is_array($s = $this->getSummary()) && !empty($s)) {
      foreach ($this->getSummary() as $summaryAttributes) {
        $item = array();
        if (is_array($summaryAttributes) && !empty($summaryAttributes)) {
          foreach ($summaryAttributes as $k => $v) {
            $item[] = $k . ":" . $v;
          }
        }
        $summary[] = implode(",", $item);
      }
    }
    return implode(";", $summary);
  }

  /**
   * 设置返回的数据格式
   *
   * @param string $format 数据格式名称，有xml, json和protobuf 三种类型。
   */
  public function setFormat($format) {
    $this->format = $format;
  }

  /**
   * 获取当前的数据格式名称
   *
   * @return string 返回当前的数据格式名称。
   */
  public function getFormat() {
    return $this->format;
  }

  /**
   * 设置返回结果的offset偏移量
   *
   * @param int $start 偏移量。
   */
  public function setStartHit($start) {
    $this->start = (int) $start;
  }

  /**
   * 获取返回结果的offset偏移量
   *
   * @return int 返回当前设定的偏移量。
   */
  public function getStartHit() {
    return $this->start;
  }

  /**
   * 设置结果集大小
   *
   * 设置当前返回结果集的doc个数。
   *
   * @param number $hits 指定的doc个数。默认值：20
   */
  public function setHits($hits = 20) {
    $this->hits = (int) $hits;
  }

  /**
   * 获取结果集大小
   *
   * 获取当前设定的结果集的doc数。
   *
   * @return number 返回当前指定的doc个数。
   */
  public function getHits() {
    return $this->hits;
  }

  /**
   * 添加排序设置
   *
   * 增加一个排序字段及排序方式。
   * 更多说明请参见[API 排序sort子句]({{!api-reference/query-clause&sort-clause!}})
   * @param string $field 字段名称。
   * @param string $sortChar 排序方式，有升序+和降序-两种方式。
   */
  public function addSort($field, $sortChar = self::SORT_DECREASE) {
    $this->sort[$field] = $sortChar;
  }

  /**
   * 删除指定字段的排序
   *
   * @param string $field 指定的字段名称。
   */
  public function removeSort($field) {
    unset($this->sort[$field]);
  }

  /**
   * 获取排序信息
   *
   * @param string $sortKey 如果此字段为空，则返回所有排序信息，否则只返回指定字段的排序值。
   * @return string\array 返回排序值。
   */
  public function getSort($sortKey = '') {
    if (!empty($sortKey)) {
      return $this->sort[$sortKey];
    } else {
      return $this->sort;
    }
  }

  /**
   * 获取排序字符串
   *
   * 把排序信息生成字符串并返回。
   *
   * @return string 返回字符串类型的排序规则。
   */
  public function getSortString() {
    $sort = $this->getSort();
    $sortString = array();
    if (is_array($sort) && !empty($sort)) {
      foreach ($sort as $k => $v) {
        $sortString[] = $v . $k;
      }
    }
    return implode(";", $sortString);
  }

  /**
   * 添加过滤规则
   *
   * 针对指定的字段添加过滤规则。
   * 更多说明请参见 [API 过滤filter子句]({{!api-reference/query-clause&filter-clause!}})
   *
   * @param string $filter 过滤规则，例如fieldName>=1。
   * @param string $operator 操作符，可以为 AND OR。默认值为：'AND'
   */
  public function addFilter($filter, $operator = 'AND') {
    if (empty($this->filter)) {
      $this->filter = $filter;
    } else {
      $this->filter .= " {$operator} {$filter}";
    }
  }

  /**
   * 获取过滤规则
   *
   * @return filter 返回字符串类型的过滤规则。
   */
  public function getFilter() {
    return $this->filter;
  }

  /**
   * 添加统计信息相关参数
   *
   * 一个关键词通常能命中数以万计的文档，用户不太可能浏览所有文档来获取信息。而用户感兴趣的可
   * 能是一些统计类的信息，比如，查询“手机”这个关键词，想知道每个卖家所有商品中的最高价格。
   * 则可以按照卖家的user_id分组，统计每个小组中最大的price值：
   * groupKey:user_id, aggFun: max(price)
   * 更多说明请参见 [APi aggregate子句说明]({{!api-reference/query-clause&aggregate-clause!}})
   *
   * @param string $groupKey 指定的group key.
   * @param string $aggFun 指定的function。当前支持：count、max、min、sum。
   * @param string $range 指定统计范围。
   * @param string $maxGroup 最大组个数。
   * @param string $aggFilter 表示仅统计满足特定条件的文档。
   * @param string $aggSamplerThresHold 抽样统计的阈值。表示该值之前的文档会依次统计，该值之后的文档会进行抽样统计。
   * @param string $aggSamplerStep 抽样统计的步长。
   */
  public function addAggregate($groupKey, $aggFun, $range = '', $maxGroup = '',
      $aggFilter = '', $aggSamplerThresHold = '', $aggSamplerStep = '') {
    if (empty($groupKey) || empty($aggFun)) {
      return false;
    }

    $aggregate = array();
    $aggregate['group_key'] = $groupKey;
    $aggregate['agg_fun'] = $aggFun;

    empty($range) || $aggregate['range'] = $range;
    empty($maxGroup) || $aggregate['max_group'] = $maxGroup;
    empty($aggFilter) || $aggregate['agg_filter'] = $aggFilter;
    empty($aggSamplerThresHold) ||
        $aggregate['agg_sampler_threshold'] = $aggSamplerThresHold;
    empty($aggSamplerStep) || $aggregate['agg_sampler_step'] = $aggSamplerStep;

    $this->aggregate[$groupKey][] = $aggregate;
  }

  /**
   * 删除指定的统计数据
   *
   * @param string $groupKey 指定的group key。
   */
  public function removeAggregate($groupKey) {
    unset($this->aggregate[$groupKey]);
  }

  /**
   * 获取统计相关信息
   *
   * @param string $groupKey 指定group key获取其相关信息，如果为空，则返回整个信息。
   * @return array 统计相关信息
   */
  public function getAggregate($key = '') {
    return (!empty($key)) ? $this->aggregate[$key] : $this->aggregate;
  }

  /**
   * 获取字符串类型的统计信息
   *
   * @return string 获取字符串类型的统计信息
   */
  public function getAggregateString() {
    $aggregate = array();
    if (is_array($agg = $this->getAggregate()) && !empty($agg)) {
      foreach ($agg as $aggDescs) {
        $item = array();
        if (is_array($aggDescs) && !empty($aggDescs)) {
          foreach ($aggDescs as $aggDesc) {
            foreach ($aggDesc as $itemKey => $itemValue) {
              $item[] = $itemKey . ":" . $itemValue;
            }
            $aggregate[] = implode(",", $item);
          }
        }

      }
    }
    return implode(";", $aggregate);
  }

  /**
   * 添加distinct排序信息
   *
   * 例如：检索关键词“手机”共获得10个结果，分别为：doc1，doc2，doc3，doc4，doc5，doc6，
   * doc7，doc8，doc9，doc10。其中前三个属于用户A，doc4-doc6属于用户B，剩余四个属于用户C。
   * 如果前端每页仅展示5个商品，则用户C将没有展示的机会。但是如果按照user_id进行抽取，每轮抽
   * 取1个，抽取2次，并保留抽取剩余的结果，则可以获得以下文档排列顺序：doc1、doc4、doc7、
   * doc2、doc5、doc8、doc3、doc6、doc9、doc10。可以看出，通过distinct排序，各个用户的
   * 商品都得到了展示机会，结果排序更趋于合理。
   * 更多说明请参见 [API distinct子句]({{!api-reference/query-clause&distinct-clause!}})
   *
   * @param string $key 为用户用于做distinct抽取的字段，该字段要求建立Attribute索引。
   * @param int $distCount 为一次抽取的document数量，默认值为1。
   * @param int $distTimes 为抽取的次数，默认值为1。
   * @param string $reserved 为是否保留抽取之后剩余的结果，true为保留，false则丢弃，丢弃时totalHits的个数会减去被distinct而丢弃的个数，但这个结果不一定准确，默认为true。
   * @param string $distFilter 为过滤条件，被过滤的doc不参与distinct，只在后面的 排序中，这些被过滤的doc将和被distinct出来的第一组doc一起参与排序。默认是全部参与distinct。
   * @param string $updateTotalHit 当reserved为false时，设置update_total_hit为true，则最终total_hit会减去被distinct丢弃的的数目（不一定准确），为false则不减；默认为false。
   * @param int $maxItemCount 设置计算distinct时最多保留的doc数目。
   * @param number $grade 指定档位划分阈值。
   */
  public function addDistinct($key, $distCount = 0, $distTimes = 0,
      $reserved = '', $distFilter = '', $updateTotalHit = '',
      $maxItemCount = 0, $grade = '') {

    if (empty($key)) {
      return false;
    }

    $distinct = array();
    $distinct['dist_key'] = $key;
    empty($distCount) || ($distinct['dist_count'] = (int) $distCount);
    empty($distTimes) || $distinct['dist_times'] = (int) $distTimes;
    empty($reserved) || $distinct['reserved'] = $reserved;
    empty($distFilter) || $distinct['dist_filter'] = $distFilter;
    empty($updateTotalHit) || $distinct['update_total_hit'] = $updateTotalHit;
    empty($maxItemCount) || $distinct['max_item_count'] = (int) $maxItemCount;
    empty($grade) || $distinct['grade'] = $grade;

    $this->distinct[$key] = $distinct;
  }

  /**
   * 删除某个字段的所有distinct排序信息
   *
   * @param string $distinctKey 指定的字段
   */
  public function removeDistinct($distinctKey) {
    unset($this->distinct[$distinctKey]);
  }

  /**
   * 获取某字段的distinct排序信息
   *
   * @param string $key 指定的distinct字段，如果字段为空则返回所有distinct信息。
   * @return array 指定字段的distinct排序信息
   */
  public function getDistinct($key = '') {
    return (!empty($key)) ? $this->distinct[$key] : $this->distinct;
  }

  /**
   * 获取字符串类型的所有的distinct信息
   * @return string 字符串类型的所有的distinct信息
   */
  public function getDistinctString() {
    $distinct = array();
    if (is_array($s = $this->getDistinct()) && !empty($s)) {
      foreach ($s as $distinctAttribute) {
        $item = array();
        if ($distinctAttribute['dist_key'] != 'none_dist') {
          if (is_array($distinctAttribute) && !empty($distinctAttribute)) {
            foreach ($distinctAttribute as $k => $v) {
              $item[] = $k . ":" . $v;
            }
          }
          $distinct[] = implode(",", $item);
        } else {
          $distinct[] = $distinctAttribute['dist_key'];
        }
      }
    }
    return implode(";", $distinct);
  }

  /**
   * 设定指定索引字段范围的搜索关键词
   *
   * [NOTE]:$query必须指定索引名称，格式类似为 索引名称:'搜索关键词'。
   *
   * 此query是查询必需的一部分，可以指定不同的索引名，并同时可指定多个查询及之间的关系
   * （AND, OR, ANDNOT, RANK）。
   *
   * 例如查询subject索引字段的query:“手机”，可以设置为
   * query=subject:'手机'。
   *
   * NOTE: text类型索引在建立时做了分词，而string类型的索引则没有分词
   * 更多说明请参见 [API query子句]({{!api-reference/query-clause&query-clause!}})
   *
   * @param string $query 设定搜索的查询词。
   * @param string $fieldName 设定的索引范围。
   *
   */
  public function setQueryString($query) {
    $this->query = $query;
  }

  /**
   * 获取当前指定的查询词内容
   *
   * @return string 当前指定的查询词内容
   */
  public function getQuery() {
    return $this->query;
  }

  /**
   * 添加指定结果集返回的字段
   *
   * @param array\string $field 结果集返回的字段。
   */
  public function addFetchFields($field) {
    if (!is_array($field)) {
      if (!in_array($field, $this->fetches)) {
        $this->fetches[] = $field;
      }
    } else {
      $this->fetches = $field;
    }
  }

  /**
   * 删除指定结果集的返回字段
   *
   * @param string $fieldName 指定字段名称。
   */
  public function removeFetchField($fieldName) {
    $flip = array_flip($this->fetches);
    unset($flip[$fieldName]);
    $this->fetches = array_flip($flip);
  }

  /**
   * 设置kvpair
   * 更多说明请参见 [API 自定义kvpair子句]({{!api-reference/query-clause&kvpair-clause!}})
   *
   * @param string $pair 指定的pair信息。
   */
  public function setPair($pair) {
    $this->kvpair = $pair;
  }

  /**
   * 获取当前的kvpair
   *
   * @return string 返回当前设定的kvpair。
   */
  public function getPair() {
    return $this->kvpair;
  }

  /**
   * 增加自定义参数
   *
   * @param string $paramKey 参数名称。
   * @param string $paramValue 参数值。
   */
  public function addCustomParam($paramKey, $paramValue) {
    $this->customParams[$paramKey] = $paramValue;
  }

  /**
   * 指定精排算分的文档个数
   *
   * 若不指定则使用默认值200
   *
   * @param int $rerankSize 精排算分文档个数
   */
  public function addRerankSize($rerankSize) {
    $this->rerankSize = $rerankSize;
  }

  /**
   * 添加一条查询分析规则
   * 
   * @param QPName 查询分析规则
   */
  public function addQPName($QPName) {
    if (is_array($QPName)) {
      $this->QPName = $QPName;
    } else {
      $this->QPName[] = $QPName;
    }
  }

  /**
   * 获取设置的查询分析规则
   *
   * @return String 设置的查询分析规则
   */
  public function getQPName() {
    return $this->QPName;
  }

  /**
   * 关闭某些功能模块。
   *
   * 有如下场景需要考虑：
   * 1、如果要关闭整个qp的功能，则参数为空即可。
   * 2、要指定某个索引关闭某个功能，则可以指定disableValue="processer:index",
   * processer:index为指定关闭某个processer的某个索引功能，其中index为索引名称，多个索引可以用“|”分隔，可以为index1[|index2...]
   * 3、如果要关闭多个processor可以传递数组。
   * qp processor 有如下模块：
   * 1、spell_check: 检查用户查询串中的拼写错误，并给出纠错建议。
   * 2、term_weighting: 分析查询中每个词的重要程度，并将其量化成权重，权重较低的词可能不会参与召回。
   * 3、stop_word: 根据系统内置的停用词典过滤查询中无意义的词
   * 4、synonym: 根据系统提供的通用同义词库和语义模型，对查询串进行同义词扩展，以便扩大召回。
   * example:
   * "" 表示关闭整个qp。
   * "spell_check" 表示关闭qp的拼音纠错功能。
   * "stop_word:index1|index2" 表示关闭qp中索引名为index1和index2上的停用词功能。
   *
   * @param string $functionName 指定的functionName，例如“qp”等
   * @param string|array $disableValue 需要关闭的值
   */
  public function addDisabledQP($disableValue = "") {
    $this->addDisabledFunction("qp", $disableValue);
  }

  /**
   * 添加一项禁止的功能模块
   *
   * @param functionName 功能模块名称
   * @param disableValue 禁用的功能细节
   */
  public function addDisabledFunction($functionName, $disableValue = "") {
    if (is_array($disableValue)) {
      $this->functions[$functionName] = $disableValue;
    } else {
      $this->functions[$functionName][] = $disableValue;
    }
  }

/**
 * 获取所有禁止的功能模块
 * 
 * @return array 所哟禁止的功能模块
 */
  public function getDisabledFunction() {
    return $this->functions;
  }

  /**
   * 以字符串的格式返回disable的内容。
   *
   * @return string
   */
  public function getDisabledFunctionString() {
    $functions = $this->getDisabledFunction();
    $result = array();
    if (!empty($functions)) {
      foreach ($functions as $functionName => $value) {
        $string = "";
        if (is_array($value) && !empty($value)) {
          $string = implode(",", $value);
        }

        if ($string === "") {
          $result[] = $functionName;
        } else {
          $result[] = $functionName . ":" . $string;
        }
      }
    }

    return implode(";", $result);
  }

  /**
   * 获取精排算分文档个数
   *
   * @return int 精排算分文档个数
   */
  public function getRerankSize() {
    return $this->rerankSize;

  }

  /**
   * 获取自定义参数
   *
   * @return string 自定义参数
   */
  public function getCustomParam() {
    return $this->customParams;
  }

  /**
   * 获取指定结果集返回的字段列表
   *
   * @return array 指定结果集返回的字段列表
   */
  public function getFetchFields() {
    return $this->fetches;
  }

  /**
   * 设置此次获取的scroll id的期时间。
   *
   * 可以为整形数字，默认为毫秒。也可以用1m表示1min；支持的时间单位包括：
   * w=Week, d=Day, h=Hour, m=minute, s=second
   *
   * @param string|int $scroll
   */
  public function setScroll($scroll) {
    $this->scroll = $scroll;
  }

  /**
   * 获取scroll的失效时间。
   *
   * @return string|int
   */
  public function getScroll() {
    return $this->scroll;
  }

  /**
   * 设置搜索类型
   *
   * @param searchType 搜索类型
   */
  private function setSearchType($searchType) {
    $this->searchType = $searchType;
  }

  /**
   * 获取设置的搜索类型
   *
   * @return String 设置的搜索类型
   */
  private function getSearchType() {
    return $this->searchType;
  }

  /**
   * 从$opts数组中抽取所有的需要的参数并复制到属性中。
   *
   * @param array $opts
   */
  private function extract($opts, $type = 'search') {
    if (!empty($opts) && is_array($opts)) {
      isset($opts['query']) && $this->setQueryString($opts['query']);
      isset($opts['indexes']) && $this->addIndex($opts['indexes']);
      isset($opts['fetch_field']) && $this->addFetchFields($opts['fetch_field']);
      isset($opts['format']) && $this->setFormat($opts['format']);
      isset($opts['start']) && $this->setStartHit($opts['start']);
      isset($opts['hits']) && $this->setHits((int) $opts['hits']);
      isset($opts['filter']) && $this->addFilter($opts['filter']);
      isset($opts['kvpair']) && $this->setPair($opts['kvpair']);
      isset($opts['rerankSize']) && $this->addRerankSize($opts['rerankSize']);

      if ($type == 'search') {
        isset($opts['sort']) && $this->sort = $opts['sort'];
        isset($opts['aggregate']) && $this->aggregate = $opts['aggregate'];
        isset($opts['distinct']) && $this->distinct = $opts['distinct'];
        isset($opts['formula_name']) && $this->setFormulaName($opts['formula_name']);
        isset($opts['summary']) && $this->summary = $opts['summary'];
        isset($opts['qp']) && $this->addQPName($opts['qp']);
        isset($opts['disable_qp']) && $this->addDisabledQP($opts['disable']);
      } else if ($type == 'scroll') {
        isset($opts['scroll_id']) && $this->setScrollId($opts['scroll_id']);
        isset($opts['scroll']) && $this->setScroll($opts['scroll']);
        $this->setSearchType(self::SEARCH_TYPE_SCAN);
      }
    }
  }

  /**
   * 生成HTTP的请求串，并通过CloudsearchClient类向API服务发出请求并返回结果。
   *
   * query参数中的query子句和config子句必需的，其它子句可选。
   *
   * @return string
   */
  private function call($type = 'search') {
    $haquery = array();
    $haquery[] = "config=" . $this->clauseConfig();
    $haquery[] = "query=" . ($this->getQuery() ? $this->getQuery() : "''") . "";

    ($f = $this->getFilter()) && ($haquery[] = 'filter=' . $f);
    ($k = $this->getPair()) && ($haquery[] = 'kvpairs=' . $k);
    if ($type == 'search') {
      ($s = $this->getSortString()) && ($haquery[] = "sort=" . $s);
      ($d = $this->getDistinctString()) && ($haquery[] = 'distinct=' . $d);
      ($a = $this->getAggregateString()) && ($haquery[] = 'aggregate=' . $a);
    }

    $params = array(
        'query' => implode("&&", $haquery),
        'index_name' => implode(";", $this->getSearchIndexes()),
        'format' => $this->getFormat()
    );

    if ($result = $this->getCustomParam()) {
      foreach ($result as $k => $v) {
        $params[$k] = $v;
      }
    }

    ($f = $this->getFetchFields()) && ($params['fetch_fields'] = implode(";", $f));
    if ($type == 'search') {
      ($f = $this->getFormulaName()) && ($params['formula_name'] = $f);
      ($f = $this->getFirstFormulaName()) && ($params['first_formula_name'] = $f);
      ($s = $this->getSummaryString()) && ($params['summary'] = $s);
      ($f = $this->getQPName()) && ($params['qp'] = implode(",", $f));
      ($f = $this->getDisabledFunctionString()) && ($params['disable'] = $f);
    } else if ($type == 'scroll') {
      ($f = $this->getScroll()) && ($params['scroll'] = $f);
      ($f = $this->getScrollId()) && ($params['scroll_id'] = $f);
      $params['search_type'] = self::SEARCH_TYPE_SCAN;
    }

    return $this->client->call($this->path, $params, 'GET');
  }

  /**
   * 生成语法的config子句并返回。
   * @return string
   */
  private function clauseConfig() {
    $config = array();
    $config[] = 'format:' . $this->getFormat();
    $config[] = 'start:' . $this->getStartHit();
    $config[] = 'hit:' . $this->getHits();
    ($r = $this->getRerankSize()) && ($config[] = 'rerank_size:' . $r);

    return implode(",", $config);
  }
}