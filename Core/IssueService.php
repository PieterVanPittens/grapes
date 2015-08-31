<?php
class IssueService extends BaseService {

	
	/**
	 * updates one field of an issue
	 * used for inline edit in client
	 * @param Issue $issue
	 * @param string $fieldName
	 * @param string $value
	 * @param ActionResult
	 */
	public function updateIssueField($issue, $fieldName, $value) {

		/* authorized? */
		$project = $this->repository->getProjectById($issue->projectId);
		$this->securityManager->checkLeadOrMemberAuthorization($this->contextUser, $project);

		$actionResult = null;
		switch($fieldName) {
			case "subject":
				$issue->subject = $value;
				$this->repository->updateIssueMetadata($issue);
				$actionResult = new ActionResult($issue, 0, null, "subject changed");
				break;
			case "statusId":

				$statusOld = $this->repository->getStatusById($issue->statusId);
				$statusNew = $this->repository->getStatusById($value);
				
				$this->repository->beginTransaction();

				$issue->statusId = $value;
				$this->repository->updateIssueStatus($issue);
				$pointsUpdate = $this->logActionByName("change-issue-status", $issue);

				$projectManager = new ProjectManager($this->repository);
				$projectManager->touchObject($issue, $this->contextUser);
				
				// create feed item for issue wall
				$activityStreamManager = new ActivityStreamManager($this->repository);
				$item = new FeedItem();
				$item->feed = "changed status of ".$issue->issueNr." from ".$statusOld->name." to ".$statusNew->name;
				$item->createdByUserId = $this->contextUser->userId;
				$item->targetObjectId = $issue->issueId;
				$item->targetObjectTypeId = ObjectTypeEnum::Issue;
				$item->objectId = $issue->projectId;
				$item->objectTypeId = ObjectTypeEnum::Project;
				$item->verb = "change";
				$item = $activityStreamManager->createFeedItem($item);
				
				
				$this->repository->commit();
				
				$pointsNewTotal = $this->repository->getUserTotalPointsById($this->contextUser->userId);
				$message = "status changed";
				$actionResult = new ActionResult($issue, $pointsUpdate, $pointsNewTotal, $message);
				
				break;
		}
		
		return $actionResult;
	}
	
	
	/**
	 * gets list of issues filtered by filter
	 * @param string $filter
	 * @return unknown
	 */
	public function getIssuesByFilter($filter) {
		$filterInternal = $this->convertFilter($filter);
		$issues = $this->repository->getIssues($this->contextUser->userId, $filterInternal);
		return $issues;
	}
	
	
	/**
	 * converts filter from display format to internal format (db)
	 */
	public function convertFilter($filterDisplay) {
		$filterInternal = strtolower($filterDisplay);
		$filterDisplay = strtolower($filterDisplay);
	
		$found = preg_match_all("((\w+)\s*=\s*(\w+))", $filterDisplay, $matches);
		if ($found) {
			$i = 0;
			foreach($matches[0] as $match) {
				$fullMatch = $match;
				$keyWord = $matches[1][$i];
				$name = $matches[2][$i];

				switch ($keyWord) {
					case "project":
						// resolve projectName
						$id = "";
						$project = $this->repository->getProjectByName($name);
						if ($project == null) {
							throw new Exception("Project '$name' does not exist");
						} else {
							$id = $project->projectId;
						}
						$sqlFilter = "i.project_id = $id";
						$filterInternal = str_replace($fullMatch, $sqlFilter, $filterInternal);
					break;
					case "issuetype":
						// resolve issuetype
						$id = "";
						switch ($name) {
							case "issue":
							$id = IssueTypeEnum::Issue;
							break;
							case "story":
							$id = IssueTypeEnum::Story;
							break;
							case "incident":
							$id = IssueTypeEnum::Incident;
							break;
							case "task":
							$id = IssueTypeEnum::Task;
							break;
							default:
							throw new Exception("Issue Type '$name' does not exist");
							break;
						}
						$sqlFilter = "i.issue_type_id = $id";
						$filterInternal = str_replace($fullMatch, $sqlFilter, $filterInternal);
					break;
					case "status":
						// resolve status
						$id = "";
						switch ($name) {
							case "open":
							$id = StatusEnum::Neww;
							break;
							case "inprogress":
							$id = StatusEnum::InProgress;
							break;
							case "done":
							$id = StatusEnum::Resolved;
							break;
							case "closed":
							$id = StatusEnum::Confirmed;
							break;
							default:
							throw new Exception("Status '$name' does not exist");
							break;
						}
						$sqlFilter = "i.status_id = $id";
						$filterInternal = str_replace($fullMatch, $sqlFilter, $filterInternal);
					break;
					default:
					throw new Exception("Invalid key word '$keyWord'");
					break;
				}
				$i++;
			}
		}
		
		// done
		return $filterInternal;
	}
		
	
	
	/**
	 * Creates a new Issue
	 * @param Issue $model
	 * @return Issue
	 */
	public function createIssue($model) {
		/* is the model a model? */
		if (!is_object($model)) {
			throw new ParameterException("model is null");
		}
		if (get_class($model) != "Issue") {
			throw new ParameterException("model is not of type Issue");
		}

		/* authorized? */
		$project = $this->repository->getProjectById($model->projectId);
		$this->securityManager->checkLeadOrMemberAuthorization($this->contextUser, $project);
		
		/* model valid? */
		$modelException = new ModelException("Issue contains validation errors");
		// check properties
		if ($model->subject == "") {
			$modelException->addModelError("subject", "empty");
		}
		if ($project == null) {
			$modelException->addModelError("projectId", "Project does not exist");
		}

		$component = $this->repository->getComponentById($model->componentId);
		if ($component == null) {
			$modelException->addModelError("componentId", "Component does not exist");
		}
		$assignedTo = null;
		if ($model->assignedToUserId != "" && $model->assignedToUserId != null) {
			$assignedTo = $this->repository->getUserById($model->assignedToUserId);
			if ($assignedTo == null) {
				$modelException->addModelError("assignedToUserId", "User does not exist");
			}
			// has assignee project permission?
			if (!$this->securityManager->isProjectLeadOrMember($assignedTo, $project)) {
				$modelException->addModelError("assignedToUserId", "User is not a member of this project");
			}
		}
		$component = $this->repository->getComponentById($model->componentId);
		if ($component == null) {
			$modelException->addModelError("componentId", "Component does not exist");
		}
		
		
		// done
		if ($modelException->hasModelErrors()) {
			throw $modelException;
		}

		if (!$model->isNew()) {
			throw new ModelException("Issue is not new, cannot be created again");
		}

		// finally: we can create the Issue
		$this->repository->beginTransaction();
		// determine issue nr
		$issueCount = $this->repository->getProjectIssueCount($model->projectId);
		$model->issueNr = $project->identifier."-".($issueCount+1);
		$model->createdByUserId = $this->contextUser->userId;
		$model->createdAt = time();
		$model->resolutionId = ResolutionEnum::Unresolved;
		if ($model->issueTypeId == IssueTypeEnum::Story) {
			$model->wikiName = "Story:".$model->issueNr;
		} else {
			$model->wikiName = "Issue:".$model->issueNr;
		}
		
		$firstStatus = $this->repository->getFirstStatus();
		$model->statusId = $firstStatus->statusId;
		
		$model = $this->repository->createIssue($model);

		// add issue to search
		$searchItem = new SearchItem();
		$searchItem->objectId = $model->issueId;
		$searchItem->objectTypeId = ObjectTypeEnum::Issue;
		$searchItem->itemText = $model->subject . "\n" . $model->description;
		$searchItem = $this->repository->createSearchItem($searchItem);
			
		// create wikipage for issue
		$documentManager = new DocumentManager($this->repository);
		$raw = "#  " . $model->subject. "\n";
		$raw .= $model->description;
		$document = $documentManager->createWikiPage($model, $this->contextUser, $raw);

		
		// create feeditem
		$activityStreamManager = new ActivityStreamManager($this->repository);
		$feedItem = new FeedItem();
		$feedItem->createdByUserId = $this->contextUser->userId;
		$feedItem->feed = "created Issue ". $model->issueNr . " in Project " .$project->name;
		$feedItem->objectTypeId = ObjectTypeEnum::Issue;
		$feedItem->objectId = $model->issueId;
		$feedItem->targetObjectTypeId = ObjectTypeEnum::Project;
		$feedItem->targetObjectId = $project->projectId;
		$feedItem->verb = "create";
		$feedItem = $activityStreamManager->createFeedItem($feedItem);
		
		// log change in issue status so that creating an issue already gives you 1 point
		$pointsUpdate = $this->logActionByName("change-issue-status", $model);
		$projectManager = new ProjectManager($this->repository);
		$projectManager->touchObject($model, $this->contextUser);
		$projectManager->touchObject($project, $this->contextUser);
		$projectManager->touchObject($component, $this->contextUser);
		$projectManager->touchObject($component, $this->contextUser);
		if ($assignedTo != null) {
			$projectManager->touchObject($assignedTo, $this->contextUser);				
		}		
		
		$this->repository->commit();
		
		
		$pointsNewTotal = $this->repository->getUserTotalPointsById($this->contextUser->userId);
		$message = "Issue '".$model->issueNr."' created";
		$actionResult = new ActionResult($model, $pointsUpdate, $pointsNewTotal, $message);
		
		return $actionResult;
	}

	/**
	 * gets all issues the user has access to
	 */
	public function getIssues() {
		$issues = $this->repository->getIssues($this->contextUser->userId);
		return $issues;
	}
	
	
	/**
	 * retrieves issue by key
	 * @param string $key
	 * @return Issue
	 */
	public function getIssueByKey($key) {
		$model = $this->repository->getIssueByKey($key);
		$model->project = $this->repository->getProjectById($model->projectId);
		$model->component = $this->repository->getComponentById($model->componentId);
		$model->createdBy = $this->repository->getUserById($model->createdByUserId);
		$model->assignedTo = $this->repository->getUserById($model->assignedToUserId);
		$model->status = $this->repository->getStatusById($model->statusId);
		
		$projectManager = new ProjectManager($this->repository);
		$projectManager->touchObject($model, $this->contextUser);
		return $model;
	}

	/**
	 * retrieves issue by id
	 * @param int $id
	 * @return Issue
	 */
	public function getIssueById($id) {
		$model = $this->repository->getIssueById($id);
		$projectManager = new ProjectManager($this->repository);
		$projectManager->touchObject($model, $this->contextUser);
		return $model;
	}
	
	
	
	/**
	 * gets list of issues recently touched by this user
	 * @return multitype:
	 */
	public function getRecentIssues() {
		$recents = $this->repository->getRecentIssues($this->contextUser->userId);
		return $recents;
	}
	
	/**
	 * creates issues out of a file, e.g. docx or jpg
	 * @param Project $project
	 * @param string $issueFilename
	 * @param array $fileInfo
	 * @return array
	 */
	public function createIssuesFromFile($project, $issueFilename, $fileInfo) {
		$actionResults = array();
		$filename = $fileInfo["name"];
		$extension = pathinfo($filename, PATHINFO_EXTENSION);
		
		switch($extension) {
			case "docx":
			case "doc":
				$actionResults = $this->createIssuesFromWord($project, $issueFilename);
			break;
			default:
				throw new ParameterException("Invalid file type: $extension");
				break;
		}
		return $actionResults;
	}
		
	private function createIssuesFromWord($project, $wordFilename) {
		
		// convert docx to html
		require_once __DIR__ . '/../3rdparty/PhpWord/Autoloader.php';
		PhpOffice\PhpWord\Autoloader::register();
		PhpOffice\PhpWord\Settings::loadConfig();
		

		$targetFile = $wordFilename.".html";
		$format = "HTML";
		$phpWord = \PhpOffice\PhpWord\IOFactory::load($wordFilename);
		$phpWord->save($targetFile, $format);
		
		
		$xmlstr = file_get_contents($targetFile);
		$xmlstr = str_replace("&nbsp", "", $xmlstr);
		
		$doc = new DOMDocument();
		$doc->loadXML($xmlstr);
		
		$issues = array();		
		$xpath = new DOMXpath($doc);
		$elements = $xpath->query("//body/child::node()");
		
		$issue = null;
		$i = 0;
		if (!is_null($elements)) {
			foreach ($elements as $element) {
		
				if ($element->nodeName == "h1") {
					$issue = new Issue();
					$issue->projectId = $project->projectId;
					$issue->componentId = $project->defaultComponentId;
					$issue->assignedToUserId = null;
					$issue->statusId = StatusEnum::Neww;
					$issue->issueTypeId = IssueTypeEnum::Issue;
						
					
					$subject = "";
					$nodes = $element->childNodes;
					foreach ($nodes as $node) {
						$subject .= $node->nodeValue;
					}
					$issue->subject = $subject;
					$issue->description = "";
					$issues[] = $issue;
				}
				if ($element->nodeName == "p") {
					if (!is_null($issue)) {
						$nodes = $element->childNodes;
						$desc = "";
						foreach ($nodes as $node) {
							if ($node->nodeName == "img") {
		
								$data = $node->getAttribute("src");
								$data = str_replace("data:image/", "", $data);
								$pos = strpos($data, ";");
								$format = substr($data, 0, $pos);
								$data = substr($data, $pos + strlen("base64,"));
								$binary = base64_decode($data);
								file_put_contents(__DIR__ . "/../upload/".com_create_guid().".".$format, $binary);
								$i++;
							} else {
								$desc .= $node->nodeValue."\n";
							}
						}
						$issue->description .= $desc;
					}
				}
		
				//echo "<br/>[". $element->nodeName. "]";
			}
		}
		$actionResults = array();
		foreach($issues as $issue) {
			$actionResult = $this->createIssue($issue);
			$actionResults[] = $actionResult;
		}		
		
		return $actionResults;
	}
}

?>