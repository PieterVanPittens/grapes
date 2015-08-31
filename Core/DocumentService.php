<?php


/**
 * Document Service
 * @package Services
 */
class DocumentService extends BaseService {
	
	/**
	 * gets Document by id
	 * @param Document $id
	 * @param ValidationState $validationState
	 * @return Document */
	public function getDocumentById($id, &$validationState) {
		if ($id == "") {
			$validationState = ValidationHelper::getParameterEmptyState("Id");
			return null;
		}
		$model = $this->repository->getDocumentById($id, $validationState);
		if ($model == null) {
			$validationState = ValidationHelper::getObjectNotFoundState($id, "Document");
		}
		return $model;
	}
	
	/**
	 * gets Document by name
	 * @param string $name
	 * @return Document
	 */
	public function getDocumentByName($name) {
		if ($name == "") {
			throw new ParameterException("name is empty");
		}
		$model = $this->repository->getDocumentByName($name);
		if ($model == null) {
			throw new NotFoundException($name);
		}
		return $model;
	}

	/**
	 * gets DocumentContent by id
	 * @param int $id
	 * @return DocumentContent
	 */	
	public function getDocumentContentById($id) {
		if ($id == "") {
			throw new ParameterException("id is empty");
		}
		$model = $this->repository->getDocumentContentById($id);
		if ($model == null) {
			throw new NotFoundException($id);
		}
		return $model;
	}

	/**
	 * gets wiki by document content id
	 * @param int $documentContentId
	 * @return string
	 */
	public function getDocumentContentWikiById($documentContentId) {
		$content = $this->getDocumentContentById($documentContentId);
		$parser = new GrapesMarkdown();
		$html = $parser->parse($content->content);
		return $html;
	}

	/**
	 * gets wiki by document id
	 * will deliver content of latest version
	 * @param int $documentId
	 * @param ValidationState $validationState
	 * @return string
	 */
	public function getDocumentWikiById($documentId, &$validationState) {
		$document = $this->repository->getDocumentById($documentId);
		// todo: check
		
		$documentContentId = $document->latestContentId;
		return $this->getDocumentContentWikiById($documentContentId);

	}
	
	/**
	 * Creates a new Document
	 * @param Document $model
	 * @return Document
	 */
	public function createDocument($model) {
		/* is the model a model? */
		if (!is_object($model)) {
			throw new ParameterException("model is null");
		}
		if (get_class($model) != "Document") {
			throw new ParameterException("model is not of type Document");
		}
	
		/* authorized? */
		$this->securityManager->checkAdminAuthorization($this->contextUser);
	
		/* model valid? */
		$modelException = new ModelException("Document contains validation errors");
		// check properties
		if ($model->name == "") {
			$modelException->addModelError("name", "empty");
		}
		// done
		if ($modelException->hasModelErrors()) {
			throw $modelException;
		}
	
		if (!$model->isNew()) {
			throw new ModelException("Document is not new, cannot be created again");
		}
	
		// finally: we can create the Document
		$model->createdByUserId = $this->contextUser->userId;
		$model->createdAt = time();
		$model->version = 1;
		$model->latestContentId = 0;
		$model->objectId = 0;
		$model->objectTypeId = ObjectTypeEnum::Document;
		$model->wikiName = $model->createWikiName();
		$this->repository->beginTransaction();
		$model = $this->repository->createDocument($model);
	
		$pointsUpdate = $this->logActionByName("create-document", $model);
		$pointsNewTotal = $this->repository->getUserTotalPointsById($this->contextUser->userId);
		$message = "Document created";
		$actionResult = new ActionResult($model, $pointsUpdate, $pointsNewTotal, $message);
		
		// if content is set -> create content as well
		if (isset($model->content)) {
			$content = new DocumentContent();
			$content->documentId = $model->documentId;
			$content->createdAt = time();
			$content->createdByUserId = $this->contextUser->userId;
			$content->content = $model->content;
			$content->version = 1;
			$content = $this->repository->createDocumentContent($content);
			$model->latestContentId = $content->documentContentId;
			$model = $this->repository->updateDocument($model);
		}
		
		$this->repository->commit();
		return $actionResult;
	}
	
	/**
	 * updates a document, i.e. create a new content version
	 * @param DocumentContent $content
	 */
	public function createNewContentVersion($content) {
		if (!is_object($content)) {
			throw new ParameterException("content is null");
		}
		if (get_class($content) != "DocumentContent") {
			throw new ParameterException("content is not of type DocumentContent");
		}

		// todo: auth check
		$content->createdAt = time();
		$content->createdByUserId = $this->contextUser->userId;
		
		$this->repository->beginTransaction();
		$content = $this->repository->createDocumentContent($content);

		$document = $this->repository->getDocumentById($content->documentId);
		$document->latestContentId = $content->documentContentId;
		$document = $this->repository->updateDocument($document);

		$pointsUpdate = $this->logActionByName("update-document", $document);
		$pointsNewTotal = $this->repository->getUserTotalPointsById($this->contextUser->userId);
		$message = "New Content Version created";
		$actionResult = new ActionResult($document, $pointsUpdate, $pointsNewTotal, $message);
		$this->repository->commit();
		
		return $actionResult;		
	}
	

}

class DocumentManager extends BaseManager {
	/**
	 * creates a new wikipage
	 * wikipage needs to be linked any kind of object
	 * @param object $object
	 * @param User $user
	 * @param string $raw
	 * @return Document
	 */
	public function createWikiPage($object, $user, $raw) {
		$document = new Document();
		$document->createdAt = time();
		$document->createdByUserId = $user->userId;
		$document->documentTypeId = DocumentTypeEnum::Wiki;
		$document->name = $object->wikiName;
		$document->wikiName = $object->wikiName;
		$document->objectId = $object->getId();
		$document->objectTypeId = $object->getObjectType();
		$document->version = 1;
		$document->latestContentId = 0;
		$document = $this->repository->createDocument($document);

		$documentContent = new DocumentContent();
		$documentContent->content = $raw;
		$documentContent->createdAt = time();
		$documentContent->createdByUserId = $user->userId;
		$documentContent->documentId = $document->documentId;
		$documentContent->version = 1;
		$documentContent = $this->repository->createDocumentContent($documentContent);
		$document->latestContentId = $documentContent->documentContentId;
		
		$document = $this->repository->updateDocument($document);
		return $document;
	}
	

}


?>