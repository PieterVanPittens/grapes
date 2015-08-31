<?php
class DateService extends BaseService {

	/**
	 * Creates a new Date
	 * @param Date $model
	 * @return Date
	 */
	public function createDate($model) {
		/* is the model a model? */
		if (!is_object($model)) {
			throw new ParameterException("model is null");
		}
		if (get_class($model) != "Date") {
			throw new ParameterException("model is not of type Date");
		}

		/* authorized? */
		$isAuthorized = true;
		// todo: auth check
		if (!$isAuthorized) {
			throw new UnauthorizedException();
		}

		/* model valid? */
		$modelException = new ModelException("Date contains validation errors");
		// check properties
		if ($model->summary == "") {
			$modelException->addModelError("summary", "empty");
		}
		// done
		if ($modelException->hasModelErrors()) {
			throw $modelException;
		}

		if (!$model->isNew()) {
			throw new ModelException("Date is not new, cannot be created again");
		}

		// finally: we can create the Date
		
		$model->guid = com_create_guid();
		$model = $this->repository->createDate($model);
		return $model;
	}

	/**
	 * retrieves all Dates
	 * @return Array
	 */
	public function getDates() {
		$models = $this->repository->getDates();
		return $models;
	}
}

?>