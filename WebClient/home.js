'use strict';

var homeApp = angular.module('grapes.home', [ 'ngRoute' ])

.config([ '$routeProvider', function($routeProvider) {
	$routeProvider.when('/home', {
		templateUrl : 'WebClient/home.html',
		controller : 'HomeController'
	});
} ])
.factory("DashboardService", function($resource) {
	return $resource("Api/dashboards/:id");
})
.factory("TilesService", function($resource) {
	return $resource("Api/tiles");
})

.controller(
		'HomeController',
		[
				'$rootScope',
				'$scope',
				'$resource',
				'DashboardService',
				'TilesService',
				function($rootScope, $scope, $resource, DashboardService, TilesService) {
					
					$("body").addClass("body-dashboard");
					
					var dashboardId = 0;
					console.log('HomeController here!');

					$rootScope.$broadcast('refresh-stream', { objectTypeId: 1, objectId: $rootScope.auth.currentUser.userId });

					$("#trash").hide();
					
					$scope.loadDashboard = function() {
						// load and display Dashboard
						DashboardService.get({
							id : $rootScope.auth.currentUser.homeDashboardId
						}, function(dashboardJson) {
							var dashboardNode = $("#dashboard");
							dashboardId = dashboardJson.dashboardId;
							
							// delete all tiles first that might already exist (in case this dashboard is reloaded)
							$(".gridster > ul").remove();
							$(".gridster").append("<ul></ul>");
							var gridster = $(".gridster > ul").gridster({
								widget_margins : [ 4, 4 ],
								widget_base_dimensions : [ 140, 140 ],
								serialize_params: function ($w, wgd) {              
									if (wgd == null) {
								          return {
								              /* add element ID to data*/
								              id: $w.attr('id'),
								              /* defaults */
								              col: 0,
								              row: 0,
								              size_x: 0,
								              size_y: 0
								          }
									} else {
								          return {
								              /* add element ID to data*/
								              id: $w.attr('id'),
								              /* defaults */
								              col: wgd.col,
								              row: wgd.row,
								              size_x: wgd.size_x,
								              size_y: wgd.size_y
								          }								
									}

							      },
								draggable: {
									start: function(event, ui) {
										//$("#trash").show();
									},
									stop: function(event, ui, $widget) {
										//$("#trash").hide();
										//console.log(ui);
										
										var tileNode = ui.$helper[0];
										var bottomBorder = tileNode.parentNode.clientHeight;
										//console.log(bottomBorder);
										/*
										if (ui.position.top > bottomBorder ) {
											var tileId = tileNode.id.replace("tile-", "");
											$.ajax({
											    url: 'Api/dashboardtiles/' + tileId,
											    type: 'DELETE'
											})
										    .fail(function(xhr, textStatus, errorThrown ) {
												toastr.error(xhr.responseText, 'Could not remove tile from dashboard');
										    })
										    .done(function() {
												gridster.remove_widget(tileNode);
										    	
										    });
										}
											*/								
										
										var jsonChanged = gridster.serialize_changed();
										var numChanged = jsonChanged.length;
										for (var i = 0; i <= numChanged - 1; i++) {
											var tileChanged = jsonChanged[i];
											var tileId = tileChanged.id.replace("tile-", "");
											$.post( "Api/dashboardtiles/" + tileId + "/position", tileChanged, function (data) {
												//console.log(data);
											})
											.fail( function(xhr, textStatus, errorThrown) {
												toastr.error(xhr.responseText, 'Could not save changes to dashboard');
										    });
										}
									}
								},
								resize : {
									enabled : true,
									stop: function(event, ui) {
										var jsonChanged = gridster.serialize(); //serialize_changed does not work when resizing. it works only for re-positioning
										var numChanged = jsonChanged.length;
										for (var i = 0; i <= numChanged - 1; i++) {
											var tileChanged = jsonChanged[i];
											var tileId = tileChanged.id.replace("tile-", "");
											$.post( "Api/dashboardtiles/" + tileId + "/position", tileChanged, function (data) {
												//console.log(data);
											})
											.fail( function(xhr, textStatus, errorThrown) {
												toastr.error(xhr.responseText, 'Could not save changes to dashboard');
										    });
										}
									}
									
								}
							}).data('gridster');
							var numTiles = dashboardJson.tiles.length;

							// create tiles
							for (var i = 0; i <= numTiles - 1; i++) {
								var tile = dashboardJson.tiles[i];
								gridster
										.add_widget('<li id="tile-'
												+ tile.dashboardTileId + '"></li>',
												tile.width, tile.height, tile.col,
												tile.row);
							}

							// fill tiles and apply their custom css class
							var fillTile = function(tileId) {
								return function(data, textStatus, jqXHR) {
									$("#tile-" + tileId).append(data);
								};
							};
							for (var i = 0; i <= numTiles - 1; i++) {
								var tile = dashboardJson.tiles[i];
								var tileId = tile.dashboardTileId;
								// load custom CSS
								if (tile.customCssFile != "" && tile.customCssFile != "null") {
									var styleURI = "Plugins/Tiles/" + tile.name + "/" + tile.customCssFile;
									if (document.createStyleSheet){
						                document.createStyleSheet(styleURI);
						            }
						            else {
						                $("head").append($("<link rel='stylesheet' href='" + styleURI + "' type='text/css' />"));
						            }
									
									$("#tile-" + tileId).addClass("tile-" + tile.name);
								}
								// load custom JS
								// todo
								$.getScript("Plugins/numberofusers/numberofusers.js");
								
								var tileObject = $("#tile-" + tileId);
								//tileObject.append("<div class='tile-properties-icon'><span class='glyphicon glyphicon-cog' aria-hidden='true'></span></div>");
								
								var deleteButtonId = "delete-tile" + tileId ;
								
								var buttons = "<div class='tile-properties-icon'>";
								buttons += '<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">';
								buttons += '<span class="glyphicon glyphicon-cog" aria-hidden="true"></span>';
								buttons += '</button>';

								buttons += '<ul class="dropdown-menu dropdown-menu-right">';
								buttons += '<li><a id="' +  deleteButtonId + '" data-id="' + tileId + '">Delete</a></li>';
								buttons += '</ul>';
								buttons += '</div>';

								
								buttons += '</div>';
								tileObject.append(buttons);
								
								$('#' + deleteButtonId).on('click', function (e) {
									var tileId = e.currentTarget.dataset.id;
									$scope.removeTileFromDashboard(tileId);
								});
								
	/*							
								tileObject.append('<div class="btn-group">');
								tileObject.append('<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">');
								tileObject.append('<span class="glyphicon glyphicon-cog" aria-hidden="true"></span>');
								tileObject.append('</button>');
								tileObject.append('<ul class="dropdown-menu">');
								tileObject.append('<li><a href="#">Action</a></li>');
								tileObject.append('<li><a href="#">Another action</a></li>');
								tileObject.append('<li><a href="#">Something else here</a></li>');
								tileObject.append(' <li role="separator" class="divider"></li>');
								tileObject.append('<li><a href="#">Separated link</a></li>');
								tileObject.append(' </ul>');
								tileObject.append('</div>');							
								
		*/						
								$.get("dashboardtile.php?id=" + tileId,
										fillTile(tileId));

							}

						});
					
					};
					
					
					$('#tilesModal').on('shown.bs.modal', function (e) {
						// load all tiles and fill into scope for modal dialog
						TilesService.get(function(tiles) {
							$scope.tiles = tiles.data;
						});
					});
					
					
					// add new tile to dashboard
					$scope.addTileToDashboard = function(tileId) {
				    	var DashboardTilesService = $resource("Api/dashboards/" + dashboardId + "/tiles", {tileId: "@tileId"});
				    	DashboardTilesService.save({tileId:tileId}, function(actionResult) {
				    		toastSuccess(actionResult);
				    		$scope.loadDashboard();
					 	}, function(error) {
				    		toastError(error);
				    	});
					};
					// remove tile from dashboard
					$scope.removeTileFromDashboard = function(tileId) {
				    	var DashboardTilesService = $resource("Api/dashboardtiles/" + tileId);
				    	DashboardTilesService.delete(function(actionResult) {
				    		toastSuccess(actionResult);
				    		$scope.loadDashboard();
					 	}, function(error) {
				    		toastError(error);
				    	});
					};
					
					
					$scope.loadDashboard();

					
				} ]);
