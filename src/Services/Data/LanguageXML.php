<?php

namespace youconix\Core\Services\Data;

/**
 * Xml language-handler for making your website language-independent
 */
class LanguageXML extends \youconix\Core\Services\Language
{
  /**
   * Calls the set language-file and reads it
   *
   * @param string $language
   * @return array The documents
   * @throws IOException If the system of site language file is missing
   */
  protected function readLanguage($language)
  {
    $documents = $this->readCacheFile($language);
    if (!is_null($documents)) {
      return $documents;
    }

    $documents = [];
    if ($this->file->exists(NIV . 'language/language_' . $language . '.lang')) {
      $documents = $this->readDocument(
        $this->loadDocument(NIV . 'language/language_' . $language . '.lang'),
        $documents
      );
    }

    /* Get files */
    $files = $this->file->readDirectory(NIV . 'language/' . $language . '/LC_MESSAGES');
    foreach ($files as $file) {
      if (strpos($file, '.lang') === false) {
        continue;
      }

      $documents = $this->readDocument(
        $this->loadDocument(NIV . 'language/' . $language . '/LC_MESSAGES/' . $file),
        $documents
      );
    }

    if (count($documents) == 0) {
      throw new \IOException('Missing language files for language ' . $language . '.');
    }

    $this->writeCacheFile($language, $documents);

    return $documents;
  }

  /**
   * @param \SimpleXMLElement $reader
   * @param array $language
   * @return array
   */
  protected function readDocument($reader, array $language)
  {
    $path = $reader->getName();

    $language = $this->readArray($reader, $path, $language);

    return $language;

  }

  /**
   * @param \SimpleXMLElement $node
   * @param string $path
   * @param array $language
   * @return array
   */
  protected function readArray(\SimpleXMLElement $node, $path, array $language)
  {
    foreach ($node as $key => $value) {
      if ($value->count() > 0) {
        $language = $this->readArray($value, $path . '.' . $key, $language);
      } else {
        $value = (string)$value;
        $language[$path . '.' . $key] = trim($value);
      }
    }

    return $language;
  }

  /**
   * @param string $filename
   * @return \SimpleXMLElement
   */
  protected function loadDocument($filename)
  {
    $reader = simplexml_load_file($filename);
    return $reader;
  }
}