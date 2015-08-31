<?php

/**
 * base class for tile plugins
 *
 */
abstract class BaseTilePlugin {
	public $contextUser;
	public $repository;
	public $config;
	/**
	 * 
	 * @var Tile
	 */
	public $tile;

	/**
	 * 
	 * @var DashboardTile
	 */
	public $dashboardTile;
	/**
	 * initializes the plugin
	 * will be called at first by core
	 */
	public function initialize() {		
		
	}
	
	/**
	 * renders the Tile
	 */
	public function render() {
		
	}
}
?>