<?php
class ChuckNorrisPlugin extends BaseTilePlugin {

	
	public function initialize() {
	}
	
	public function render() {
		?>
<script type="text/javascript">
<!--
	var joke = '';
	var jqxhr = $.get( "http://api.icndb.com/jokes/random", function(data) {
joke = data.value.joke;
		$("#joke").html(joke);
		  
		})
  .fail(function() {
	  joke = "Could not get a joke";
	  $("#joke").html(joke);
    });

//-->
</script>


<div id="joke" style="padding: 6px; text-align: center"></div>

		
		<?php
	}
}
