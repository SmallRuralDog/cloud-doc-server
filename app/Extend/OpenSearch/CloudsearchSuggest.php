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
 * opensearch 下拉提示搜索接口。
 *
 * 用户需要在控制台中配置好下拉提示，并且已经生效，才有可能通过此接口获取结果。
 *
 * example：
 * <code>
 * $suggest = new CloudsearchSuggest($client);
 * $suggest->setIndexName("index_name");
 * $suggest->setSuggestName("suggest_name");
 * $suggest->setHits(10);
 * $suggest->setQuery($query);
 *
 * echo $suggest->search();
 * </code>
 *
 * 或
 *
 * <code>
 * $suggest = new CloudsearchSuggest($client);
 *
 * $opts = array(
 *     "index_name" => "index_name",
 *     "suggest_name" => "suggest_name",
 *     "hit" => 10,
 *     "query" => "query"
 * );
 *
 * echo $suggest->search($opts);
 * </code>
 *
 */
class CloudsearchSuggest {

  private $client = null;

  private $indexName = null;

  private $suggestName = null;

  private $hits = 10;

  private $query = null;
  
  private $path = "/suggest";

  public function __construct($client) {
    $this->client = $client;
  }

  /**
   * 设定下拉提示对应的应用名称
   *
   * @param string $indexName 指定的应用名称
   */
  public function setIndexName($indexName) {
    $this->indexName = $indexName;
  }

  /**
   * 获取下拉提示对应的应用名称
   *
   * @return string 返回应用名称
   */
  public function getIndexName() {
    return $this->indexName;
  }

  /**
   * 设定下拉提示名称
   *
   * @param string $suggestName 指定的下拉提示名称。
   */
  public function setSuggestName($suggestName) {
    $this->suggestName = $suggestName;
  }

  /**
   * 获取下拉提示名称
   *
   * @return string 返回下拉提示名称。
   */
  public function getSuggestName() {
    return $this->suggestName;
  }

  /**
   * 设定返回结果条数
   *
   * @param int $hits 返回结果的条数。
   */
  public function setHits($hits) {
    $hits = (int) $hits;
    if ($hits < 0) {
        $hits = 0;
    }
    $this->hits = $hits;
  }

  /**
   * 获取返回结果条数
   *
   * @return int 返回条数。
   */
  public function getHits() {
    return $this->hits;
  }

  /**
   * 设定要查询的关键词
   *
   * @param string $query 要查询的关键词。
   */
  public function setQuery($query) {
    $this->query = $query;
  }

  /**
   * 获取要查询的关键词
   *
   * @return string 返回要查询的关键词。
   */
  public function getQuery() {
    return $this->query;
  }

  /**
   * 发出查询请求
   *
   * @param array $opts options参数列表
   * @subparam             index_name 应用名称
   * @subparam             suggest_name 下拉提示名称
   * @subparam             hits 返回结果条数
   * @subparam  		   query 查询关键词
   * @return string 返回api返回的结果。
   */
  public function search($opts = array()) {
    if (!empty($opts)) {
      if (isset($opts['index_name']) && $opts['index_name'] !== '') {
        $this->setIndexName($opts['index_name']);
      }

      if (isset($opts['suggest_name']) && $opts['suggest_name'] !== '') {
        $this->setSuggestName($opts['suggest_name']);
      }

      if (isset($opts['hits']) && $opts['hits'] !== '') {
        $this->setHits($opts['hits']);
      }

      if (isset($opts['query']) && $opts['query'] !== '') {
        $this->setQuery($opts['query']);
      }
    }

    $params = array(
        "index_name" => $this->getIndexName(),
        "suggest_name" => $this->getSuggestName(),
        "hit" => $this->getHits(),
        "query" => $this->getQuery()
    );

    return $this->client->call($this->path, $params);
  }
}
