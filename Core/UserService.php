<?php

class UserService extends BaseService {

	/**
	 * retrieves preview object for a user
	 * @param int $id
	 * @return ObjectPreview
	 */
	public function getUserPreview($id) {
		$model = $this->repository->getUserById($id);
		if ($model == null) {
			throw new NotFoundException("User $id does not exist");
		}
		$preview = new ObjectPreview();
		$preview->objectType = ObjectTypeEnum::User;
		$preview->objectName = $model->name;
		$preview->addProperty("name", "strName", $model->name);
		$preview->addProperty("description", "strDescription", $model->description);
		$preview->addProperty("created", "strCreated", "todo...");
		$preview->addProperty("xyz", "strXYZ", "todo...");
		return $preview;
	}

	/**
	 * gets all projects of a user
	 * @param unknown $userId
	 */
	public function getUserProjects($userId) {
		$projects = $this->repository->getUserProjects($userId);
		return $projects;
	}

	/**
	 * retrieves user stats
	 * @return UserStats
	 */
	public function getStats() {

		return $this->repository->getUserStats();
	}
	/**
	 * retrieves user by id
	 */
	public function getUserById($id) {
		$model = $this->repository->GetUserById($id);
		return $model;
	}

	/**
	 * retrieves user by name
	 */
	public function getUserByName($userName) {
		$model = $this->repository->GetUserByName($userName);
		return $model;
	}

	/**
	 * retrieves all users
	 */
	public function getUsers() {
		$model = $this->repository->getUsers();
		return $model;
	}


	/**
	 * Creates a new User
	 * @param User $model
	 * @return User
	 */
	public function createUser($model) {
		/* authorized? */
		$this->securityManager->checkAdminAuthorization($this->contextUser);


		$systemManager = new SystemManager($this->repository);
		$model->wikiName = $model->createWikiName();
		$model->createdAt = time();
		$model->createdByUserId = $this->contextUser->userId;
		$model = $systemManager->registerUser($model);


		return $model;
	}

	/**
	 * confirms as a user with its confirmation Key
	 * @param User $user
	 * @param string $confirmationKey
	 * @return User
	 */
	public function confirmUser($user, $confirmationKey) {
		/* is the model a model? */
		if (!is_object($user)) {
			throw new ParameterException("user is null");
		}
		if (get_class($user) != "User") {
			throw new ParameterException("user is not of type User");
		}

		// security check: confirm your own user only OR admin can confirm all users
		if ($this->contextUser->userId != $user->userId) {
			$this->securityManager->checkAdminAuthorization($this->contextUser);
		}
		if ($user->isConfirmed) {
			throw new ServiceException("user is already confirmed");
		}

		$this->repository->beginTransaction();

		$user = $this->securityManager->confirmUser($user, $confirmationKey);

		// create wikipage for user
		$documentManager = new DocumentManager($this->repository);
		$raw = "# User " . $user->name. "\n";
		$raw .= "This is the wiki page of User " . $user->name;
		$validationState = new ValidationState();
		$document = $documentManager->createWikiPage($user, $this->contextUser, $raw);

		// create dashboard for user
		$dashboardManager = new DashboardManager($this->repository);
		$dashboard = $dashboardManager->createDashboardForObject($user, $this->contextUser);

		// set homedashboard for user
		$user->homeDashboardId = $dashboard->dashboardId;
		$this->repository->updateUserHomeDashboardId($user);
		
		// configure dashboard
		$tile = $dashboardManager->getTileByName("WikiPage");
		$dashboardTile = $dashboardManager->addTileToDashboard($tile, $dashboard);
		$parameterManager = new ParameterManager($this->repository);
		$parameterManager->setParameterValueForObject($tile->parameters[0], $dashboardTile, $document->documentId);

		$pointsUpdate = $this->logActionByName("confirm-user", $user);

		$pointsNewTotal = $this->repository->getUserTotalPointsById($this->contextUser->userId);

		$this->repository->commit();




		return $user;

	}

	public function validateUser($user, &$validationState) {

	}
	
	
	/**
	 * saves file as user picture
	 * @param string $imagePath path to file on server
	 */
	public function savePicture($pictureFile) {
		
		$userManager = new UserManager($this->repository);
		$userManager->savePicture($this->contextUser, $pictureFile);
		
	}

	
}

class UserManager extends BaseManager {

	/**
	 * saves file as user picture
	 * @param User $user
	 * @param string $imagePath path to file on server
	 */
	public function savePicture($user, $pictureFile) {

		$uploaddir = __DIR__ ."/../upload/profiles/";
		
		
		$uploadfileMedium = $uploaddir ."/profile-". $user->userId."-medium.jpg";
		$uploadfileLarge = $uploaddir ."/profile-". $user->userId."-large.jpg";
		$uploadfileSmall = $uploaddir ."/profile-". $user->userId."-small.jpg";
	
		$this->resizePicture($pictureFile, $uploadfileLarge, 100, 100);
		$this->resizePicture($pictureFile, $uploadfileMedium, 50, 50);
		$this->resizePicture($pictureFile, $uploadfileSmall, 30, 30);
	
	
	}
	
	
	/**
	 * Image resize
	 * @param int $width
	 * @param int $height
	 */
	function resizePicture($sourceFile, $targetFile, $width, $height){
		/* Get original image x y*/
		list($w, $h) = getimagesize($sourceFile);
		/* calculate new image size with ratio */
		$ratio = max($width/$w, $height/$h);
		$h = ceil($height / $ratio);
		$x = ($w - $width / $ratio) / 2;
		$w = ceil($width / $ratio);
		/* new file name */
		$path = $targetFile;
		/* read binary data from image file */
		$imgString = file_get_contents($sourceFile);
		/* create image from string */
		$image = imagecreatefromstring($imgString);
		$tmp = imagecreatetruecolor($width, $height);
		imagecopyresized($tmp, $image,
		0, 0,
		$x, 0,
		$width, $height,
		$w, $h);
		/* Save image */
		//switch ($_FILES['image']['type']) {
		//case 'image/jpeg':
		imagejpeg($tmp, $path, 100);
		//break;
		//case 'image/png':
		//	imagepng($tmp, $path, 0);
		//	break;
		//case 'image/gif':
		//	imagegif($tmp, $path);
		//	break;
		//default:
		//	exit;
		//	break;
		//}
		/* cleanup memory */
		imagedestroy($image);
		imagedestroy($tmp);
		return $path;
	}
	
	
}

?>