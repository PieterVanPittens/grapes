<?php
class WikiPagePlugin extends BaseTilePlugin {

	private $html;
	
	public function initialize() {
		$documentService = new DocumentService($this->contextUser, $this->repository);
		$validationState = new ValidationState();
		$documentId = $this->dashboardTile->parameterValues["documentId"]->value;
		$this->html = $documentService->getDocumentWikiById($documentId, $validationState);
	}
	
	public function render() {
		echo $this->html;
	}
}
?>