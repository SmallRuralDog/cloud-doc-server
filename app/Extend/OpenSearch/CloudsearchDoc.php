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
 * opensearch 文档接口。
 *
 * 此接口负责添加文档、更新文档、删除文档、获取指定文档状态和导入一个文档文件。
 *
 */
class CloudsearchDoc {

  const METHOD = 'POST';

  const DOC_ADD = 'add';
  const DOC_REMOVE = 'delete';
  const DOC_UPDATE = 'update';

  /**
   * push数据时API返回的正确的状态值。
   * @var string
   */
  const PUSH_RETURN_STATUS_OK = 'OK';

  /**
   * push数据时验证签名的方式。
   *
   * 如果此常量为1，且生成签名的query string中包含了items字段，则计算签名的时候items字段
   * 将不被包含在内。否则，所有的字段将都要被计算签名。
   *
   * @var int
   */
  const SIGN_MODE = 1;

  const CSV_SEPARATOR = ',';


  /**
   * 在切割一个大数据块后push数据的频率。默认 5次/s。
   * @var int
   */
  const PUSH_FREQUENCE = 4;

  /**
   * POST一个文件，进行切割时的单请求的最大size。单位：MB。
   * @var number
   */
  const PUSH_MAX_SIZE = 4;

  /**
   * Ha3Doc文件doc分割符。
   * @var string
   */
  const HA_DOC_ITEM_SEPARATOR = "\x1e\n";

  /**
   * Ha3Doc文件字段分割符
   * @var string
   */
  const HA_DOC_FIELD_SEPARATOR = "\x1F\n";

  /**
   * Ha3Doc文件字段多值分割符。
   * @var string
   */
  const HA_DOC_MULTI_VALUE_SEPARATOR = "\x1D";

  /**
   * section weight标志符。
   * @var string
   */
  const HA_DOC_SECTION_WEIGHT = "\x1C";

  /**
   * 索引名称。
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
   * @param string $indexName 指定操作的索引名称。
   * @param CloudsearchClient $client cloudsearch客户端
   * @param array $opts 可选参数
   */
  public function __construct($indexName, $client, $opts = array()) {
    $this->indexName = $indexName;
    $this->client = $client;
    $this->path = '/index/doc/' . $this->indexName;
  }


  /**
   * 查看文档
   *
   * 根据文档id获取doc的详细信息。
   *
   * @param string $docId 指定的文档id。
   * @return string 该docId对应的doc详细信息
   */
  public function detail($docId) {
    $item = array("id" => $docId);
    return $this->client->call($this->path, $item, self::METHOD);
  }


  /**
   * 更新文档
   *
   * 向指定的表中更新doc。
   * @param array $docs 指定要更新的doc。
   * @param string $tableName 指定向哪个表中更新doc。
   * @return string 返回API返回的结果。
   */
  public function update($docs, $tableName) {
    return $this->action($docs, $tableName, self::SIGN_MODE);
  }


  /**
   * 添加文档
   *
   * 向指定的表中增加doc。
   * @param array $docs 指定要添加的doc。
   * @param string $tableName 指定向哪个表中增加doc。
   * @return string 返回API返回的结果。
   */
  public function add($docs, $tableName) {
    return $this->action($docs, $tableName, self::SIGN_MODE);
  }


  /**
   * 删除文档
   *
   * 删除指定表中的doc。
   * @param array $docs 指定要删除的doc列表，必须含有主键。
   * @param string $tableName 指定要从哪个表删除记录。
   * @return string 返回API返回的结果。
   */
  public function remove($docs, $tableName) {
    return $this->action($docs, $tableName, self::SIGN_MODE);
  }


  /**
   * 执行文档相关操作
   *
   * @param array|string $docs 此docs为用户push的数据，此字段为json_encode的字符串或者数据。
   * @param string $tableName 操作的表名。
   * @throws Exception
   * @return string 请求API并返回相应的结果。
   */
  private function action($docs, $tableName, $signMode = self::SIGN_MODE) {
    if (!is_array($docs)) {
      $docs = json_decode($docs, true);
    }

    if (!is_array($docs) || empty($docs) || !is_array($docs[0])) {
      throw new Exception('Operation failed. The docs is not correct.');
    }

    $params = array(
        'action' => "push",
        'items' => json_encode($docs),
        'table_name' => $tableName
    );

    if ($signMode == self::SIGN_MODE) {
      $params['sign_mode'] = self::SIGN_MODE;
    }
    return $this->client->call($this->path, $params, self::METHOD);
  }


  /**
   * 重新生成doc文档。
   * @param array $docs doc文档
   * @param string $type 操作类型，有ADD、UPDATE、REMOVE。
   * @return array 返回重新生成的doc文档。
   */
  private function generate($docs, $type) {
    $result = array();
    foreach ($docs as $doc) {
      $item = array('cmd' => $type);
      $item['fields'] = $doc;
      $result[] = $item;
    }

    return $result;
  }

  /**
   * 通过csv格式文件上传文档数据
   *
   * NOTE: 此文件必需为csv格式的文件（“，”分割）；且第一行为数据结构字段名称，例如：
   *
   * id, title, name, date，1, "我的测试数据\"1\"测试1", test_name1, "2013-09-21 00:12:22"
   * ...
   *
   * @param string $fileName 本地文件。
   * @param string $primaryKey 指定此表的主键。
   * @param string $tableName 指定表名。
   * @param array $multiValue 指定此表中的多值的字段。默认值为空
   * @param int $offset 指定从第offset条记录开始导入。默认值为1
   * @param number $maxSize 指定每次push数据的最大值，单位为MB。默认值为4
   * @param int $frequence 指定上传数据的频率，默认值为4，单位为次/秒
   *
   * @return string 返回如果成功上传或上传失败的状态。
   */
  public function pushCSVFile($fileName, $primaryKey, $tableName,
      $multiValue = array(), $offset = 1, $maxSize = self::PUSH_MAX_SIZE,
      $frequence = self::PUSH_FREQUENCE) {
    $reader = $this->_connect($fileName);

    $lineNo = 0;
    $buffter = array();
    $latestLine = $offset - 1;
    $latestPrimaryKey = '';
    $totalSize = 0;
    $primaryKeyPos = 0;

    $time = time();
    $timeFreq = 0;

    while ($data = fgetcsv($reader, 1024, self::CSV_SEPARATOR)) {
      if ($lineNo == 0) {
        $header = $data;
        if (count(array_flip($data)) != count($data)) {
          throw new Exception('There are some multi fields in your header.');
        }

        $primaryKeyPos = array_search($primaryKey, $header);
        if (false === $primaryKey) {
          throw new Exception("The primary key '{$primaryKey}' is not exists.");
        }
      } else {
        if ($lineNo < $offset) {
          continue;
        }

        if (count($data) != count($header)) {
          throw new Exception("The number of columns of values is not matched
              the number of header of primary key '{$data[$primaryKeyPos]}'.
              Latest successful posted primary key number is '{$latestPrimaryKey}'.");
        }


        $item = array();
        $item['cmd'] = self::DOC_ADD;
        if (!empty($multiValue)) {
          foreach ($multiValue as $field => $separator) {
            $pos = array_search($field, $header);
            if ($pos !== false) {
              $data[$pos] = explode($separator, $data[$pos]);
            }
          }
        }
        $item['fields'] = array_combine($header, $data);

        $json = json_encode($item);
        // 检测是否push数据push成功。
        $currentSize = strlen(urlencode($json));
        if ($currentSize + $totalSize >= self::PUSH_MAX_SIZE * 1024 * 1024) {
          $txt = $this->add($buffer, $tableName);
          $return = json_decode($txt, true);
          if ('OK' != $return['status']) {
            throw new Exception("Api returns error: " . $txt .
            ". Latest successful posted primary key is {$latestPrimaryKey}.");
          } else {
            // 计算每秒钟的push的频率并如果超过频率则sleep。
            $newTime = microtime(true);
            $timeFreq ++;

            if (floor($newTime) == $time && $timeFreq >= self::PUSH_FREQUENCE) {
              usleep((floor($newTime) + 1 - $newTime) * 1000000);
              $timeFreq = 0;
            }

            $newTime = floor(microtime(true));
            if ($time != $newTime) {
              $time = $newTime;
              $timeFreq = 0;
            }

            if (is_array($buffer) && !empty($buffer)) {
              $last = count($buffer) - 1;
              $latestPrimaryKey = $buffer[$last][$primaryKeyPos];
            } else {
              $latestPrimaryKey = 0;
            }
          }
          $buffer = array();
          $totalSize = 0;
        }
        $buffer[] = $item;
        $totalSize += $currentSize;
      }

      $lineNo ++;
    }

    if (!empty($buffer)) {
      $return = json_decode($this->add($buffer, $tableName), true);
      if (self::PUSH_RETURN_STATUS_OK != $return['status']) {
        throw new Exception($return['errors'][0]['message'] .
            ". Latest successful posted line number is {$latestLine}.");
      }
    }

    return 'The data is posted successfully.';
  }

  /**
   * 推送HA3格式文档
   *
   * 除了上面的方法还可以通过文件将文档导入到指定的表中
   * 这里的文档需满足一定的格式，我们称之为HA3文档格式。HA3文件的要求如下：
   *
   * 文件编码：UTF-8
   *
   * 支持CMD: add, delete,update。
   * 如果给出的字段不是全部，add会在未给出的字段加默认值，覆盖原值；update只会更新给出的字段，未给出的不变。
   *
   * 文件分隔符：
   * <pre>
   *
   * 编码-------描述--------------------显示形态
   * "\x1E\n"   每个doc的分隔符.        ^^(接换行符)
   * "\x1F\n"   每个字段key和value分隔  ^_(接换行符)
   * "\x1D"     多值字段的分隔符        ^]
   * </pre>;
   *
   * 示例：
   *
   * <pre>;
   * CMD=add^_
   * url=http://www.opensearch.console.aliyun.com^_
   * title=开放搜索^_
   * body=xxxxx_xxxx^_
   * multi_value_feild=123^]1234^]12345^_
   * ^^
   * CMD=update^_
   * ...
   * </pre>
   *
   * 注意：文件结尾的分隔符也必需为"^^\n"，最后一个换行符不能省略。
   *
   * @param string $fileName 指定HA3DOC所有在的路径。
   * @param string $tableName 指定要导入的表的名称。
   * @param int $offset 指定偏移行数，如果非0，则从当前行一下的数据开始导入。默认值为：1
   * @param number $maxSize 指定每次导入到api接口的数据量的大小，单位MB，默认值为：4
   * @param int $frequence 指定每秒钟导入的频率，单位次/秒，默认值为：4
   * @throws Exception 如果在导入的过程中由于字段问题或接口问题则抛出异常。
   * @return string 返回导入成功标志。
   */
  public function pushHADocFile($fileName, $tableName, $offset = 1,
      $maxSize = self::PUSH_MAX_SIZE, $frequence = self::PUSH_FREQUENCE) {
    $reader = $this->_connect($fileName);

    // 默认doc初始结构。
    $doc = array('cmd' => '', 'fields' => array());

    // 当前行号，用来记录当前已经解析到了第多少行。
    $lineNumber = 1;

    // 最新成功push数据的行号，用于如果在重新上传的时候设定offset偏移行号。
    $lastLineNumber = 0;

    // 最后更新的doc中的字段名，如果此行没有字段结束符，则下行的数据会被添加到这行的字段上。
    // 有一些富文本，在当前行没有结束此字段，则要记录最后的字段名称。
    // 例如：
    // rich_text=鲜花
    // 礼品专卖店^_
    // other_field=xxx^_
    $lastField = '';

    // 当前还未上传的文档的大小。单位MB.
    $totalSize = 0;

    // 当前秒次已经发了多少次请求，用于限流。
    $timeFreq = 0;

    $time = time();

    $buffer = array();

    // 开始遍历文件。
    try {
      while($line = fgets($reader)) {

        // 如果当前的行号小于设定的offset行号时跳过。
        if ($lineNumber < $offset) {
          continue;
        }

        // 获取结果当前行的最后两个字符。
        $separator = substr($line, -2);

        // 如果当前结束符是文档的结束符^^\n，则当前doc解析结束。并计算buffer+当前doc文档的
        // 大小，如果大于指定的文档大小，则push buffer到api，并清空buffer，同时把当前doc
        // 文档扔到buffer中。
        if ($separator == self::HA_DOC_ITEM_SEPARATOR) {

          $lastField = '';

          // 获取当前文档生成json并urlencode之后的size大小。
          $json = json_encode($doc);
          $currentSize = strlen(urlencode($json));

          // 如果计算的大小+buffer的大小大于等于限定的阀值self::PUSH_MAX_SIZE，则push
          // buffer数据。
          if ($currentSize + $totalSize >= self::PUSH_MAX_SIZE * 1024 * 1024) {

            // push 数据到api。
            $return = json_decode($this->add($buffer, $tableName), true);
            // 如果push不成功则抛出异常。
            if ('OK' != $return['status']) {
              throw new Exception("Api returns error. " . $return['errors'][0]['message']);
            } else {
              // 如果push成功，则计算每秒钟的push的频率并如果超过频率则sleep。
              $lastLineNumber = $lineNumber;
              $newTime = microtime(true);
              $timeFreq ++;

              // 如果时间为上次的push时间且push频率超过设定的频率，则unsleep 剩余的毫秒数。
              if (floor($newTime) == $time && $timeFreq >= self::PUSH_FREQUENCE) {
                usleep((floor($newTime) + 1 - $newTime) * 1000000);
                $timeFreq = 0;
              }
              // 重新设定时间和频率。
              $newTime = floor(microtime(true));
              if ($time != $newTime) {
                $time = $newTime;
                $timeFreq = 0;
              }
            }

            // 重置buffer为空，并重新设定total size 为0；
            $buffer = array();
            $totalSize = 0;
          }
          // doc 添加到buffer中，并增加total size的大小。
          $buffer[] = $doc;
          $totalSize += $currentSize;

          // 初始化doc。
          $doc = array('cmd' => '', 'fields' => array());
        } else if ($separator == self::HA_DOC_FIELD_SEPARATOR) {
          // 表示当前字段结束。
          $detail = substr($line, 0, -2);

          if (!empty($lastField)) {

            // 表示当前行非第一行数据，则获取最后生成的字段名称并给其赋值。
            $doc['fields'][$lastField] =
                $this->_extractFieldValue($doc['fields'][$lastField] . $detail);
          } else {

            // 表示当前为第一行数据，则解析key 和value。
            list($key, $value) = $this->_parseHADocField($detail);

            if (strtoupper($key) == 'CMD') {
              $doc['cmd'] = strtoupper($value);
            } else {
              $doc['fields'][$key] = $this->_extractFieldValue($value);
            }
          }

          // 设置字段名称为空。
          $lastField = '';
        } else {
          // 此else 表示富文本的非最后一行。

          // 表示富文本非第一行。
          if (!empty($lastField)) {
            $doc['fields'][$lastField] .= $line;
          } else {
            // 表示字段的第一行数据。
            list($key, $value) = $this->_parseHADocField($line);

            $doc['fields'][$key] = $value;
            $lastField = $key;
          }
        }
        $lineNumber ++;
      }

      fclose($reader);

      // 如果buffer 中还有数据则再push一次数据。
      if (!empty($buffer)) {
        $return = json_decode($this->add($buffer, $tableName), true);
        if (self::PUSH_RETURN_STATUS_OK != $return['status']) {
          throw new Exception("Api returns error. " . $return['errors'][0]['message']);
        }
      }

      if (!empty($doc['fields'])) {
        throw new Exception('Fail to push doc:' . json_encode($doc));
      }

      return json_encode(
        array('status' => 'OK', 'message' => 'The data is posted successfully.')
      );
    } catch (Exception $e) {
      throw new Exception(
        $e->getMessage() .
        '. Latest posted successful line no is ' . $lastLineNumber
      );
    }
  }

  /**
   * 创建一个文件指针资源。
   * @param string $fileName
   * @throws Exception
   * @return resource 返回文件指针。
   */
  private function _connect($fileName) {
    $reader = fopen($fileName, "r");
    if (!$reader) {
      throw new Exception("The file is not exists or not readabled. Please
          check your file.");
    }
    return $reader;
  }

  /**
   * 解析一段字符串并生成key和value。
   * @param string $string
   * @return string|boolean 返回一个数组有两个字段，第一个为key，第二个为value。如果解析
   * 失败则返回错误。
   */
  private function _parseHADocField($string) {
    $separater = '=';
    $pos = strpos($string, $separater);

    if ($pos !== false) {
      $key = substr($string, 0, $pos);
      $value = substr($string, $pos + 1);
      return array($key, $value);
    } else {
      throw new Exception('The are no key and value in the field.');
    }
  }

  /**
   * 检查字段值的值是否为多值字段，如果是则返回多值的数组，否则返回一个string的结果。
   * @param string $value 需要解析的结果。
   * @return string|string 如果非多值则返回字符串，否则返回多值数组。
   */
  private function _extractFieldValue($value) {
    $split = explode(self::HA_DOC_MULTI_VALUE_SEPARATOR, $value);
    return count($split) > 1 ? $split : $split[0];
  }
}