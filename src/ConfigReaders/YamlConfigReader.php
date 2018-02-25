<?php

namespace youconix\Core\ConfigReaders;


class YamlConfigReader extends \youconix\Core\ConfigReaders\AbstractConfig implements \ConfigReader
{

  /**
   *
   * @param string $file
   * @throws \RuntimeException
   */
  public function loadConfig($file)
  {
    $this->config = [];

    if (!function_exists('yaml_parse_file')) {
      throw new \CoreException('YAML library is required for using yaml configuration files.');
    }

    $reader = yaml_parse_file($file);

    if ($reader) {
      $this->parseFile($reader);
    }
  }

  /**
   * @param array $content
   */
  protected function parseFile(array $content)
  {
    $keys = array_keys($content);
    $root = $keys[0];

    $this->config = [];
    $this->parseArray($content[$root], $root);
  }

  /**
   * @param array $content
   * @param string $pre
   */
  protected function parseArray(array $content, $pre)
  {
    foreach ($content as $key => $value) {
      $newKey = $pre . '.' . $key;

      if (is_array($value)) {
        $keys = array_keys($value);
        if (!is_numeric($keys[0])) {
          $this->parseArray($value, $newKey);
          continue;
        }
      }

      if (array_key_exists($newKey, $this->config)) {
        if (!is_array($newKey, $this->config)) {
          $this->config[$newKey] = [$this->config[$newKey]];
        }
        $this->config[$newKey][] = $value;
        continue;
      }

      $this->config[$newKey] = $value;
    }
  }

  /**
   * @return array
   */
  public function getConfigAsArray()
  {
    return $this->config;
  }
}