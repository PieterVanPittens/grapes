<?php
/**
 * Projects Service
 * @package Services
 *
 */
class ProjectService extends BaseService {
	
	var $projectManager = null;
	
	function __construct($contextUser, $repository) {
		parent::__construct($contextUser, $repository);
		$this->projectManager = new ProjectManager($repository);
	}

	/**
	 * retrieves components of a project
	 * @param int $id
	 * @return array
	 */
	public function getProjectComponents($id) {
		$components = $this->repository->getProjectComponents($id);
		return $components;
	}

	/**
	 * retrieves releases of a project
	 * @param int $id
	 * @return array
	 */
	public function getProjectReleases($id) {
		$releases = $this->repository->getProjectReleases($id);
		return $releases;
	}
	
	/**
	 * retrieves preview object for a project
	 * @param int $id
	 * @return ObjectPreview
	 */
	public function getProjectPreview($id, &$validationState) {
		$model = $this->repository->getProjectById($id);
		$preview = null;
		if ($model != null) {
			$preview = new ObjectPreview();
			$preview->objectType = ObjectTypeEnum::Project;
			$preview->objectName = $model->name;
			$preview->addProperty("name", "strName", $model->name);
			$preview->addProperty("description", "strDescription", $model->description);
			$preview->addProperty("created", "strCreated", "todo...");
			$preview->addProperty("xyz", "strXYZ", "todo...");
		} else {
			$validationState = ValidationHelper::getObjectNotFoundState($id, "Project");
		}
		return $preview;
	}

	/**
	 * retrieves preview object for a component
	 * @param int $id
	 * @return ObjectPreview
	 */
	public function getComponentPreview($id, &$validationState) {
		$model = $this->repository->getComponent($id);
		$preview = null;
		if ($model != null) {
			$preview = new ObjectPreview();
			$preview->objectType = ObjectType::Component;
			$preview->objectName = $model->name;
			$preview->addProperty("name", "strName", $model->name);
			$preview->addProperty("description", "strDescription", $model->description);
			$preview->addProperty("created", "strCreated", "todo...");
			$preview->addProperty("xyz", "strXYZ", "todo...");

		} else {
			$validationState = ValidationHelper::getObjectNotFoundState($id, "Project");
		}
		return $preview;
	}

	/**
	 * gets all users of a project
	 * @param unknown $projectId
	 */
	public function getProjectUsers($projectId) {
		$users = $this->repository->getProjectUsers($projectId);
		return $users;
	}

	/**
	* retrieves a list of all users in the system and their rights in this project
	 * @param unknown $projectId
	 */
	public function getProjectUsersAll($projectId) {
		$users = $this->repository->getProjectUsersAll($projectId);
		return $users;
	}
	
	
	
	
	

	/**
	 * saves project user role
	 * @param AccessControlItem $aci
	 */
	public function saveProjectAci($aci) {
		// revoke any rights from this project
		if ($aci->roleId == null || $aci->roleId == 0) {
			$aci = $this->repository->getAccessControlItem($aci->projectId, $aci->userId);
			if (!is_null($aci)) {
				$this->repository->deleteAccessControlItem($aci->aclId);
			}
			$actionResult = new ActionResult($aci, 0, 0, "Role has been revoked from this project");
			return $actionResult;
		}
		// already has any rights?
		$aciAlreadyThere = $this->repository->getAccessControlItem($aci->projectId, $aci->userId);
		if (!is_null($aciAlreadyThere)) {
			if ($aciAlreadyThere->roleId == $aci->roleId) {
				$actionResult = new ActionResult($aci, 0, 0, "User already has that Role in this Project");				
				return $actionResult;
			} else {
				$this->repository->deleteAccessControlItem($aciAlreadyThere->aclId);
			}
		}
		if ($aci->roleId == RoleEnum::Admin) {
			$aci->projectId = 0;
		}
		$aci = $this->repository->createAccessControlItem($aci);
		$actionResult = new ActionResult($aci, 0, 0, "Role has been given in this project");
		return $actionResult;
	}
	
	
	/**
	 * retrieves project by id
	 * @return Project
	 */
	public function getProjectById($id) {
		$model = $this->repository->getProjectById($id);
		if ($model != null) {
			$this->projectManager->touchObject($model, $this->contextUser);
		}
		return $model;
	}

	/**
	 * retrieves project by name
	 * @param int $name
	 * @return Project
	 */
	public function getProjectByName($name) {
		$model = $this->repository->getProjectByName($name);
		if ($model != null) {
			$this->projectManager->touchObject($model, $this->contextUser);
		}
		return $model;
	}

	
	/**
	 * retrieves project by identifier
	 * @param int $identifier
	 * @return Project
	 */
	public function getProjectByIdentifier($identifier) {
		$model = $this->repository->getProjectByIdentifier($identifier);
		if ($model != null) {
			$this->projectManager->touchObject($model, $this->contextUser);
		}
		return $model;
	}
	
	
	/**
	 * retrieves all projects
	 * @return Array
	 */
	public function getProjects() {
		$this->securityManager->checkAdminAuthorization($this->contextUser);
		$model = $this->repository->getProjects();
		return $model;
	}

	/**
	 * retrieves component by id
	 * @param int $id
	 * @return Component
	 */
	public function getComponent($id, &$validationState) {
		$model = $this->repository->getComponent($id);
		if ($model != null) {
		} else {
			$validationState = ValidationHelper::getObjectNotFoundState($id, "Component");
		}
		return $model;
	}

	/**
	 * Creates a new Component
	 * @param Component $model
	 * @return Component
	 */
	public function createComponent($model) {
		/* is the model a model? */
		if (!is_object($model)) {
			throw new ParameterException("model is null");
		}
		if (get_class($model) != "Component") {
			throw new ParameterException("model is not of type Component");
		}

		/* authorized? */
		$isAuthorized = true;
		// todo: auth check
		if (!$isAuthorized) {
			throw new UnauthorizedException();
		}

		/* model valid? */
		$modelException = new ModelException("Component contains validation errors");
		// check properties
		if ($model->name == "") {
			$modelException->addModelError("name", "empty");
		}
		// dupe check
		$currentComponent = $this->repository->getComponentByKey($model->name, $model->projectId);
		if ($currentComponent != null) {
			// this is a dupe
			$modelException->addModelError("name", sprintf("Component '%s' already exists in this project",  $model->name));
		}
		// done
		if ($modelException->hasModelErrors()) {
			throw $modelException;
		}

		if (!$model->isNew()) {
			throw new ModelException("Component is not new, cannot be created again");
		}

		// create wiki name
		$project = $this->repository->getProjectById($model->projectId);
		$model->wikiName = $project->wikiName.$model->createWikiName();
		
		// finally: we can create the Component
		$this->repository->beginTransaction();
		$model = $this->repository->createComponent($model);

		// create wikipage for component
		$documentManager = new DocumentManager($this->repository);
		$raw = "# Project " . $project->name . " - Component " . $model->name. "\n";
		$raw .= "This is the wiki page of Component " . $model->name;
		$document = $documentManager->createWikiPage($model, $this->contextUser, $raw);
		
		
		$this->repository->commit();
		return $model;
	}


	/**
	 * gets Component by id
	 * @param int $id
	 * @return Component */
	public function getComponentById($id) {
		if ($id == "") {
			throw new ParameterException("id is empty");
		}
		$model = $this->repository->getComponentById($id);
		if ($model == null) {
			throw new NotFoundException($id);
		}
		return $model;
	}
	
	
	/**
	 * Creates a new Project
	 * @param Project $model
	 * @return Project
	 */
	public function createProject($model) {
		/* is the model a model? */
		if (!is_object($model)) {
			throw new ParameterException("model is null");
		}
		if (get_class($model) != "Project") {
			throw new ParameterException("model is not of type Project");
		}

		/* authorized? */
		$this->securityManager->checkAdminAuthorization($this->contextUser);

		/* model valid? */
		$modelException = new ModelException("Project contains validation errors");
		// check properties
		if ($model->name == "") {
			$modelException->addModelError("name", "empty");
		}
		// done
		if ($modelException->hasModelErrors()) {
			throw $modelException;
		}
		if (!$model->isNew()) {
			throw new ModelException("Project is not new, cannot be created again");
		}

		// finally: we can create the Project
		$model->createdByUserId = $this->contextUser->userId;
		$model->createdAt = time();
		$model->wikiName = $model->createWikiName();
		$this->repository->beginTransaction();
		$model = $this->repository->createProject($model);
		$this->projectManager->touchObject($model, $this->contextUser);
		
		// create wikipage for project
		$documentManager = new DocumentManager($this->repository);
		$raw = "# Project " . $model->name. "\n";
		$raw .= "This is the wiki page of Project " . $model->name;
		$document = $documentManager->createWikiPage($model, $this->contextUser, $raw);

		// create dashboard for project
		$dashboardManager = new DashboardManager($this->repository);
		$validationState = new ValidationState();
		$dashboard = $dashboardManager->createDashboardForObject($model, $this->contextUser);

		// configure dashboard
		$tile = $dashboardManager->getTileByName("WikiPage", $validationState);
		$dashboardTile = $dashboardManager->addTileToDashboard($tile, $dashboard);
		$parameterManager = new ParameterManager($this->repository);
		$parameterManager->setParameterValueForObject($tile->parameters[0], $dashboardTile, $document->documentId, $validationState);

		$pointsUpdate = $this->logActionByName("create-project", $model);
			
		
		// create default component
		$component = new Component();
		$component->name = "(default)";
		$component->description = "Default Component of Project '".$model->name."'";
		$component->projectId = $model->projectId;
		$component = $this->createComponent($component);
		
		$model->defaultComponentId = $component->componentId;
		$this->repository->updateProject($model);
		
		
		// the user that creates a project is the project lead
		$this->securityManager->promoteUserToProjectLead($this->contextUser, $model);

		$this->repository->commit();
			
		$pointsNewTotal = $this->repository->getUserTotalPointsById($this->contextUser->userId);
		$message = "Project created";
		$actionResult = new ActionResult($model, $pointsUpdate, $pointsNewTotal, $message);
		
		return $actionResult;
	}

	/**
	 * updates a project
	 * @param Project $project
	 * @param ValidationState $validationState
	 * @return Project
	 */
	public function updateProject($project, &$validationState) {
		// dupe check
		// id check
		if (!$validationState->hasErrors()) {
			$project = $this->repository->updateProject($project, $validationState);
			if (!$validationState->hasErrors()) {
				$this->projectManager->touchObject($model, $this->contextUser);
				
				return $project;
			}
		}
	}

	/**
	 * updates a component
	 * @param Component $component
	 * @return ActionResult
	 */
	public function updateComponent($component) {
		$currentComponent = $this->repository->getComponentById($component->componentId);
		if ($currentComponent == null) {
			throw new NotFoundException("component not found");
		}
		
		$project = $this->repository->getProjectById($component->projectId);

		$component->wikiName = $project->wikiName.$component->createWikiName();
		$component= $this->repository->updateComponent($component);
		$message = "Component saved";
		$actionResult = new ActionResult($component,0, 0, $message);
		return $actionResult;
	}


	public function getRecentProjects() {
		$recentProjects = $this->repository->getRecentProjects($this->contextUser->userId);

		return $recentProjects;
	}


	/**
	 * Creates a new Release
	 * @param Release $model
	 * @return Release
	 */
	public function createRelease($model) {
		/* is the model a model? */
		if (!is_object($model)) {
			throw new ParameterException("model is null");
		}
		if (get_class($model) != "Release") {
			throw new ParameterException("model is not of type Release");
		}

		/* authorized? */
		$isAuthorized = true;
		// todo: auth check
		if (!$isAuthorized) {
			throw new UnauthorizedException();
		}

		/* model valid? */
		$modelException = new ModelException("Release contains validation errors");
		// check properties
		if ($model->name == "") {
			$modelException->addModelError("name", "empty");
		}
		// done
		if ($modelException->hasModelErrors()) {
			throw $modelException;
		}

		if (!$model->isNew()) {
			throw new ModelException("Release is not new, cannot be created again");
		}

		// create wiki name
		$project = $this->repository->getProjectById($model->projectId);
		$model->wikiName = $project->wikiName.$model->createWikiName();

		$this->repository->beginTransaction();
		// finally: we can create the Release
		$model = $this->repository->createRelease($model);
		
		// create wikipage for release
		$documentManager = new DocumentManager($this->repository);
		$raw = "# Project " . $project->name . " - Release " . $model->name. "\n";
		$raw .= "This is the wiki page of Release " . $model->name;
		$document = $documentManager->createWikiPage($model, $this->contextUser, $raw);
		
		$this->repository->commit();		
		return $model;
	}
	
	/**
	 * gets project stats
	 * @param int $projectId
	 * @return ProjectProgress
	 */
	public function getProjectProgress($projectId) {
		$progress = $this->repository->getProjectStatus($projectId);
		
		$total = 0;
		for ($i = 1; $i<=4;$i++) {
			if (!isset($progress[$i])) {
				$progress[$i] = array("count" => 0, "percent" => 0);
			}
			$total += $progress[$i]["count"];
		}
		$result2 = array();
		for ($i = 1; $i<=4;$i++) {
			$percent = $progress[$i]["count"] / $total * 100;
			$progress[$i]["percent"] = round($percent, 0);
			
			$result2[] = array("count" => $progress[$i]["count"], "percent" => $progress[$i]["percent"]);
		}
		return $result2;
	}
}
class ProjectManager extends BaseManager {

	/**
	 * saves object in recent objects list
	 * @param BaseModel $object
	 * @param User $user
	 */
	public function touchObject($object, $user) {
		$recentObject = new RecentObject();
		$recentObject->userId = $user->userId;
		$recentObject->objectType = $object->getObjectType();
		$recentObject->objectId= $object->getId();
		$this->repository->createRecentObject($recentObject);
		
	}
	
	
}
?>