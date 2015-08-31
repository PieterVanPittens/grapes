<?php


class firstprojectIssuer extends BaseIssuer implements iBadgeIssuer {

	/**
	 * (non-PHPdoc)
	 * @see iBadgeIssuer::issueBadges()
	 */
	public function issueBadges($actionLogItem) {
		$userBadges = array();
		
		$badge = $this->repository->getBadgeByName("myfirstproject");
		if ($badge === null) {
			throw new PluginException("Badge 'myfirstproject' does not exist");
		}
		// user already has that badge?
		if ($this->userAlreadyHasBadge($actionLogItem->userId, $badge->badgeId)) {
			return $userBadges;
		}
		
		// user has created a project?
		if ($actionLogItem->objectTypeId == ObjectTypeEnum::Project) {
			$query = "SELECT COUNT(project_id) FROM projects WHERE created_by_user_id = ?";

			$stmt = $this->repository->mysqli->prepare($query);
			$stmt->bind_param("i", $actionLogItem->userId);
			$stmt->execute();
			$a = array();
			$stmt->bind_result($a["numberOfProjects"]);
			if ($stmt->fetch()) {
				if ($a["numberOfProjects"] > 0) {
					$this->addBadgeToBeIssued($badge, $actionLogItem);
				}
			}
		}
	}
}

?>