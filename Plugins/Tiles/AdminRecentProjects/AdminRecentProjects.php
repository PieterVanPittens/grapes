<?php
class AdminRecentProjectsPlugin extends BaseTilePlugin {

	private $recentProjects;
	
	public function initialize() {
		$projectService = new ProjectService($this->contextUser, $this->repository);
		$this->recentProjects = $projectService->getRecentProjects();
	}
	
	public function render() {
		?>
		<div style=" height: 100%; padding: 5px" class="tile tile-orange">
		<h3>Recent Projects</h3>
		<ul>
		<?php
		foreach($this->recentProjects as $recentProject) {		
			?>
			<li><a href="#/project/<?php echo $recentProject->name; ?>"><?php echo $recentProject->name; ?></a></li>
			<?php
			}
		?>
		</ul>
		</div>
		<?php
	}
}
?>