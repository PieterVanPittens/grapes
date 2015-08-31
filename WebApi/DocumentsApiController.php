<?php

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
		$model = $service->getDocumentByName($name);
		return $model;
	}
	
	/**
	 * retrieves a DocumentContent by id
	 * @param array $parameters
	 * @return DocumentContent
	 */
	public function getDocumentContentById($parameters) {
		$id = $parameters["id"];
		$service = new DocumentService($this->contextUser, $this->repository);
		$model = $service->getDocumentContentById($id);
		return $model;
	}

	/**
	 * retrieves wiki content by id
	 * @param array $parameters
	 * @return string
	 */
	public function getDocumentContentWikiById($parameters) {
		$id = $parameters["id"];
		$service = new DocumentService($this->contextUser, $this->repository);
		$html = $service->getDocumentContentWikiById($id);
		return $html;
	}
	
	/**
	 * creates a new document content, i.e. new content version
	 * @param unknown $parameters
	 */
	public function createDocumentContent($parameters) {
		$body = file_get_contents('php://input');
		$content = DocumentContent::createModelFromJson($body);
		
		$service = new DocumentService($this->contextUser, $this->repository);
		$result = $service->createNewContentVersion($content);
		return $result;
	}

	/**
	 * creates a new document including first version of content
	 * used for creating wiki pages
	 * @param unknown $parameters
	 */
	public function createDocument($parameters) {
		$body = file_get_contents('php://input');
		$document = Document::createModelFromJson($body);
	
		$service = new DocumentService($this->contextUser, $this->repository);
		$result = $service->createDocument($document);
		return $result;
	}
	
}


?>