<?php
use GaME\iModel;
class ParameterService extends BaseService {
	/**
	 * internal manager
	 * @var ParameterManager
	 */
	private $manager;
	
	function __construct($contextUser, $repository) {
		parent::__construct($contextUser, $repository);
		$this->manager = new ParameterManager($repository);
	}
	/**
	 * Creates a new parameter
	 * @param Parameter $parameter
	 * @return Parameter
	 */
	public function createParameter($parameter) {
		/* authorized? */
		$isAuthorized = true;
		// todo: auth check
		if (!$isAuthorized) {
			throw new UnauthorizedException();
		}
		
		return $this->manager->createParameter($parameter, $this->contextUser);
	}
}

class ParameterManager extends BaseManager {


	
	/**
	 * creates the initial parameter values for an object
	 * @param BaseModel $object
	 */
	public function createParameterValuesForObject($object) {
		if ($object->getObjectType() == ObjectTypeEnum::DashboardTile) {
			$parameters = $this->repository->getParametersOfObjectType(ObjectTypeEnum::Tile);
		} else {
			$parameters = $this->repository->getParametersOfObjectType($object->getObjectType());
		}
		foreach ($parameters as $parameter) {
			$parameterValue = new ParameterValue();
			$parameterValue->objectId = $object->getId();
			$parameterValue->objectTypeId = $object->getObjectType();
			$parameterValue->value = $parameter->defaultValue;
			$parameterValue->parameterId = $parameter->parameterId;
			$parameterValue = $this->repository->createParameterValue($parameterValue);
		}
	}
	
	/**
	 * sets parameter value for an object
	 * @param Parameter $parameter
	 * @param iModel $object
	 * @param string $value
	 * @return ParameterValue
	 */
	public function setParameterValueForObject($parameter, $object, $value) {
		if ($parameter == null) {
			throw new ParameterException("parameter is null");
		}
		if ($object == null) {
			throw new ParameterException("object is null");
		}
		$parameterValue = $this->repository->getObjectParameterValue($parameter->parameterId, $object->getObjectType(), $object->getId());
		if ($parameterValue == null) {
			$parameterValue = new ParameterValue();
			$parameterValue->objectId = $object->getId();
			$parameterValue->objectTypeId = $object->getObjectType();
			$parameterValue->parameterId = $parameter->parameterId;
			$parameterValue->value = $value;
			
			$parameterValue = $this->repository->createParameterValue($parameterValue);
		} else {
			$parameterValue->value = $value;
			$parameterValue = $this->repository->updateParameterValue($parameterValue);
		}
		return $parameterValue;
	}

	/**
	 * sets parameter value for an object by parametername
	 * @param string $parameterName
	 * @param iModel $object
	 * @param string $value
	 * @return ParameterValue
	 */
	public function setParameterValueForObjectByName($parameterName, $object, $value) {
		if ($object == null) {
			throw new ParameterException("object is null");
		}
		$parameter = $this->repository->getParameterByName($parameterName);
		$parameterValue = $this->setParameterValueForObject($parameter, $object, $value);
		return $parameterValue;
	}
	

}

?>