<?php

namespace youconix\Core\Cli;

class Database extends \youconix\Core\Templating\CliController
{
  /**
   * @var \DALInterface
   */
  private $dal;

  public function __construct(\DALInterface $dal)
  {
    parent::__construct();

    $this->dal = $dal;
  }

  public function check()
  {
    $this->message('Checking database...');

    $tables = $this->getTables();
    try {
      foreach ($tables as $table) {
        if (!$this->dal->analyse($table)) {
          throw new \Exception('Table ' . $table . ' is damaged.');
        }
      }

      $this->message('Database check complete');
    } catch (\Exception $e) {
      $this->message(
        'Database check failed.' . PHP_EOL .
        'Reason: ' . $e->getMessage() . PHP_EOL .
        'Run database:repair.'
      );
    }
  }

  public function optimize()
  {
    $this->message('Optimizing database...');

    $tables = $this->getTables();
    try {
      foreach ($tables as $table) {
        if (!$this->dal->optimize($table)) {
          throw new \Exception('Table ' . $table . ' is damaged.');
        }
      }

      $this->message('Database optimizing complete');
    } catch (\Exception $e) {
      $this->message(
        'Database optimizing failed.' . PHP_EOL .
        'Reason: ' . $e->getMessage() . PHP_EOL .
        'Run database:repair.'
      );
    }
  }

  public function repair()
  {
    $this->message('Repairing database...');

    $tables = $this->getTables();
    try {
      foreach ($tables as $table) {
        if (!$this->dal->repair($table)) {
          throw new \Exception('Table ' . $table . ' could not be repaired.');
        }
      }

      $this->message('Database repair complete');
    } catch (\Exception $e) {
      $this->message(
        'Database repair failed.' . PHP_EOL .
        'Reason: ' . $e->getMessage() . PHP_EOL
      );
    }
  }

  public function dump()
  {
    if (!function_exists('exec')) {
      throw new \RuntimeException('Exec must be activated to dump the database.');
    }
    $this->message('Dumping database database to a file...');

    $file = DATA_DIR . 'database_' . date('d-m-Y') . '.sql';
    $this->dal->dump($file);
    $this->message('Dumping database complete.' . PHP_EOL . 'Location: ' . $file . '.');
  }

  private function getTables()
  {
    $this->dal->prepare('SHOW TABLES');
    $this->dal->exequte();
    $result = $this->dal->fetch_row();

    $tables = [];
    foreach ($result as $item) {
      $tables[] = $item[0];
    }

    return $tables;
  }
}