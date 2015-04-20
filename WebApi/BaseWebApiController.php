<?

/**
 * Base class WebApi
 */
class BaseWebApiController {
	public $contextUser;
	public $mySqlRepository;
	public $config;

	public $gamePlayer;
	public $gameRepository;
	public $gameConfig;
	

	public function returnValidationState($validationState) {
		if ($validationState->isValid) {
		} else {
			header("HTTP/1.0 400 Bad Request");
		}
		exit(json_encode($validationState));
	}
	
	public function createApiResponseForObject($object, $validationState) {
		$apiResponse = new ApiResponse();
		$apiResponse->validationState = $validationState;
		if ($validationState->validationStateType == ValidationStateType::Success) {
			$apiResponse->object = $object;
		}
		return $apiResponse;
	}

	public function createApiResponseForArray($array, $validationState) {
		$apiResponse = new ApiResponse();
		$apiResponse->validationState = $validationState;
		if ($validationState->validationStateType == ValidationStateType::Success) {
			$apiResponse->data = $array;
		}
		return $apiResponse;
	}
}

?>