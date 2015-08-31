<?php

/**
 * Base class WebApi
 */
class BaseWebApiController {
	public $contextUser;
	public $mySqlRepository;
	public $config;	

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
	
	/**
	 * gets the value of a request parameter that is mandatory to be provided by a request
	 * throws WebApiException if Parameter does not exist in _REQUEST
	 * @param string $name
	 */
	public function getMandatoryParameter($name) {
		if (isset($_REQUEST[$name])) {
			return $_REQUEST[$name];
		} else {
			throw new ParameterException("Parameter is missing in request: '$name'");
		}
	}
}

?>