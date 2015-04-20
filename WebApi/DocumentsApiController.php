<?

class DocumentsApiController extends BaseWebApiController {

	/**
	 * retrieves a Document by id
	 * @param array $parameters
	 * @return Document */
	public function getDocumentById($parameters) {
		$id = $parameters["id"];
		$service = new DocumentService($this->contextUser, $this->repository);
		$validationState = new ValidationState();
		$model = $service->getDocumentById($id, $validationState);
		if ($model == null) {
			return $validationState;
		} else {
			return $model;
		}
	}
	
	/**
	 * retrieves a Document by name
	 * @param array $parameters
	 * @return Document
	 */
	public function getDocumentByName($parameters) {
		$name = $parameters["name"];
		$service = new DocumentService($this->contextUser, $this->repository);
		$validationState = new ValidationState();
		$model = $service->getDocumentByName($name, $validationState);
		if ($model == null) {
			return $validationState;
		} else {
			return $model;
		}
	}
	
	/**
	 * retrieves a DocumentContent by id
	 * @param array $parameters
	 * @return DocumentContent
	 */
	public function getDocumentContentById($parameters) {
		$id = $parameters["id"];
		$service = new DocumentService($this->contextUser, $this->repository);
		$validationState = new ValidationState();
		$model = $service->getDocumentContentById($id, $validationState);
		if ($model == null) {
			return $validationState;
		} else {
			return $model;
		}
	}

	/**
	 * retrieves a raw content DocumentContent by id
	 * @param array $parameters
	 * @return string
	 */
	public function getDocumentContentRawById($parameters) {
		$id = $parameters["id"];
		$service = new DocumentService($this->contextUser, $this->repository);
		$validationState = new ValidationState();
		$raw = $service->getDocumentContentRawById($id, $validationState);
		if ($raw == null) {
			return $validationState;
		} else {
			return $raw;
		}
	}

	/**
	 * retrieves wiki content by id
	 * @param array $parameters
	 * @return string
	 */
	public function getDocumentContentWikiById($parameters) {
		$id = $parameters["id"];
		$service = new DocumentService($this->contextUser, $this->repository);
		$validationState = new ValidationState();
		$raw = $service->getDocumentContentWikiById($id, $validationState);
		if ($raw == null) {
			return $validationState;
		} else {
			return $raw;
		}
	}
	
	
	
}


?>