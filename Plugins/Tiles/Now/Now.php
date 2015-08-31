<?php 

class NowPlugin extends BaseTilePlugin {

	public function initialize() {
	}
	
	public function render() {
		echo "<div style='padding-top: 30px; height: 100%; text-align: center' class='tile-purple'>";
		echo "<b>".date("l")."</b>";
		echo ", ";
		echo "<br/>";
		echo date("jS \of F").",<br/>CW " . date("W")."";
		echo "<br/>";
		echo date("h:i:s A");
		echo "</div>";
	}
}

?>