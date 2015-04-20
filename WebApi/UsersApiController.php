<?

class UsersApiController extends BaseWebApiController {

	public function Get() {
		$service = new UserService($this->contextUser, $this->repository);
		$users = $service->getUsers();
		return  json_encode($users);
	}

	public function getUserById($parameters) {
		$userId = $parameters["userId"];
		$service = new UserService($this->contextUser, $this->repository);
		$user = $service->getUserById($userId);
		if ($user == null) {
			return "HTTP/1.0 404 Not Found";
		} else {
			return  json_encode($user);
		}
	}

	
	
	public function Create() {
		$body = file_get_contents('php://input');
		
		$user = User::createModelFromJson($body);
		$validationState = new ValidationState();
		$service = new UserService($this->contextUser, $this->repository);
		$service->createUser($user, $validationState);
		$this->returnValidationState($validationState);
	}
}


?>