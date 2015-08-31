<?php
/**
 * System Manager
 *
 */
class SystemManager extends BaseManager {

	/**
	 * User that will be used to create wiki pages for stuff that is registered
	 * @var User
	 */
	private $adminUser;

	function __construct($repository) {
		parent::__construct($repository);
		$user = new User();
		$user->userId = 1;
		$this->adminUser = $user;
	}
	
	
	/**
	 * registers a new Resolution
	 * @param Resolution $model
	 * @return Resolution
	 */
	public function registerResolution($model) {
		/* is the model a model? */
		if (!is_object($model)) {
			throw new ParameterException("model is null");
		}
		if (get_class($model) != "Resolution") {
			throw new ParameterException("model is not of type Resolution");
		}

		/* model valid? */
		$modelException = new ModelException("Resolution contains validation errors");
		// check properties
		if ($model->name == "") {
			$modelException->addModelError("name", "empty");
		}
		// done
		if ($modelException->hasModelErrors()) {
			throw $modelException;
		}

		if (!$model->isNew()) {
			throw new ModelException("Resolution is not new, cannot be created again");
		}

		// finally: we can create the Resolution
		$model = $this->repository->createResolution($model);
		return $model;
	}


	/**
	 * registers a new Status
	 * @param Status $model
	 * @return Status
	 */
	public function registerStatus($model) {
		/* is the model a model? */
		if (!is_object($model)) {
			throw new ParameterException("model is null");
		}
		if (get_class($model) != "Status") {
			throw new ParameterException("model is not of type Status");
		}

		/* model valid? */
		$modelException = new ModelException("Status contains validation errors");
		// check properties
		if ($model->name == "") {
			$modelException->addModelError("name", "empty");
		}
		// done
		if ($modelException->hasModelErrors()) {
			throw $modelException;
		}

		if (!$model->isNew()) {
			throw new ModelException("Status is not new, cannot be created again");
		}

		// finally: we can create the Status
		$model = $this->repository->createStatus($model);
		return $model;
	}


	/**
	 * registers a new Role
	 * @param Role $model
	 * @return Role
	 */
	public function registerRole($model) {
		/* is the model a model? */
		if (!is_object($model)) {
			throw new ParameterException("model is null");
		}
		if (get_class($model) != "Role") {
			throw new ParameterException("model is not of type Role");
		}

		/* model valid? */
		$modelException = new ModelException("Role contains validation errors");
		// check properties
		if ($model->name == "") {
			$modelException->addModelError("name", "empty");
		}
		// done
		if ($modelException->hasModelErrors()) {
			throw $modelException;
		}

		if (!$model->isNew()) {
			throw new ModelException("Role is not new, cannot be created again");
		}

		// finally: we can create the Role
		$model = $this->repository->createRole($model);
		return $model;
	}

	/**
	 * registers a new User
	 * @param User $model
	 * @return User
	 */
	public function registerUser($model) {
		/* is the model a model? */
		if (!is_object($model)) {
			throw new ParameterException("model is null");
		}
		if (get_class($model) != "User") {
			throw new ParameterException("model is not of type User");
		}

		/* model valid? */
		$modelException = new ModelException("User contains validation errors");
		// check properties
		if ($model->name == "") {
			$modelException->addModelError("name", "empty");
		}
		// done
		if ($modelException->hasModelErrors()) {
			throw $modelException;
		}

		if (!$model->isNew()) {
			throw new ModelException("User is not new, cannot be created again");
		}

		$model->points = 0;
		$model->createdAt = time();
		$model->isConfirmed = 0;
		$model->confirmationKey = com_create_guid();
		$model = $this->repository->createUser($model);
		return $model;
	}

	/**
	 * registers a new DocumentType
	 * @param DocumentType $model
	 * @return DocumentType
	 */
	public function registerDocumentType($model) {
		/* is the model a model? */
		if (!is_object($model)) {
			throw new ParameterException("model is null");
		}
		if (get_class($model) != "DocumentType") {
			throw new ParameterException("model is not of type " + DocumentType);
		}

		/* model valid? */
		$modelException = new ModelException("Model contains validation errors");
		// check properties
		if ($model->name == "") {
			$modelException->addModelError("name", "empty");
		}

		// done
		if ($modelException->hasModelErrors()) {
			throw $modelException;
		}

		if (!$model->isNew()) {
			throw new ModelException("model is not new, cannot be created again");
		}

		// finally: we can create the model
		$model = $this->repository->createDocumentType($model);
		return $model;
	}


	/**
	 * registers a new object type in the system
	 * @param ObjectType $objectType
	 * @param ValidationState $validationState
	 * @return ObjectType
	 */
	public function registerObjectType($model) {
		/* is the model a model? */
		if (!is_object($model)) {
			throw new ParameterException("model is null");
		}
		if (get_class($model) != "ObjectType") {
			throw new ParameterException("model is not of type " + ObjectType);
		}

		/* authorized? */
		$isAuthorized = true;
		// todo: auth check
		if (!$isAuthorized) {
			throw new UnauthorizedException();
		}

		/* model valid? */
		$modelException = new ModelException("Model contains validation errors");
		// check properties
		if ($model->name == "") {
			$modelException->addModelError("name", "empty");
		}
		// done
		if ($modelException->hasModelErrors()) {
			throw $modelException;
		}

		// finally: we can create the model
		$model = $this->repository->createObjectType($model);
		return $model;
	}
	/**
	 * registers a new action in the system
	 * @param Action $action
	 * @param ValidationState $validationState
	 * @return Action
	 */
	public function registerAction($model) {
		/* is the model a model? */
		if (!is_object($model)) {
			throw new ParameterException("model is null");
		}
		if (get_class($model) != "Action") {
			throw new ParameterException("model is not of type Action");
		}

		/* model valid? */
		$modelException = new ModelException("Model contains validation errors");
		// check properties
		if ($model->name == "") {
			$modelException->addModelError("name", "empty");
		}
		// done
		if ($modelException->hasModelErrors()) {
			throw $modelException;
		}

		// finally: we can create the model
		$model = $this->repository->createAction($model);
		return $model;
	}

	/**
	 * Creates a new BadgeTypes
	 * @param BadgeType $model
	 * @return BadgeType
	 */
	public function registerBadgeType($model) {
		/* is the model a model? */
		if (!is_object($model)) {
			throw new ParameterException("model is null");
		}
		if (get_class($model) != "BadgeType") {
			throw new ParameterException("model is not of type BadgeTypes");
		}

		/* authorized? */
		$isAuthorized = true;
		// todo: auth check
		if (!$isAuthorized) {
			throw new UnauthorizedException();
		}

		/* model valid? */
		$modelException = new ModelException("BadgeType contains validation errors");
		// check properties
		if ($model->name == "") {
			$modelException->addModelError("name", "empty");
		}
		// done
		if ($modelException->hasModelErrors()) {
			throw $modelException;
		}


		// finally: we can create the BadgeType
		$model = $this->repository->createBadgeType($model);
		return $model;
	}

	/**
	 * registers a badge in the system
	 * @param Badge $model
	 * @return Badge
	 */
	public function registerBadge($model) {
		/* is the model a model? */
		if (!is_object($model)) {
			throw new ParameterException("model is null");
		}
		if (get_class($model) != "Badge") {
			throw new ParameterException("model is not of type Badge");
		}

		/* model valid? */
		$modelException = new ModelException("Badge contains validation errors");
		// check properties
		if ($model->name == "") {
			$modelException->addModelError("name", "empty");
		}
		// done
		if ($modelException->hasModelErrors()) {
			throw $modelException;
		}

		if (!$model->isNew()) {
			throw new ModelException("Badge is not new, cannot be created again");
		}
		$model->wikiName = $model->createWikiName();
		$model->createdByUserId = $this->adminUser->userId;
		// finally: we can create the Badge
		$this->repository->beginTransaction();

		$model = $this->repository->createBadge($model);
		// create wikipage for badge
		$documentManager = new DocumentManager($this->repository);
		$raw = "#  " . $model->name. "\n";
		$raw .= $model->description;
		$document = $documentManager->createWikiPage($model, $this->adminUser, $raw);
		$this->repository->commit();

		return $model;
	}



	/**
	 * Creates a new BadgeIssuer
	 * @param BadgeIssuer $model
	 * @return BadgeIssuer
	 */
	public function registerBadgeIssuer($model) {
		/* is the model a model? */
		if (!is_object($model)) {
			throw new ParameterException("model is null");
		}
		if (get_class($model) != "BadgeIssuer") {
			throw new ParameterException("model is not of type BadgeIssuer");
		}

		/* authorized? */
		$isAuthorized = true;
		// todo: auth check
		if (!$isAuthorized) {
			throw new UnauthorizedException();
		}

		/* model valid? */
		$modelException = new ModelException("BadgeIssuer contains validation errors");
		// check properties
		if ($model->name == "") {
			$modelException->addModelError("name", "empty");
		}
		// done
		if ($modelException->hasModelErrors()) {
			throw $modelException;
		}

		// finally: we can create the BadgeIssuer
		$model = $this->repository->createBadgeIssuer($model);
		return $model;
	}

	/**
	 * creates a new parameter
	 * @param Parameter $model
	 * @return Parameter
	 */
	public function registerParameter($model) {
		/* is the model a model? */
		if (!is_object($model)) {
			throw new ParameterException("model is null");
		}
		if (get_class($model) != "Parameter") {
			throw new ParameterException("model is not of type Parameter");
		}
	
		/* model valid? */
		$modelException = new ModelException("Parameter contains validation errors");
		// check properties
		if ($model->name == "") {
			$modelException->addModelError("name", "empty");
		}
		// done
		if ($modelException->hasModelErrors()) {
			throw $modelException;
		}
	
		if (!$model->isNew()) {
			throw new ModelException("Parameter is not new, cannot be created again");
		}
	
		$model = $this->repository->createParameter($model);
		return $model;
	}
	
	/**
	 * registers a new Tile
	 * @param Tile $model
	 * @return Tile
	 */
	public function registerTile($model) {
		/* is the model a model? */
		if (!is_object($model)) {
			throw new ParameterException("model is null");
		}
		if (get_class($model) != "Tile") {
			throw new ParameterException("model is not of type Tile");
		}
		
		// todo: file handling
		// a tile needs to have a folder, files, etc.
		// plugin style
		// todo: checks: tilename must be unique, or update version number
		
		/* authorized? */
		$isAuthorized = true;
		// todo: auth check
		if (!$isAuthorized) {
			throw new UnauthorizedException();
		}
		
		$model->wikiName = $model->createWikiName();
		
		$this->repository->beginTransaction();
		
		/* model valid? */
		$modelException = new ModelException("Tile contains validation errors");
		// check properties
		if ($model->name == "") {
			$modelException->addModelError("name", "empty");
		}
		// done
		if ($modelException->hasModelErrors()) {
			throw $modelException;
		}
		
		if (!$model->isNew()) {
			throw new ModelException("Tile is not new, cannot be created again");
		}
		
		// finally: we can create the Tile
		$this->repository->beginTransaction();
		$model = $this->repository->createTile($model);
		// add parameters
		$i =0;
		foreach ($model->parameters as $parameter) {
			$parameter->objectTypeId = ObjectTypeEnum::Tile;
			$parameter = $this->repository->createParameter($parameter);
			$model->parameters[$i] = $parameter;
			$i++;
		}
		
		// create wikipage for tile
		$documentManager = new DocumentManager($this->repository);
		$raw = "#  " . $model->name. "\n";
		$raw .= $model->description;
		$validationState = new ValidationState();
		$document = $documentManager->createWikiPage($model, $this->adminUser, $raw);
		$this->repository->commit();
		
		return $model;
		
	}

}
?>
