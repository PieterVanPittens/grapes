'use strict';

angular.module('grapes.map', ['ngRoute'])
.config(['$routeProvider', function($routeProvider) {
  $routeProvider.when('/map', {
    templateUrl: 'WebClient/maps.html',
    controller: 'MapsController'
  }).when('/map/:mapId?', {
	    templateUrl: 'WebClient/map.html',
	    controller: 'MapController'
	});
}])
.controller('tileController',
[ '$scope', '$location', '$resource', function($scope, $location, $resource) {
		$scope.init = function(dashboardTileId) {
			$scope.dashboardTileId = dashboardTileId;
			
			var service = $resource("dashboardtile.php?id=" + dashboardTileId);
			service.get(function(result) {
				$scope.dashboardTile = result.dashboardTile;
				$scope.stats = result.stats;
			}, function(error) {
				toastr.error(error);
			});
		};
}])
.controller('MapController',
[ '$scope', '$rootScope', '$location', '$resource', '$routeParams', function($scope, $rootScope, $location, $resource, $routeParams) {
	$("body").removeClass();
	$scope.mapId = $routeParams.mapId;

	
	var MapService = $resource("Api/dashboards/:mapId", {mapId: "@mapId"});
	
	$rootScope.$broadcast('refresh-stream', { objectTypeId: 9, objectId: $scope.mapId });
	
	MapService.get({mapId:$scope.mapId}, function(map) {
		$scope.map = map;
		var arrayLength = map.tiles.length;
		
		var tilesNode = $("#microtiles");
		for (var i = 0; i < arrayLength; i++) {
			var tile = map.tiles[i];
		    var templateUrl = "Plugins/Tiles/" + tile.name + "/view.html";
		    $scope.map.tiles[i].templateUrl = templateUrl;
		    
		}
		
		$("body").css("backgroundImage", "url(upload/" + map.parameterValues.dashboardBackground.value + ")"); 
		setTimeout(function() { // todo: this is a timing issue with angular. no clue yet what the correct solution might be?!
			for (var i = 0; i < arrayLength; i++) {
				var tile = map.tiles[i];
			    var id = "tile" + tile.dashboardTileId;

			    
		    	var node = $("#"+id);
			    node.draggable({
			    	stop: function(e) {
			    		var dashboardTileId = e.target.dataset.tileid;
						console.log(dashboardTileId);				    		
						var node = $("#tile"+dashboardTileId);
						console.log($(node));
						
						var col = node.position().left;
			    		var row = node.position().top;
			    		
						var tileChanged = {
								id: "tile-"+dashboardTileId,
								col: col,
								row: row,
								size_x: 1,
								size_y: 1
						};
						
						$.post( "Api/dashboardtiles/" + dashboardTileId + "/position", tileChanged, function (data) {
							//console.log(data);
						})
						.fail( function(xhr, textStatus, errorThrown) {
							toastr.error(xhr.responseText, 'Could not save changes to dashboard');
					    });
			    	}
				    	
				    	
			    });

			}
			$('[data-toggle="tooltip"]').tooltip()
			
		}, 500);
		
	}, function(error) {
		toastError(error);
	});
	
}]);

