<?php

class DashboardsApiController extends BaseWebApiController {

	public function getDashboardById($parameters) {
		$dashboardId = $parameters["id"];
		
		$service = new DashboardService($this->contextUser, $this->repository);
		
		$dashboard = $service->getDashboardById($dashboardId);
		return $dashboard;
	}
	
	/**
	 * updates position of dashboardtile
	 * @param unknown $parameters
	 */
	public function UpdatePosition($parameters) {
	
		$dashboardTile = new DashboardTile();
		$dashboardTile->dashboardTileId = $parameters["id"];
		$dashboardTile->height = $_POST["size_y"];
		$dashboardTile->width = $_POST["size_x"];
		$dashboardTile->col = $_POST["col"];
		$dashboardTile->row = $_POST["row"];

		$service = new DashboardService($this->contextUser, $this->repository);
		$service->updateDashboardTilePosition($dashboardTile);
		
	}

	/**
	 * adds a tile to a dashboard
	 * @param unknown $parameters
	 */
	public function addTileToDashboard($parameters) {
		$dashboardId = $parameters["id"];
		$service = new DashboardService($this->contextUser, $this->repository);
		
		$dashboard = $service->getDashboardById($dashboardId);
		if ($dashboard == null) {
			throw new WebApiException("dashboardId $dashboardId does not exist");
		}
		
		$tileId = $this->getMandatoryParameter("tileId");
		
		$tile = $service->getTileById($tileId);
		if ($tile == null) {
			throw new WebApiException("tileId $tileId does not exist");
		}

		$actionResult = $service->addTileToDashboard($tile, $dashboard);	
		return $actionResult;
	}
	

	/**
	 * remove tile from dashboard
	 * @param unknown $parameters
	 */
	public function removeTileFromDashboard($parameters) {
		$tileId = $parameters["id"];
		$service = new DashboardService($this->contextUser, $this->repository);
		$actionResult = $service->removeTileFromDashboard($tileId);
		return $actionResult;
	}
	
	
	/**
	 * gets list of all tiles registered in the system
	 * @param unknown $parameters
	 * @return string
	 */
	public function GetTiles() {
		$service = new DashboardService($this->contextUser, $this->repository);
		$tiles = $service->getTiles();
		$result = array();
		$result["data"] = $tiles;
		return $result;
	}
	
}


?>