<?php

class ActivityStreamApiController extends BaseWebApiController {

	/**
	 * creates a new feeditem
	 */
	public function createFeedItem() {
		$body = file_get_contents('php://input');
		$feedItem = FeedItem::createModelFromJson($body);
		$service = new ActivityStreamService($this->contextUser, $this->repository);
		$feedItem = $service->createFeedItem($feedItem);
		return $feedItem;
		
	}
	
	public function getDashboardActivities($parameters) {
		$id = $parameters["id"];
		$service = new ActivityStreamService($this->contextUser, $this->repository);
		$stream = $service->getDashboardActivities($id);
		return  $stream;
	}
	
	public function getProjectActivities($parameters) {
		$id = $parameters["id"];
		$service = new ActivityStreamService($this->contextUser, $this->repository);
		$stream = $service->getProjectActivities($id);
		return  $stream;
	}

	public function getIssueActivities($parameters) {
		$id = $parameters["id"];
		$service = new ActivityStreamService($this->contextUser, $this->repository);
		$stream = $service->getIssueActivities($id);
		return  $stream;
	}
	
	public function getUserActivities($parameters) {
		$id = $parameters["id"];
		$service = new ActivityStreamService($this->contextUser, $this->repository);
		$stream = $service->getUserActivities($id);
		return  $stream;
	}
	
	public function getActionLog() {
		if (isset($_GET["chunk"])) {
			$chunk = $_GET["chunk"];
		} else {
			$chunk = 0;
		}
		$service = new ActivityStreamService($this->contextUser, $this->repository);
		$log = $service->getActionLog($chunk);
		return  $log;
	}
	
}


?>