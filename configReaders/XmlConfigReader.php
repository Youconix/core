<?php

namespace youconix\core\configReaders;

class XmlConfigReader extends \youconix\core\services\Xml implements \ConfigReader
{

  protected $s_settingsDir;

  /**
   * 
   * @param string $file
   * @throws \RuntimeException
   */
  public function loadConfig($file)
  {
    $this->s_settingsDir = DATA_DIR . 'settings';

    $fileName = $this->s_settingsDir . '/' . $file . '.xml';
    if (!file_exists($fileName)) {
      throw new \RuntimeException('Can not find config file ' . $fileName . '.');
    }

    $this->load($fileName);
    $this->s_startTag = $file;
  }

  /**
   * Gives the asked block of the loaded file
   *
   * @param string $path
   * @return string The content of the requested part
   * @throws \XMLException when the path does not exist
   * @return array The block
   */
  public function getBlock($path)
  {
    $block = parent::getBlock($path);

    $result = [];
    foreach ($block AS $item) {
      $tag = $item->tagName;
      if (count($item->childNodes) > 0) {
	$result[$tag] = [];
	foreach ($item->childNodes as $child) {
	  $result[$tag][$child->tagName] = $child->nodeValue;
	}
      } else {
	$result[$tag] = $item->nodeValue;
      }
    }

    $keys = array_keys($result);
    if ($keys[0] == $this->s_startTag) {
      return $result[$this->s_startTag];
    }
    return $result;
  }
}
