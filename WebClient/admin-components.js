'use strict';


var selectedId;
var selectedName;

angular.module('grapes.admin-components', ['ngRoute'])
.config(['$resourceProvider', function($resourceProvider) {
  // Don't strip trailing slashes from calculated URLs
  $resourceProvider.defaults.stripTrailingSlashes = false;
}])

.config(['$routeProvider', function($routeProvider) {
	$routeProvider.when('/components/:projectName?', {
		templateUrl : 'WebClient/admin-components.html',
		controller : 'ComponentsController'
	});
	
}])
.factory("ComponentsService", function($resource) {
	return $resource("Api/projects/:id/components", {id: "@id"},
			{
	    update: {
	      method: 'PUT' // this method issues a PUT request
	    }
	  }
	);
}).factory("ComponentService", function($resource) {
	return $resource("Api/components/:id", {id: "@id"},
			{
	    update: {
	      method: 'PUT' // this method issues a PUT request
	    }
	  }
	);
})
.factory("ProjectwService", function($resource) {
	return $resource("Api/projects/name/:name",  {name: '@name'});
}).factory("ComponentPreviewService", function($resource) {
	return $resource("Api/components/:id/preview", {id: "@id"}	);
}).controller('ComponentsController',
		[ '$scope', '$location', '$routeParams', 'ProjectwService', 'ComponentsService', 'ComponentService', 'ComponentPreviewService',
		  function($scope, $location, $routeParams, ProjectwService, ComponentsService, ComponentService, ComponentPreviewService) {			
		  
			var isNew = false;
			var projectName = $routeParams.projectName;
			
			$('#modalForm').on('shown.bs.modal', function() {
				$(this).find('input:text:visible:first').focus();
				
			});
			
			
			$scope.openCreate = function() {
				$scope.component = { };
				var modal = $('#modalForm');
				modal.find('.modal-title').text('New Component');
				modal.modal('show');
				isNew = true;
			};

			$scope.openUpdate = function() {
				var modal = $('#modalForm');
				modal.find('.modal-title').text('Edit Component');
				isNew = false;
				$('#name').focus();
				if (selectedId != null) {
					ComponentService.get({
						id : selectedId
					}, function(success) {
						$scope.component = success;
						modal.modal('show');
					}, function(error) {
						toast(error);
					});
				}
			};

			$scope.save = function(component) {
				var component;
				$scope.component = component;
				if (isNew) {
					ComponentsService.save({id: $scope.project.projectId }, component, function(success) {
						$scope.master = angular.copy(component);
						$('#modalForm').modal('hide');
						toastr.success('Component created')
						$('#componentsTable').dataTable()._fnAjaxUpdate();
					}, function(error) {
						$scope.validationState = error;
						console.log(error);
						toast(error);
					});
					
				} else {
					ComponentService.update({id: component.componentId }, component, function(success) {
						$scope.master = angular.copy(component);
						$('#modalForm').modal('hide');
						toastr.success('Component saved')
						$('#componentsTable').dataTable()._fnAjaxUpdate();
					}, function(error) {
						$scope.validationState = error;
						toast(error);
					});					
				}

			};

			ProjectwService.get({
				name: projectName
			}, function(project) {
				$scope.project = project;

				var dataTable = $('#componentsTable').dataTable({
					"bPaginate" : false,
					"ajax" : "Api/projects/" + project.projectId + "/components",
					"bSortClasses": false,
					"stripeClasses":[],
					"order": [[ 1, "asc" ]],
					"dataSrc": "data",
					"columns" : [ {
						"data" : "componentId"
					}, {
						"data" : "name"
					}, {
						"data" : "description"
					} ]
				});
				$('#componentsTable').on('click', 'tr', function() {
					if ($(this).hasClass('selected')) {
						$(this).removeClass('selected');
						selectedId = null;
						selectedName = null;
						$scope.preview = null;					
					} else {
						dataTable.$('tr.selected').removeClass('selected');
						$(this).addClass('selected');
						selectedId = this.childNodes[0].innerText;
						selectedName = this.childNodes[1].innerText;
						ComponentPreviewService.get({
							id : selectedId
						}, function(success) {
							$scope.preview = success;
						}, function(error) {
							toast(error);
							$scope.preview = null;
						});
						
						
					}
				});
				$('#button').click(function() {
					dataTable.row('.selected').remove().draw(false);
				});
				$("#searchbox").keyup(function() {
					dataTable.fnFilter(this.value);
				});
				
			}, function(error) {
				toast(error);
			}
			);
			
		} ]);

function updatePreviewPane(preview) {
	if (preview == null) {
		$('#previewpane').html('');
	} else {
		console.log(preview);
		$('#previewpane').html(preview.objectName);
	}
	
}
