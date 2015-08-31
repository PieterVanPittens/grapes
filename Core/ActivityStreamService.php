<?php
class ActivityStreamService extends BaseService {

	/**
	 *
	 * @param FeedItem $feedItem
	 */
	public function createFeedItem($feedItem) {
		/* is the model a model? */
		if (!is_object($feedItem)) {
			throw new ParameterException("feeditem is not an object");
		}
		if (get_class($feedItem) != "FeedItem") {
			throw new ParameterException("feedItem is not of type FeedItem");
		}
		// todo: auth check


		$activityStreamManager = new ActivityStreamManager($this->repository);

		$this->repository->beginTransaction();
		$feedItem->createdByUserId = $this->contextUser->userId;
		if ($feedItem->verb === null || $feedItem->verb == "") {
			$feedItem->verb = "post";
		}
		$feedItem = $activityStreamManager->createFeedItem($feedItem);

		$pointsUpdate = $this->logActionByName("create-feed-item", $feedItem);
		$this->repository->commit();

		$pointsNewTotal = $this->repository->getUserTotalPointsById($this->contextUser->userId);
		$message = "FeedItem created";
		$actionResult = new ActionResult($feedItem, $pointsUpdate, $pointsNewTotal, $message);

		return $actionResult;
	}

	/**
	 * gets activity stream for a dashboard
	 * @param unknown $dashboardId
	 */
	public function getDashboardActivities($dashboardId) {
		$feedItems = $this->repository->getObjectFeedItems(ObjectTypeEnum::Dashboard, $dashboardId);
		$activityStream = $this->getActivityStream($feedItems);
		$dashboard = $this->repository->getDashboardById($dashboardId);
		$activityStream->title = $dashboard->name;
		return $activityStream;
	}

	/**
	 * gets activity stream for a issue
	 * @param unknown $issueId
	 */
	public function getIssueActivities($issueId) {
		$feedItems = $this->repository->getObjectFeedItems(ObjectTypeEnum::Issue, $issueId);
		$activityStream = $this->getActivityStream($feedItems);
		$activityStream->title = "Issue";
		return $activityStream;
	}
	
	/**
	 * gets actionlog
	 */
	public function getActionLog($chunk) {
		$items = $this->repository->getActionLogItems($chunk);
		return $items;
	}
	
	
	/**
	 * gets activity stream for a project
	 * @param unknown $projectId
	 */
	public function getProjectActivities($projectId) {
		$feedItems = $this->repository->getObjectFeedItems(ObjectTypeEnum::Project, $projectId);
		$activityStream = $this->getActivityStream($feedItems);
		$project = $this->repository->getProjectById($projectId);
		$activityStream->title = $project->name;
		return $activityStream;
	}

	/**
	 * gets activity stream for a user
	 * @param unknown $projectId
	 */
	public function getUserActivities($userId) {
		// auth check
		// public user must not see any other streams except the public one (his own)
		if ($userId != 1) {
			SecurityManager::checkPublicUser($this->contextUser);
		}
		// get the data
		$feedItems = $this->repository->getObjectFeedItems(ObjectTypeEnum::User, $userId);
		$activityStream = $this->getActivityStream($feedItems);
		$user = $this->repository->getUserById($userId);
		$activityStream->title = $user->displayName;
		return $activityStream;
	}

	/**
	 * gets Activitystream
	 * @return ActivityStream
	 */
	private function getActivityStream($feedItems) {

		$activityStream = new ActivityStream();

		$feedItems2 = array();
		$currentParent = null;
		foreach($feedItems as $feedItem) {
			$feedItem->createdBy->updateImageUrls();
			if (($currentParent == null) || ($feedItem->replyToId == 0)) {
				$currentParent = $feedItem;
				$feedItems2[] = $feedItem;
			} else {
				$currentParent->replies[] = $feedItem;
			}
		}
		$activityStream->feedItems = $feedItems2;

		return $activityStream;
	}
}

class ActivityStreamManager extends BaseManager {

	/**
	 *
	 * @param FeedItem $feedItem
	 */
	public function createFeedItem($feedItem) {
		$feedItem->replyToId = 0;
		$feedItem->createdAt = time();
		$feedItem->sortParents = 0;
		$feedItem->sortReplies= "";
		$feedItem->numReplies = 0;
		$feedItem = $this->repository->createFeedItem($feedItem);

		$this->repository->updateFeedItemSorting($feedItem->feedItemId, $feedItem->feedItemId);
		return $feedItem;
	}

	/**
	 * Replies to a feed item
	 * @param FeedItem $reply
	 * @param int $replyToFeedItemId
	 */
	public function replyToFeedItem($reply, $replyToFeedItemId) {
		$parentInfos = $this->repository->getFeedItemParentInfoById($replyToFeedItemId);
		$reply->numReplies = 0;
		$reply->replyToId = $replyToFeedItemId;
		$reply->createdAt = time();
		$reply->sortParents = $replyToFeedItemId;
		$reply->sortReplies= $replyToFeedItemId . "-" . ($parentInfos["numReplies"]+1);
		$reply = $this->repository->createFeedItem($reply);
		$this->repository->updateFeedItemNumReplies($replyToFeedItemId);
		return $reply;
	}
}
?>