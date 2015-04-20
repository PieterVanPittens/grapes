<?

class DashboardsApiController extends BaseWebApiController {

	public function Get($parameters) {
		$dashboardName = $parameters["dashboardName"];
		
		$service = new DashboardService($this->contextUser, $this->repository);
		
		$dashboard = $service->getDashboardByName($dashboardName);
		if ($dashboard == null) {
			return "HTTP/1.0 404 Not Found";
		} else {
			return json_encode($dashboard);
		}
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
	public function AddTileToDashboard($parameters) {
		$dashboardId = $parameters["id"];
		$service = new DashboardService($this->contextUser, $this->repository);
		
		$dashboard = $service->getDashboardById($dashboardId);
		if ($dashboard == null) {
			return "HTTP/1.0 404 Not Found";
		}

		$tileId = $_POST["tileId"];
		
		$tile = $service->getTile($tileId);
		if ($tile == null) {
			return "HTTP/1.0 400 Tile not Found";
		}

		$validationState = new ValidationState();
		$dashboardTile = $service->addTileToDashboard($tile, $dashboard, $validationState);	
	}
	

	/**
	 * remove tile from dashboard
	 * @param unknown $parameters
	 */
	public function removeTileFromDashboard($parameters) {
		$tileId = $parameters["id"];
		$service = new DashboardService($this->contextUser, $this->repository);
		$service->removeTileFromDashboard($tileId);
		
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
		$result["tiles"] = $tiles;
		
		if ($tiles == null) {
			return "HTTP/1.0 404 Not Found";
		} else {
			return json_encode($result);
		}
	}
	
}


?>