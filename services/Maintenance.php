<?php

namespace youconix\core\services;

/**
 * Maintenance service for maintaining the website
 *
 * This file is part of Miniature-happiness
 *
 * @copyright Youconix
 * @author Rachelle Scheijen
 * @version 1.0
 * @since 1.0
 */
class Maintenance extends Service {
	/**
	 * 
	 * @var \Builder
	 */
	protected $builder;
	/**
	 * 
	 * @var \youconix\core\models\Stats
	 */
	protected $stats;
	
	/**
	 * PHP 5 constructor
	 *
	 * @param Builder $builder
	 * @param core\models\Stats $stats
	 */
	public function __construct(\Builder $builder, \youconix\core\models\Stats $stats) {
		$this->builder = $builder;
		$this->stats = $stats;
	}
	
	/**
	 * Optimizes the database tables
	 */
	public function optimizeDatabase() {
		$a_tables = $this->getTables ();
		$i_registrated = time () - 172800; // 2 days ago
		$i_pm = time () - 2592000; // 30 days ago
		
		try {
			$this->builder->delete ( 'users' )->getWhere ()->addAnd ( array (
					'registrated',
					'active' 
			), array (
					'i',
					's' 
			), array (
					$i_registrated,
					'0' 
			), array (
					'<',
					'=' 
			) );
			$this->builder->getResult ();
			
			$this->builder->delete ( 'pm' )->getWhere ()->addAnd ( 'send', 'i', $i_pm, '<' );
			$this->builder->getResult ();
			
			$service_Database = $this->builder->getDatabase ();
			
			foreach ( $a_tables as $a_table ) {
				$bo_status = $service_Database->optimize ( $a_table [0] );
				
				if (! $bo_status) {
					/* Try repair table */
					$service_Database->repair ( $a_table [0] );
					$service_Database->optimize ( $a_table [0] );
				}
			}
			
			return 1;
		} catch ( \DBException $e ) {
			reportException( $e );
			
			return 0;
		}
	}
	
	/**
	 * Checks the database tables and auto repairs
	 */
	public function checkDatabase() {
		$a_tables = $this->getTables ();
		
		$service_Database = $this->builder->getDatabase ();
		
		try {
			foreach ( $a_tables as $a_table ) {
				$bo_status = $service_Database->analyse ( $a_table [0] );
				
				if (! $bo_status) {
					/* Try repair table */
					$service_Database->repair ( $a_table [0] );
				}
			}
			
			return 1;
		} catch ( \DBException $e ) {
			reportException( $e );
			return 0;
		}
	}
	
	/**
	 * Returns the table names in the current database
	 *
	 * @return array table names
	 */
	protected function getTables() {
		$this->builder->showTables ();
		
		$a_tables = $this->builder->getResult ()->fetch_row ();
		return $a_tables;
	}
	
	/**
	 * Cleans the stats from a year old
	 * 
	 * @return int	1 if the stats are cleared
	 */
	public function cleanStatsYear() {
		try {
			$this->stats->cleanStatsYear ();
			return 1;
		}
		catch(\DBException $e){
			reportException($e);
			return 0;
		}
	}
	
	/**
	 * Cleans the stats from a month old
	 */
	public function cleanStatsMonth() {
		$this->stats->cleanStatsMonth ();
	}
}