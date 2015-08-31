<?php 

class DaysRemainingPlugin extends BaseTilePlugin {

	public function initialize() {
	}
	
	public function render() {
	?>
	<a data-target="#loginModal" data-toggle="modal">Login</a>
		<?php
		echo "only 34 Days left!";
	}
}

?>