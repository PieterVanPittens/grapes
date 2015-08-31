<?php
class IssuesApiController extends BaseWebApiController {

	/**
	 * creates a new issue
	 * @param unknown $parameters
	 */
	public function createIssue($parameters) {
		$body = file_get_contents('php://input');
		
		$issue = Issue::createModelFromJson($body);
		$service = new IssueService($this->contextUser, $this->repository);
		$actionResult = $service->createIssue($issue);
		
		return $actionResult;
	}
	
	/**
	 * gets all issues that the user has access to
	 */
	public function getIssues() {
		$service = new IssueService($this->contextUser, $this->repository);
		$issues = $service->getIssues();
		$data["data"] = $issues;
		return $data;
	}
	
	/**
	 * retrieves an issue by key
	 * @param unknown $parameters
	 * @return string
	 */
	public function getIssueByKey($parameters) {
		$key = $parameters["key"];
	
		$service = new IssueService($this->contextUser, $this->repository);
		$issue = $service->getIssueByKey($key);
		return $issue;
	}
	
	public function updateIssueField($parameters) {
		$key = $parameters["key"];
		
		$body = file_get_contents('php://input');
		$json = json_decode($body);
		$vars = get_object_vars($json);
		$fieldName= key($vars);
		$value = $vars[$fieldName];
		$service = new IssueService($this->contextUser, $this->repository);
		$issue = $service->getIssueByKey($key);
		
		$result = $service->updateIssueField($issue, $fieldName, $value);
		return $result;		
	}
	
	/*
	 * upload file for creating issues
	 */
	public function createIssuesFromFile($parameters) {
		$id = $parameters["id"];
		$uploaddir = __DIR__ ."/../upload/";
		$issueFile = $uploaddir .com_create_guid();
		$fileInfo = $_FILES["file"];
		if (move_uploaded_file($_FILES['file']['tmp_name'], $issueFile)) {
			$service = new ProjectService($this->contextUser, $this->repository);
			$project = $service->getProjectById($id);
			$service = new IssueService($this->contextUser, $this->repository);
			$actionResults = $service->createIssuesFromFile($project, $issueFile, $fileInfo);
			return $actionResults;
		}
		return null;	
	}
}

?>