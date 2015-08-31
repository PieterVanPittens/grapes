<?php

class UsersApiController extends BaseWebApiController {

	/**
	 * only dummy, since authentication is always handled in index.php already
	 */
	public function authenticate() {
		return null;
	}

	public function getAllRecentObjects() {
		$service = new UserService($this->contextUser, $this->repository);
		$users = $service->getAllRecentObjects();
		$data["data"] = $users;
		return  $data;
	}
	
	
	public function Get() {
		$service = new UserService($this->contextUser, $this->repository);
		$users = $service->getUsers();
		$data["data"] = $users;
		return  $data;
	}

	public function getUserById($parameters) {
		$userId = $parameters["userId"];
		$service = new UserService($this->contextUser, $this->repository);
		$user = $service->getUserById($userId);
		return $user;
	}

	public function getUserPreview($parameters) {
		$userId = $parameters["id"];
		$service = new UserService($this->contextUser, $this->repository);
		$user = $service->getUserPreview($userId);
		return $user;
	}
	
	
	public function getUserBadges($parameters) {
		$userId = $parameters["id"];
		$service = new BadgeService($this->contextUser, $this->repository);
		$userBadges = $service->getUserBadges($userId);
		return $userBadges;
	}
	
	/**
	 * retrieves all projects of a user
	 * @param unknown $parameters
	 * @return string
	 */
	public function getUserProjects($parameters) {
		$id = $parameters["id"];
		$service = new UserService($this->contextUser, $this->repository);
		$projects = $service->getUserProjects($id);
		$data["data"] = $projects;
		return $data;
	}
	
	/*
	 * upload picture of user
	 */
	public function uploadPicture($parameters) {
		$userId = $parameters["userId"];
		
		$uploaddir = __DIR__ ."/../upload/";
		$pictureFile = $uploaddir ."/". $userId;

		if (move_uploaded_file($_FILES['file']['tmp_name'], $pictureFile)) {
			
			$userService = new UserService($this->contextUser, $this->repository);
			$userService->savePicture($pictureFile);
		}
		
	}
	
	
	
	
	
	
	
	public function create() {
		$body = file_get_contents('php://input');
		
		$user = User::createModelFromJson($body);
		$validationState = new ValidationState();
		$service = new UserService($this->contextUser, $this->repository);
		$service->createUser($user, $validationState);
		$this->returnValidationState($validationState);
	}
}


?>