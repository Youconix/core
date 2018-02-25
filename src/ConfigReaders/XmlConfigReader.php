<?php

namespace youconix\core\ConfigReaders;

class XmlConfigReader extends \youconix\core\ConfigReaders\AbstractConfig implements \ConfigReader
{
  /**
   *
   * @param string $file
   * @throws \RuntimeException
   */
  public function loadConfig($file)
  {
    $reader = simplexml_load_file($file);
    $this->readDocument($reader);
  }

  /**
   * @param \SimpleXMLElement $reader
   */
  protected function readDocument($reader)
  {
    $path = $reader->getName();

    $this->readArray($reader, $path);

  }

  /**
   * @param \SimpleXMLElement $node
   * @param string $path
   */
  protected function readArray(\SimpleXMLElement $node, $path)
  {
    foreach ($node as $key => $value) {
      if ($value->count() > 0) {
        $this->readArray($value, $path . '.' . $key);
      } else {
        $value = (string)$value;

        $key = $path . '.' . $key;
        if (!array_key_exists($key, $this->config)) {
          $this->config[$key] = trim($value);
        } else {
          if (!is_array($this->config[$key])) {
            $this->config[$key] = [$this->config[$key]];
          }
          $this->config[$key][] = $value;
        }
      }
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