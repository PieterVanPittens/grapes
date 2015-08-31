<?php

class numberofusersPlugin extends BaseTilePlugin {

	private $userStats;
	
	public function initialize() {
		$userService = new UserService($this->contextUser, $this->repository);
		$this->userStats = $userService->getStats();
	}
	
	public function render() {
		?>
		<div style="left: 10px;" class="numberofusers">
		<p><span style="font-weight: bold; font-size: 50pt;"><?php echo $this->userStats->numberOfUsers; ?></span></p>
		<p><a href="#users"><span style="font-weight: bold; font-size: 20pt;">Users</span></a></p>
		</div>
		<button type="button" id="class="btn btn-info">Info</button>
		<?php
	}
}

?>
