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
 * opensearch应用接口.
 *
 * 主要功能、创建应用、查看应用内容、删除应用和修改应用名称。
 *
 */
class CloudsearchIndex {

  const STATUS_OK = 'OK';
  const STATUS_FAIL = 'FAIL';
  /**
   * 应用名称。
   * @var string
   */
  private $indexName;
  
  /**
   * CloudsearchClient实例。
   * @var CloudsearchClient
   */
  private $client;

  /**
   * 请求的API的URI。
   * @var string
   */
  private $path;

 /**
   * 构造函数
   * @param string $indexName 指定操作的应用名称。
   * @param CloudsearchClient $client cloudsearch客户端
   */
  public function __construct($indexName, $client) {
    $this->client = $client;
    $this->indexName = $indexName;
    $this->_setPath($indexName);
  }

  /**
   * 通过模板名称创建应用
   *
   * 用指定的模板名称创建一个新的应用。
   * @param string $templateName 模板名称(可以使系统内置模板，也可以是自定义模板)
   * @param array $opts 包含应用的备注信息。
   *
   * @return string 返回api返回的结果。
   */
  public function createByTemplateName($templateName, $opts = array()) {
    $params = array(
        'action' => "create",
        'template' => $templateName,
    );

    if (isset($opts['desc']) && !empty($opts['desc'])) {
      $params['index_des'] = $opts['desc'];
    }

    return $this->client->call($this->path, $params);
  }

   /**
   * 通过模板创建应用
   *
   * 用指定的模板创建一个新的应用。模版是一个格式化数组,用于描述应用的结构，可以在控制台中通过创建应用->保存模板->导出模板来获得json结构的模板；也可以自己生成，格式见控制台模板管理。
   * @param string $template 使用的模板
   * @param array $opts 包含应用的备注信息。
   *
   * @return string 返回api返回的正确或错误的结果。
   */
  public function createByTemplate($template,$opts = array()) {
    $params = array(
        'action' => "create",
        'template' => $template,
    );

    if (isset($opts['desc']) && !empty($opts['desc'])) {
      $params['index_des'] = $opts['desc'];
    }

    $params['template_type'] = 2;

    return $this->client->call($this->path, $params);
  }

  /**
   * 修改应用名称和备注
   *
   * 更新当前应用的应用名称和备注信息。
   * @param string $toIndexName 更改后的新名字
   * @param array $opts 可选参数,包含： desc 应用备注信息
   * @return string API返回的操作结果
   */
  public function rename($toIndexName, $opts = array()) {
    $params = array(
        'action' => "update",
        'new_index_name' => $toIndexName
    );

    if (isset($opts['desc']) && !empty($opts['desc'])) {
      $params['description'] = $opts['desc'];
    }

    $result = $this->client->call($this->path, $params);
    $json = json_decode($result, true);
    if (isset($json['status']) && $json['status'] == 'OK') {
      $this->indexName = $toIndexName;
      $this->_setPath($toIndexName);
    }
    return $result;
  }

  private function _setPath($indexName) {
    $this->path = '/index/' . $indexName;
  }

  /**
   * 删除应用
   *
   * @return string API返回的操作结果
   */
  public function delete() {
    return $this->client->call($this->path, array('action' => "delete"));
  }


  /**
   * 查看应用状态
   *
   * @return string API返回的操作结果
   */
  public function status() {
    return $this->client->call($this->path, array('action' => "status"));
  }

  /**
   * 列出所有应用
   *
   * @param int $page 页码
   * @param int $pageSize 每页的记录条数
   */
  public function listIndexes($page = 1, $pageSize = 10) {
    $params = array(
        'page' => $page,
        'page_size'  => $pageSize,
    );
    return $this->client->call('/index', $params);
  }

  /**
   * 获取应用名称
   *
   * 获取当前应用的名称。
   *
   * @return string 当前应用的名称
   */
  public function getIndexName() {
    return $this->indexName;
  }

  /**
   * 获取应用的最近错误列表
   *
   * @param int $page 指定获取第几页的错误信息。默认值：1
   * @param int $pageSize 指定每页显示的错误条数。默认值：10
   *
   * @return array 返回指定页数的错误信息列表。
   */
  public function getErrorMessage($page = 1, $pageSize = 10) {
    $this->_checkPageClause($page);
    $this->_checkPageSizeClause($pageSize);

    $params = array(
        'page' => $page,
        'page_size' => $pageSize
    );
    return $this->client->call('/index/error/' . $this->indexName, $params);
  }

  /**
   * 检查$page参数是否合法。
   *
   * @param int $page 指定的页码。
   *
   * @throws Exception 如果参数不正确，则抛出此异常。
   *
   * @access private
   */
  private function _checkPageClause($page) {
    if (NULL == $page || !is_int($page)) {
      throw new Exception('$page is not an integer.');
    }
    if ($page <= 0) {
      throw new Exception('$page is not greater than or equal to 0.');
    }
  }

  /**
   * 检查$pageSize参数是否合法。
   *
   * @param int $pageSize 每页显示的记录条数。
   *
   * @throws Exception 参数不合法
   *
   * @access private
   */
  private function _checkPageSizeClause($pageSize) {
    if (NULL == $pageSize || !is_int($pageSize)) {
      throw new Exception('$pageSize is not an integer.');
    }
    if ($pageSize <= 0) {
      throw new Exception('$pageSize is not greater than 0.');
    }
  }
}
