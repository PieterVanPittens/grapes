<?php



class BaseService {
	/**
	 * 
	 * @var User
	 */
	protected $contextUser;
	/**
	 * 
	 * @var MySqlRepository
	 */
	protected $repository;
	
	/**
	 * security manager
	 * @var SecurityManager
	 */
	protected $securityManager;

	public $config;
	
	function __construct($contextUser, $repository) {
		if (!is_object($contextUser)) {
			throw new ParameterException("contextUser is null");
		}
		if (get_class($contextUser) != "User") {
			throw new ParameterException("contextUser is not of type User");
		}
		if (!is_object($repository)) {
			throw new ParameterException("repository is null");
		}
		if (get_class($repository) != "MySqlRepository") {
			throw new ParameterException("repository is not of type MySqlRepository");
		}
		
		$this->contextUser = $contextUser;
		$this->repository = $repository;
		$this->securityManager = new SecurityManager($repository);
	}
	
	/**
	 * logs an action and rewards points accordingly
	 * @param string $actionName
	 * @param object $object
	 * @return int Points for this action
	 */
	public function logActionByName($actionName, $object) {
		$action = $this->repository->getActionByName($actionName);
		return $this->logAction($action, $object);
	}

	/**
	 * logs an action and rewards points accordingly
	 * @param Action $action
	 * @param object $object
	 * @return int Points for this action
	 */
	public function logAction($action, $object) {
		if ($action === null) {
			throw new Exception("logAction: action must not be null");
		}
		if ($object === null) {
			throw new Exception("logAction: object must not be null");
		}
		if (get_class($action) != "Action") {
			throw new ParameterException("action is not of type Action");
		}
		$actionLogItem = new ActionLogItem();
		$actionLogItem->actionId = $action->actionId;
		$actionLogItem->objectId = $object->getId();
		$actionLogItem->objectTypeId = $action->objectTypeId;
		$actionLogItem->points = $action->points;
		$actionLogItem->timestamp = time();
		$actionLogItem->userId = $this->contextUser->userId;

		return $this->logActionLogItem($actionLogItem);
	}
	
	/**
	 * logs an actionlogitem and rewards points accordingly
	 * @param ActionLogItem $actionLogItem
	 * @param object $object
	 * @return int Points for this action
	 */
	public function logActionLogItem($actionLogItem) {
		if ($actionLogItem === null) {
			throw new Exception("actionlogitem must not be null");
		}
		$validationState = new ValidationState();
		$actionLogItem = $this->repository->createActionLogItem($actionLogItem, $validationState);
		$this->repository->addUserPoints($actionLogItem->userId, $actionLogItem->points);
		return $actionLogItem->points;
	}
	
	
}





interface iBadgeIssuer {
	
	/**
	 * issues badges according to an actionlogitem
	 * @param ActionLogItem $actionLogItem
	 */
	public function issueBadges($actionLogItem);
}

class BaseIssuer extends BaseService {
	
	private $userBadges = array();
	
	/**
	 * adds a badge that is supposed to be issued to a user based on an action
	 * 
	 * @param Badge $badge
	 * @param User $user
	 * @param ActionLogItem $actionLogItem
	 */
	public function addBadgeToBeIssued($badge, $actionLogItem) {
		$userBadge = new UserBadge();
		$userBadge->actionLogItemId = $actionLogItem->logId;
		$userBadge->badgeId = $badge->badgeId;
		$userBadge->userId = $actionLogItem->userId;
		$userBadge->badge = $badge;
		$this->userBadges[] = $userBadge;
	}
	
	/**
	 * returns array of userbadges that are supposed to be issued
	 * @return multitype:
	 */
	public function getBadgesToBeIssued() {
		return $this->userBadges;
	}
	
	/**
	 * checks if user already has that badge
	 * @param User $user
	 * @param Badge $badge
	 * @return boolean
	 */
	public function userAlreadyHasBadge($userId, $badgeId) {
		$userBadge = $this->repository->getUserBadge($userId, $badgeId);
		return $userBadge != null;		
	}
	
	
}




class BadgeService extends BaseService {
	
	/**
	 * retrieves all Badges
	 * @return Array
	 */
	public function getBadges() {
		$models = $this->repository->getBadges();
		return $models;
	}
	
	
	/**
	 * issues badges for all unprocessed actionlogitems
	 */
	function issueUnprocessedActionLogItems() {
		$actionLogItems = $this->repository->getUnprocessedActionLogItems();
		foreach ($actionLogItems as $actionLogItem) {
			$this->issueBadges($actionLogItem);
		}
		
	}
	/**
	 * issues all badges that might be caused by this actionlogitem
	 * @param ActionLogItem $actionLogItem
	 */
	function issueBadges($actionLogItem) {
		
		// get all issuers for this objecttype
		$issuers = $this->repository->getIssuersByObjectTypeId($actionLogItem->objectTypeId);
		$actionReceiveBadge = $this->repository->getActionByName("receive-badge");
		if ($actionReceiveBadge === null) {
			throw new Exception("action receive-badge not found in database");
		}
		// execute issuers
		
		$activityStreamManager = new ActivityStreamManager($this->repository);
		$this->repository->beginTransaction();
		foreach ($issuers as $issuer) {
			$filename = "issuer." . $issuer->name . ".php";
			
			require_once dirname(__FILE__) . "/../Plugins/BadgeIssuers/$filename";
			$className = $issuer->name . "Issuer";
			$issuer = new $className($this->contextUser, $this->repository);
			$issuer->issueBadges($actionLogItem);
			$userBadges = $issuer->getBadgesToBeIssued();
			$validationState = new ValidationState();
			foreach($userBadges as $userBadge) {
				// assign badge
				$this->repository->createUserBadge($userBadge);
				// log assignment
				$actionLogItem2 = new ActionLogItem();
				$actionLogItem2->actionId = $actionReceiveBadge->actionId;
				$actionLogItem2->objectId = $userBadge->badge->badgeId;
				$actionLogItem2->objectTypeId = ObjectTypeEnum::Badge;
				$actionLogItem2->points = $userBadge->badge->points;
				$actionLogItem2->timestamp = time();
				$actionLogItem2->userId = $userBadge->userId;
				$actionLogItem2->isProcessed = 1; // no need to have processed later on for issuing a badge
				$this->logActionLogItem($actionLogItem2);

				// communicate: universe awarded badge to user

				// universe user
				$universeUser = $this->repository->getUserById(2);
				$badgeUser = $this->repository->getUserById($userBadge->userId);
				$feedItem = new FeedItem();
				$feedItem->createdByUserId = $universeUser->userId;
				$feedItem->createdAt = time();
				$feedItem->feed = " awarded Badge '".  $userBadge->badge->name . "' to " . $badgeUser->displayName; // todo
				$feedItem->objectId = $userBadge->badge->badgeId;
				$feedItem->objectTypeId = ObjectTypeEnum::Badge;
				$feedItem->targetObjectId = $userBadge->userId;
				$feedItem->targetObjectTypeId = ObjectTypeEnum::User;
				$feedItem->verb = "award";

				$feedItem = $activityStreamManager->createFeedItem($feedItem);
			}
		}
		
		// set logitem to issued
		$validationState = new ValidationState();
		$this->repository->setActionLogItemToProcessed($actionLogItem, $validationState);
		$this->repository->commit();
	}
	
	
	/**
	 * gets UserBadges by userId
	 * @param int $userId
	 * @return array
	 * */
	public function getUserBadges($userId) {
		if ($userId == "") {
			throw new ParameterException("userId is empty");
		}
		$models = $this->repository->getUserBadges($userId);
		return $models;
	}
	
}

?>