<?php
class RecentIssuesPlugin extends BaseTilePlugin {

	public function initialize() {
		$service = new IssueService($this->contextUser, $this->repository);
		$this->recentIssues = $service->getRecentIssues();
	}
	
	public function render() {
		?>
		<div style=" height: 100%; padding: 5px" class="tile tile-blue">
		<h3>Recent Issues</h3>
		
		<ul style="margin-top: 8px">
			
			
		<?php
		foreach($this->recentIssues as $recentIssue) {
			if ($recentIssue->issueTypeId == 1) {
				$icon = "flash";
			} elseif ($recentIssue->issueTypeId == 2) {
				$icon = "film";
			} else {
				$icon = "fire";
			}
				
			?>
			<li><span class="glyphicon glyphicon-<?php echo $icon; ?>" aria-hidden="true"></span>&nbsp;<a href="#/issue/<?php echo $recentIssue->issueNr; ?>"><?php echo $recentIssue->issueNr .":". $recentIssue->subject; ?></a></li>
			<?php
			}
		?>
		</ul>
		</div>
		<?php
	}
}
?>