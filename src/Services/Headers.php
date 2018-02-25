<?php

namespace youconix\Core\Services;

/**
 * Headers generating service
 *
 * @author Rachelle Scheijen
 * @version 1.0
 * @since 2.0
 */
class Headers extends AbstractService implements \HeadersInterface
{

  /**
   * 
   * @var \ConfigInterface
   */
  protected $config;

  /**
   *
   * @var \youconix\Core\Routes
   */
  protected $routes;
  protected $a_headers = array();
  protected $bo_forceDownload = false;

  /**
   * PHP5 constructor
   *
   * @param \ConfigInterface $config
   * @param \youconix\Core\Routes $routes
   */
  public function __construct(\ConfigInterface $config, \youconix\Core\Routes $routes)
  {
    $this->config = $config;
    $this->routes = $routes;

    $this->clear();
  }

  /**
   * Returns if the object should be treated as singleton
   *
   * @return boolean True if the object is a singleton
   */
  public static function isSingleton()
  {
    return true;
  }

  /**
   * Clears the headers
   */
  public function clear()
  {
    $this->a_headers = array();
    $this->contentType('text/html');
  }

  /**
   * Sets the given content type
   *
   * @param string $s_contentType
   *            content type
   */
  public function contentType($s_contentType)
  {
    $this->a_headers['Content-Type'] = array(
	'Content-Type',
	$s_contentType
    );
  }

  /**
   * Sets the javascript content type
   */
  public function setJavascript()
  {
    $this->contentType('application/javascript');
  }

  /**
   * Sets the CSS content type
   */
  public function setCSS()
  {
    $this->contentType('text/css');
  }

  /**
   * Sets the XML content type
   */
  public function setXML()
  {
    $this->contentType('application/xml');
  }

  /**
   * Sets the last modified header
   *
   * @param int $i_modified
   *            modified time as a timestamp
   */
  public function modified($i_modified)
  {
    $this->a_headers['Last-Modified'] = array(
	'Last-Modified',
	gmdate('D, d M Y H:i:s', $i_modified) . ' GMT'
    );
  }

  /**
   * Sets the cache time, -1 for no cache
   *
   * @param int $i_cache
   *            cache time in seconds
   */
  public function cache($i_cache)
  {
    if ($i_cache == - 1) {
      $this->a_headers[] = array(
	  'Expires',
	  'Thu, 01-Jan-70 00:00:01 GMT'
      );
      $this->a_headers['Last-Modified'] = array(
	  'Last-Modified',
	  gmdate('D, d M Y H:i:s') . ' GMT'
      );
      $this->a_headers[] = array(
	  'Cache-Control',
	  'no-store, no-cache, must-revalidate'
      );
      $this->a_headers[] = array(
	  'Cache-Control',
	  'post-check=0, pre-check=0',
	  false
      );
      $this->a_headers[] = array(
	  'Pragma',
	  'no-cache'
      );
    } else {
      $this->a_headers['Expires'] = array(
	  'Expires',
	  gmdate('D, d M Y H:i:s', (time() + $i_cache)) . ' GMT'
      );
    }
  }

  /**
   * Sets the content length
   *
   * @param int $i_length
   *            length in bytes
   */
  public function contentLength($i_length)
  {
    $this->a_headers['Content-Length'] = array(
	'Content-Length',
	$i_length
    );
  }

  /**
   * Force downloads a file
   * Program will halt
   *
   * @param string $s_file
   *            file location
   * @param string $s_contentType
   *            content type
   */
  public function forceDownloadFile($s_file, $s_contentType)
  {
    $i_size = filesize($s_file);

    $this->bo_forceDownload = true;
    $this->contentType($s_contentType);
    $this->a_headers[] = array(
	'Content-Disposition',
	'attachment; filename="' . basename($s_file) . '"'
    );
    $this->contentLength($i_size);
    $this->cache(- 1);
    $this->printHeaders();
    readfile($s_file);
    exit();
  }

  /**
   * Force downloads the given content
   * Program will halt
   *
   * @param string $s_content
   *            content to download
   * @param string $s_contentType
   *            content type
   * @param string $s_name
   *            name of the download
   */
  public function forceDownloadContent($s_content, $s_contentType, $s_name)
  {
    $i_size = strlen($s_content);

    $this->bo_forceDownload = true;
    $this->contentType($s_contentType);
    $this->a_headers[] = array(
	'Content-Disposition',
	'attachment; filename="' . $s_name . '"'
    );
    $this->contentLength($i_length);
    $this->cache(- 1);
    $this->printHeaders();
    echo ($s_content);
    exit();
  }

  /**
   * Sets a header
   *
   * @param string $s_key
   *            header key
   * @param string $s_content
   *            header value
   */
  public function setHeader($s_key, $s_content)
  {
    $this->a_headers[] = array(
	$s_key,
	$s_content
    );
  }

  /**
   * Sends the 304 not modified header
   */
  public function http304()
  {
    $this->a_headers['http'] = array(
	'HTTP/1.1',
	'304 Not Modified'
    );
  }

  /**
   * Sends the 400 bad request header
   */
  public function http400()
  {
    $this->a_headers['http'] = array(
	'HTTP/1.1',
	'400 Bad Request'
    );
  }

  /**
   * Sends the 401 unauthorized header
   */
  public function http401()
  {
    $this->a_headers['http'] = array(
	'HTTP/1.1',
	'401 Unauthorized'
    );
  }

  /**
   * Sends the 403 forbidden header
   */
  public function http403()
  {
    $this->a_headers['http'] = array(
	'HTTP/1.1',
	'403 Forbidden'
    );
  }

  /**
   * Sends the 404 not found header
   */
  public function http404()
  {
    $this->a_headers['http'] = array(
	'HTTP/1.1',
	'404 Not Found'
    );
  }

  /**
   * Sends the 500 internal server header
   */
  public function http500()
  {
    $this->a_headers['http'] = array(
	'HTTP/1.1',
	'500 Internal Server Error'
    );
  }

  /**
   * Sends the 503 service unavailable header
   */
  public function http503()
  {
    $this->a_headers['http'] = array(
	'HTTP/1.1',
	'503 Service Unavailable'
    );
  }

  /**
   * Sends the 301 redirect header
   * Program will halt
   *
   * @param string $s_location
   *            redirect location
   */
  public function redirect($s_location)
  {
    if (stripos($s_location, 'http') === false && stripos($s_location, 'ftp') === false) {
      if (substr($s_location, 0, 4) == 'www.') {
	$s_location = 'http://' . $s_location;
      } else {
	$s_host = $this->config->getProtocol() . $this->config->getHost();
	if ($this->config->getBase() != '/') {
	  $s_host .= $this->config->getBase();
	}
	if (substr($s_host, - 1) != '/') {
	  $s_host .= '/';
	}

	$s_location = $s_host . $s_location;
	while (strpos($s_location, '//') !== false) {
	  $s_location = str_replace('//', '/', $s_location);
	}
	$s_location = str_replace(array('http:/', 'https:/'),
			   array('http://', 'https://'), $s_location);
      }
    }

    $this->a_headers[] = array(
	'Location',
	$s_location
    );
    $this->printHeaders();
    exit();
  }

  /**
   * Sends the 301 redirect header for the given path
   * Program will halt
   *
   * @param string $s_path
   * @param array $a_parameters
   */
  public function redirectPath($s_path, $a_parameters = [])
  {
    $s_location = $this->routes->getRouteByName($s_path, $a_parameters);
    return $this->redirect($s_location);
  }

  /**
   * Returns if a force download was executed
   *
   * @return boolean True if the download was executed
   */
  public function isForceDownload()
  {
    return $this->bo_forceDownload;
  }

  /**
   * Returns if a redirect was executed
   *
   * @return boolean True if a redirect was executed
   */
  public function isRedirect()
  {
    return array_key_exists('Location', $this->a_headers);
  }

  /**
   * Returns the headers
   *
   * @return array headers
   */
  public function getHeaders()
  {
    return $this->a_headers;
  }

  /**
   * Returns if the template should be skipped
   * @return boolean
   */
  public function skipTemplate()
  {
    if ($this->isForceDownload() || $this->isRedirect()) {
      return true;
    }

    return false;
  }

  /**
   * Imports the given headers
   *
   * @param array $a_headers
   *            The headers
   */
  public function importHeaders($a_headers)
  {
    $this->a_headers = $a_headers;
  }

  /**
   * Sends the cached headers to the client
   */
  public function printHeaders()
  {
    $a_headers = $this->getHeaders();

    foreach ($a_headers as $a_header) {
      isset($a_header[2]) ? $status = $a_header[2] : $status = true;

      header($a_header[0] . ': ' . $a_header[1], $status);
    }
  }
}
