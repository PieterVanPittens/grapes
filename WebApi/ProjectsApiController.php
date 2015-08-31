<?php

class ProjectsApiController extends BaseWebApiController {

	/**
	 * creates a new project
	 * @param unknown $parameters
	 * @return ActionResult
	 */
	public function CreateProject($parameters) {
		$body = file_get_contents('php://input');

		$project = Project::createModelFromJson($body); 
		$service = new ProjectService($this->contextUser, $this->repository);
		$validationState = new ValidationState();
		$actionResult = $service->createProject($project, $validationState);

		return $actionResult;

	}

	/**
	 * creates a new project component
	 * @param unknown $parameters
	 */
	public function createProjectComponent($parameters) {
		$body = file_get_contents('php://input');
		$component = Component::createModelFromJson($body);
		
		$service = new ProjectService($this->contextUser, $this->repository);
		$component = $service->createComponent($component);
		return $component;
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
		$actionResult = $service->updateComponent($component);
		return $actionResult;
	}
	
	/**
	 * saves a project user role
	 * @param AccessControlItem $aci
	 * @return ActionResult
	 */
	public function saveProjectAci($parameters) {
		$projectId = $parameters["id"];
		$body = file_get_contents('php://input');
		$aci = AccessControlItem::createModelFromJson($body);
		$aci->projectId = $projectId;
		$service = new ProjectService($this->contextUser, $this->repository);
		$actionResult = $service->saveProjectAci($aci);
		return $actionResult;
	}
	
	
	/**
	 * retrieves a project by identifier
	 * @param unknown $parameters
	 * @return string
	 */
	public function getProjectByIdentifier($parameters) {
		$identifier = $parameters["identifier"];
	
		$service = new ProjectService($this->contextUser, $this->repository);
		$project = $service->getProjectByIdentifier($identifier);
		if ($project == null) {
			throw new NotFoundException("Project '$identifier' not found");
		}
		return $project;
	}
	
	
	/**
	 * retrieves a project by name
	 * @param unknown $parameters
	 * @return string
	 */
	public function GetProjectByName($parameters) {
		$name = $parameters["name"];
		
		$service = new ProjectService($this->contextUser, $this->repository);
		$project = $service->getProjectByName($name);
		if ($project == null) {
			throw new NotFoundException("Project '$name' not found");
		}
		return $project;
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
		$component = $service->getComponentById($id);
		return $component;
	}
	
	
	
	/**
	 * retrieves components of a project
	 * @param unknown $parameters
	 * @return string
	 */
	public function GetProjectComponents($parameters) {
		$id = $parameters["id"];
		$service = new ProjectService($this->contextUser, $this->repository);
		$components = $service->getProjectComponents($id);
		$data["data"] = $components;
		return $data;
	}
	
	
	/**
	 * retrieves releases of a project
	 * @param unknown $parameters
	 * @return string
	 */
	public function getProjectReleases($parameters) {
		$id = $parameters["id"];
		$service = new ProjectService($this->contextUser, $this->repository);
		$releases = $service->getProjectReleases($id);
		$data["data"] = $releases;
		return $data;
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
		$projects = $service->getProjects();
		$data["data"] = $projects;
		return $data;
	}

	/**
	 * retrieves all project users
	 * @param unknown $parameters
	 * @return string
	 */
	public function getProjectUsers($parameters) {
		$id = $parameters["id"];
		$service = new ProjectService($this->contextUser, $this->repository);
		$users = $service->getProjectUsers($id);
		$data["data"] = $users;
		return $data;
	}

	/**
	 * retrieves a list of all users in the system and their rights in this project
	 * @param unknown $parameters
	 * @return string
	 */
	public function getProjectUsersAll($parameters) {
		$id = $parameters["id"];
		$service = new ProjectService($this->contextUser, $this->repository);
		$users = $service->getProjectUsersAll($id);
		$data["data"] = $users;
		return $data;
	}
	
	
	
	/**
	 * retrieves all badges
	 * @param unknown $parameters
	 * @return string
	 */
	public function getBadges() {
		$service = new BadgeService($this->contextUser, $this->repository);
		$badges = $service->getBadges();
		$data["data"] = $badges;
		return $data;
	}
	
	public function getProjectProgress($parameters) {
		$projectId = $parameters["id"];
		
		$service = new ProjectService($this->contextUser, $this->repository);
		$progress = $service->getProjectProgress($projectId);
		return $progress;
		
	}	
}


?>