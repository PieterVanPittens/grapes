<?php
class numberOfIssuesPlugin extends BaseTilePlugin {

	private $html;
	
	private $result;
	
	public function initialize() {
		$issueService = new IssueService($this->contextUser, $this->repository);
	
		$issues = $issueService->getIssuesByFilter($this->dashboardTile->parameterValues["filter"]->value);
		$stats = array();
		$stats["numberOfIssues"] = count($issues);
	
		$this->result["dashboardTile"] = $this->dashboardTile;
		$this->result["stats"] = $stats;
		
	}
	
	public function render() {
		echo json_encode($this->result);
	}
}
?>