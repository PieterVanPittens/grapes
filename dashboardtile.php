<?php
require ("config.inc.php.conf");
require("includes.inc.php");
require("init.inc.php");

if (!isset($_GET["id"])) {
	echo "dashboardTileId missing";
	die();
}
$dashboardTileId = $_GET["id"];


// todo: security checks: only allowed to this if you're authenticated and authorized for this dashboard

$dashboardService = new DashboardService($contextUser, $repository);

$dashboardTile = $dashboardService->getDashboardTileById($dashboardTileId);

if ($dashboardTile == null) {
	echo "Invalid Dashboard Tile Id";
	die();
}
$tile = $dashboardService->getTileById($dashboardTile->tileId);
$name = $tile->name;


// todo: checks
require ("Plugins/Tiles/$name/$name.php");

$classname = $name."Plugin";
$plugin = new $classname();
$plugin->repository = $repository;
$plugin->contextUser= $contextUser;
$plugin->config = $config;
$plugin->tile = $tile;
$plugin->dashboardTile= $dashboardTile;
$plugin->initialize();
$plugin->render();
?>