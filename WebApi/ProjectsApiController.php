<?

class ProjectsApiController extends BaseWebApiController {

	/**
	 * creates a new project
	 * @param unknown $parameters
	 * @return ApiResponse
	 */
	public function CreateProject($parameters) {
		$body = file_get_contents('php://input');

		$project = Project::createModelFromJson($body); 
		$service = new ProjectService($this->contextUser, $this->repository);
		$validationState = new ValidationState();
		$project = $service->createProject($project, $validationState);

		return $this->createApiResponseForObject($project, $validationState);

	}

	/**
	 * creates a new project component
	 * @param unknown $parameters
	 */
	public function CreateProjectComponent($parameters) {
		$body = file_get_contents('php://input');
		$component = Component::createModelFromJson($body);
		$id = $parameters["id"];
		$component->projectId = $id;
		
		$service = new ProjectService($this->contextUser, $this->repository);
		$validationState = new ValidationState();
		$component = $service->createProjectComponent($component, $validationState);
		if ($validationState->validationStateType == ValidationStateType::Error) {
			return $validationState;
		} else {
			return $component;
		}
	}
	
	/**
	 * updates a project
	 * @param unknown $parameters
	 */
	public function UpdateProject($parameters) {
		$body = file_get_contents('php://input');
		$id = $parameters["id"];
		$project = Project::createModelFromJson($body);
		$project->projectId = $id;
		$service = new ProjectService($this->contextUser, $this->repository);
		$validationState = new ValidationState();
		$project = $service->updateProject($project, $validationState);
		if ($validationState->validationStateType == ValidationStateType::Error) {
			return $validationState;
		} else {
			return $project;
		}
	}

	
	/**
	 * updates a component
	 * @param unknown $parameters
	 */
	public function UpdateComponent($parameters) {
		$body = file_get_contents('php://input');
		$id = $parameters["id"];
		$component = Component::createModelFromJson($body);
		$component->componentId = $id;
		$service = new ProjectService($this->contextUser, $this->repository);
		$validationState = new ValidationState();
		$component = $service->updateComponent($component, $validationState);
		if ($validationState->validationStateType == ValidationStateType::Error) {
			return $validationState;
		} else {
			return $component;
		}
	}
	
	
	
	
	/**
	 * retrieves a project by name
	 * @param unknown $parameters
	 * @return string
	 */
	public function GetProjectByName($parameters) {
		$name = $parameters["name"];
		
		$service = new ProjectService($this->contextUser, $this->repository);
		$validationState = new ValidationState();
		$project = $service->getProjectByName($name, $validationState);
		if ($project == null) {
			return $validationState;
		} else {
			return $project;
		}
	}
	
	/**
	 * retrieves preview object for a project
	 * @param unknown $parameters
	 * @return string
	 */
	public function GetProjectPreview($parameters) {
		$id = $parameters["id"];
		$service = new ProjectService($this->contextUser, $this->repository);
		$validationState = new ValidationState();
		$project = $service->getProjectPreview($id, $validationState);
		if ($project == null) {
			return $validationState;
		} else {
			return $project;
		}
	}

	/**
	 * retrieves preview object for a component
	 * @param unknown $parameters
	 * @return string
	 */
	public function GetComponentPreview($parameters) {
		$id = $parameters["id"];
		$service = new ProjectService($this->contextUser, $this->repository);
		$validationState = new ValidationState();
		$preview = $service->getComponentPreview($id, $validationState);
		if ($preview == null) {
			return $validationState;
		} else {
			return $preview;
		}
	}
	
	
	
	/**
	 * retrieves a component
	 * @param unknown $parameters
	 * @return string
	 */
	public function GetComponent($parameters) {
		$id = $parameters["id"];
		$service = new ProjectService($this->contextUser, $this->repository);
		$validationState = new ValidationState();
		$component = $service->getComponent($id, $validationState);
		if ($component == null) {
			return $validationState;
		} else {
			return $component;
		}
	}
	
	
	
	/**
	 * retrieves components of a project
	 * @param unknown $parameters
	 * @return string
	 */
	public function GetProjectComponents($parameters) {
		$id = $parameters["id"];
		$service = new ProjectService($this->contextUser, $this->repository);
		$validationState = new ValidationState();
		$components = $service->getProjectComponents($id, $validationState);
		if (!is_array($components)) {
			return $validationState;
		} else {
			$data["data"] = $components;
			return $data;
		}
	}
	

	/**
	 * retrieves a project by id
	 * @param unknown $parameters
	 * @return string
	 */
	public function GetProjectById($parameters) {
		$id = $parameters["id"];
		$service = new ProjectService($this->contextUser, $this->repository);
		$validationState = new ValidationState();
		$project = $service->getProjectById($id, $validationState);
		if ($project == null) {
			return $validationState;
		} else {
			return $project;
		}
	}
	
	
	/**
	 * retrieves all projects
	 * @param unknown $parameters
	 * @return string
	 */
	public function GetProjects() {
		$service = new ProjectService($this->contextUser, $this->repository);
	
		$validationState = new ValidationState();
		$projects = $service->getProjects($validationState);
		$data["data"] = $projects;
		
		return $this->createApiResponseForArray($projects, $validationState);
		/*
		if ($projects == null) {
			return $validationState;
		} else {
			$data["data"] = $projects;
			return $data;
		}
		*/
	}

	/**
	 * updates position of dashboardtile
	 * @param unknown $parameters
	 */
	public function UpdatePosition($parameters) {
		$body = file_get_contents('php://input');

		
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