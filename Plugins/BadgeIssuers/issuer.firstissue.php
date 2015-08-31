<?php


class firstissueIssuer extends BaseIssuer implements iBadgeIssuer {

	/**
	 * (non-PHPdoc)
	 * @see iBadgeIssuer::issueBadges()
	 */
	public function issueBadges($actionLogItem) {
		$userBadges = array();
		
		$badge = $this->repository->getBadgeByName("myfirstissue");
		if ($badge === null) {
			throw new PluginException("Badge 'myfirstissue' does not exist");
		}
		// user already has that badge?
		if ($this->userAlreadyHasBadge($actionLogItem->userId, $badge->badgeId)) {
			return $userBadges;
		}
		
		// user has created a issue?
		if ($actionLogItem->objectTypeId == ObjectTypeEnum::Issue) {
			$query = "SELECT COUNT(issue_id) FROM issues WHERE created_by_user_id = ?";
			$stmt = $this->repository->mysqli->prepare($query);
			$stmt->bind_param("i", $actionLogItem->userId);
			$stmt->execute();
			$a = array();
			$stmt->bind_result($a["numberOfIssues"]);
			if ($stmt->fetch()) {
				if ($a["numberOfIssues"] > 0) {
					$this->addBadgeToBeIssued($badge, $actionLogItem);
				}
			}
		}
	}
}

?>