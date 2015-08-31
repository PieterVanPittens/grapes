<?php
class tomorrowPlugin extends BaseTilePlugin {

	public function initialize() {
	}
	
	public function render() {
	
	?>
		<div>
		<table width="100%">
		<tr>
			<td width="50%">
			<canvas id="chart1"></canvas>
			</td>
			<td width="50%">
			<canvas id="chart2"></canvas>
			</td>
		</tr>
		<tr>
			<td colspan="2">erläuterung....</td>
		</tr>
		</table>
		</div>	
		
			<script>
	var randomScalingFactor = function(){ return Math.round(Math.random()*100)};

	var barChartData = {
		labels : ["Pieter","Hans","Mr.Crazy","Olivia","Baerbel","Esmeralda","Nerea Barnes","Chancellor Santos","Merrill Charles","Rogan Cleveland"],
		datasets : [
			{
				fillColor : "rgba(220,220,220,0.5)",
				strokeColor : "rgba(220,220,220,0.8)",
				highlightFill: "rgba(220,220,220,0.75)",
				highlightStroke: "rgba(220,220,220,1)",
				data : [randomScalingFactor(),randomScalingFactor(),randomScalingFactor(),randomScalingFactor(),randomScalingFactor(),randomScalingFactor(),randomScalingFactor()]
			}
		]

	}
		var ctx = document.getElementById("chart1").getContext("2d");
		window.myBar = new Chart(ctx).Bar(barChartData, {
			responsive : true
		});
		var ctx = document.getElementById("chart2").getContext("2d");
		window.myBar = new Chart(ctx).Bar(barChartData, {
			responsive : true
		});

	</script>
		
	<?php
	}
}
?>