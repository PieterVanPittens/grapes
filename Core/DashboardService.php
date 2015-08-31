<?php
/**
 * Dashboard Service
 * @package Services
 *
 */
class DashboardService extends BaseService {

	/**
	 * internal manager
	 * @var DashboardManager
	 */
	private $manager;
	
	function __construct($contextUser, $repository) {
		parent::__construct($contextUser, $repository);
		$this->manager = new DashboardManager($repository);
	}
	
	/**
	 * retrieves dashboard by name
	 * @param string $name
	 * @return Dashboard
	 */
	public function getDashboardByName($name) {
		$model = $this->repository->GetDashboardByName($name);
		return $model;
	}

	/**
	 * retrieves dashboard by id
	 * @param int $id
	 * @return Dashboard
	 */
	public function getDashboardById($id) {
		$model = $this->repository->getDashboardById($id);
		return $model;
	}

	/**
	 * adds a tile to a dashboard
	 *
	 * @param Tile $tile
	 * @param Dashboard $dashboard
	 * return DashboardTile
	 */
	public function addTileToDashboard($tile, $dashboard) {
		// todo: checks
		// no guest allowed
		// only my own dashboards

		$dashboardTile = $this->manager->addTileToDashboard($tile, $dashboard);
		$message = "Tile added";
		$actionResult = new ActionResult($dashboardTile, 0, 0, $message);
		
		return $actionResult;
		
	}

	/**
	 * remove tile from dashboard
	 * @param int $dashboardTileId
	 * @return ActionResult
	 */
	public function removeTileFromDashboard($dashboardTileId) {
		// todo: security checks: cannot just remove any tile from any dashboard..
		$dashboardTile = new DashboardTile();
		$dashboardTile->dashboardTileId = $dashboardTileId;

		$this->repository->removeTileFromDashboard($dashboardTile);
		$actionResult = new ActionResult(null, 0,0,"Tile removed");
		return $actionResult;
		
	}

	/**
	 * Creates a new Dashboard
	 * @param Dashboard $dashboard
	 * @return Dashboard
	 */
	public function createDashboard($dashboard) {
		/* is the model a model? */
		if (!is_object($dashboard)) {
			throw new ParameterException("dashboard is null");
		}
		if (get_class($dashboard) != "Dashboard") {
			throw new ParameterException("dashboard is not of type Dashboard");
		}
	
		/* authorized? */
		$isAuthorized = true;
		// todo: auth check
		if (!$isAuthorized) {
			throw new UnauthorizedException();
		}
	
		/* model valid? */
		$modelException = new ModelException("Dashboard contains validation errors");
		// check properties
		if ($dashboard->name == "") {
			$modelException->addModelError("name", "empty");
		}
		// done
		if ($modelException->hasModelErrors()) {
			throw $modelException;
		}
	
		if (!$dashboard->isNew()) {
			throw new ModelException("Dashboard is not new, cannot be created again");
		}
	
		// finally: we can create the Dashboard
		// create dashboard for this user
		$dashboard->objectId = $this->contextUser->getId();
		$dashboard->objectTypeId = $this->contextUser->getObjectType();
		$dashboard = $this->manager->createDashboard($dashboard, $this->contextUser);
		return $dashboard;
	}

	/**
	 * gets a dashboardtile by Id
	 * @param DashboardTile $dashboardTileId
	 * @return DashboardTile
	 */
	public function getDashboardTileById($dashboardTileId) {
		// todo: auth check: allowed load this one?

		$dashboardTile = $this->repository->getDashboardTileById($dashboardTileId);

		return $dashboardTile;
	}

	/**
	 * gets a tile by Id
	 * @param Tile $tileId
	 * @api
	 * @return Tile
	 */
	public function getTileById($tileId) {
		$tile = $this->repository->getTileById($tileId);

		return $tile;
	}
	
	/**
	 * gets a tile by name
	 * @param string $name
	 * @api
	 * @return Tile
	 */
	public function getTileByName($name) {
		return $this->manager->getTileByName($name);
	}

	/**
	 * gets all tiles registered in the system
	 * @api
	 * @return array
	 */
	public function getTiles() {
		$tiles = $this->repository->getTiles();

		return $tiles;
	}

	/**
	 * updates position and size of a dashboard tile
	 * @api
	 * @param DashboardTile $dashboardTile
	 * @return DashboardTile
	 */
	public function updateDashboardTilePosition($dashboardTile) {
		$dashboardTile = $this->repository->updateDashboardTilePosition($dashboardTile);

		return $dashboardTile;
	}
}


class DashboardManager extends BaseManager {
	
	/**
	 * Creates a new dashboard for an object
	 * @param Object $object
	 * @param User $user
	 * @return Dashboard
	 */
	public function createDashboardForObject($object, $user) {
		if (!is_object($object)) {
			throw new ParameterException("object is null");
		}
		$dashboard = new Dashboard();
		$dashboard->name = $object->getObjectName();
		$dashboard->objectId = $object->getId();
		$dashboard->objectTypeId = $object->getObjectType();
		return $this->createDashboard($dashboard, $user);
		
	}
	
	/**
	 * creates a dashboard
	 * @param Dashboard $dashboard
	 * @param User $user
	 * @return Dashboard
	 */
	public function createDashboard($dashboard, $user) {
		if (!is_object($dashboard)) {
			throw new ParameterException("dashboard is null");
		}
		$dashboard->createdAt = time();
		$dashboard->createdByUserId = $user->userId;
		$this->repository->beginTransaction();
		$dashboard = $this->repository->createDashboard($dashboard);
		
		$parameterManager = new ParameterManager($this->repository);
		$parameterManager->createParameterValuesForObject($dashboard);

		$this->repository->commit();
		return $dashboard;
	}


	/**
	 * adds a tile to a dashboard
	 *
	 * @param Tile $tile
	 * @param Dashboard $dashboard
	 * return DashboardTile
	 */
	public function addTileToDashboard($tile, $dashboard) {
		if (!is_object($tile)) {
			throw new ParameterException("tile is null");
		}
		if (get_class($tile) != "Tile") {
			throw new ParameterException("tile is not of type Tile");
		}
		if (!is_object($dashboard)) {
			throw new ParameterException("dashboard is null");
		}
		if (get_class($dashboard) != "Dashboard") {
			throw new ParameterException("dashboard is not of type Dashboard");
		}
		
		$dashboardTile = new DashboardTile();
		$dashboardTile->dashboardId = $dashboard->dashboardId;
		$dashboardTile->tileId = $tile->tileId;
		$dashboardTile->width= $tile->defaultWidth;
		$dashboardTile->height= $tile->defaultHeight;
	
		// todo: row and col need to be determined automatically
		if ($dashboardTile->row == null) {
			$dashboardTile->row= 1;
		}
		if ($dashboardTile->col == null) {
			$dashboardTile->col = 1;
		}
	
		$dashboardTile = $this->repository->createDashboardTile($dashboardTile);
		$parameterManager = new ParameterManager($this->repository);
		$parameterManager->createParameterValuesForObject($dashboardTile);
		
		return $dashboardTile;
	}

	/**
	 * gets Tile by name
	 * @param string $name
	 * @return Tile
	 */
	public function getTileByName($name) {
		if ($name == "") {
			throw new ParameterException("name is empty");
		}
		$model = $this->repository->getTileByName($name);
		if ($model == null) {
			throw new NotFoundException($name);
		}
		return $model;
	}	


}

?>