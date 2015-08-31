<?php 

class GuestLoginPlugin extends BaseTilePlugin {

	public function initialize() {
	}
	
	public function render() {
	?>
	<div style='padding-top: 30px; padding-left: 10px; height: 100%;' class='tile-orange'>
	
	<p>Hi Guest,</p>
	<p>&nbsp;</p>
	<p><a data-target="#loginModal" data-toggle="modal">Login here</a> to enter our world</p>
	</div>
		<?php
	}
}

?>