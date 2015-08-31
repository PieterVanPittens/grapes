'use strict';


var selectedId;
var selectedName;

angular.module('grapes.admin-projects', ['ngRoute'])
.config(['$routeProvider', function($routeProvider) {
  $routeProvider.when('/projects', {
    templateUrl: 'WebClient/admin-projects.html',
    controller: 'ProjectsController'
  });
}])
.factory("ProjectService", function($resource) {
	return $resource("Api/projects/:id", {id: "@id"},
			{
	    update: {
	      method: 'PUT' // this method issues a PUT request
	    }
	  }
	);
}).factory("ProjectPreviewService", function($resource) {
	return $resource("Api/projects/:id/preview", {id: "@id"}	);
}).controller('ProjectsController',
		[ '$scope', '$rootScope', '$location', 'ProjectService', 'ProjectPreviewService', function($scope, $rootScope, $location, ProjectService, ProjectPreviewService) {
			var isNew = false;
			$("body").removeClass();
			$rootScope.toggleStream(false);


			var selectedProject = null;

			$scope.openCreate = function() {
				$scope.project = {};
				var modal = $('#modalForm');
				modal.find('.modal-title').text('New Project');
				modal.modal('show');
				isNew = true;
			};

			$scope.openUpdate = function() {
				var modal = $('#modalForm');
				modal.find('.modal-title').text('Edit Project');
				isNew = false;
				
				if (selectedProject != null) {
					ProjectService.get({
						id : selectedProject.projectId
					}, function(success) {
						$scope.project = success;
						modal.modal('show');
					}, function(error) {
						toastError(error);
					});
				}
			};

			$scope.save = function(project) {
				var project;
				$scope.project = project;
				if (isNew) {
					ProjectService.save(project, function(success) {
						//console.log(success);
						$scope.master = angular.copy(project);
						$('#modalForm').modal('hide');
						toastSuccess(success);
						$('#projectsTable').dataTable()._fnAjaxUpdate();
						
					}, function(error) {
						$scope.error = error;
						toastError(error);
					});
					
				} else {
					ProjectService.update(project, function(success) {
						$scope.master = angular.copy(project);
						$('#modalForm').modal('hide');
						toastSuccess(success);
						$('#projectsTable').dataTable().ajax.reload();
					}, function(error) {
						$scope.error = error;
						toastError(error);
					});					
				}

			};

			var dataTable = $('#projectsTable').DataTable({
				"bPaginate" : false,
				"ajax" : "Api/projects",
				"bSortClasses": false,
				"stripeClasses":[],
				"order": [[ 0, "asc" ]],
				"dataSrc": "data",
				"columns" : [ {
					"data" : "name"
				}],
				"columnDefs": [
					{
						// The `data` parameter refers to the data for the cell (defined by the
						// `data` option, which defaults to the column being worked with, in
						// this case `data: 0`.
						"render": function ( data, type, row ) {

							var str = "<a href='#/project/" + row.identifier + "'>" + row.name + " (" + row.identifier + ")</a><br/>" + row.description;
							return str;
							//return '<a>' + data +'</a>'; // ('+ row[3]+')';
						},
						"targets": 0
					}
				]
			});
			$('#projectsTable').on('click', 'tr', function() {
				if ($(this).hasClass('selected')) {
					$(this).removeClass('selected');
					selectedProject = null;
					$scope.preview = null;
				} else {
					dataTable.$('tr.selected').removeClass('selected');
					$(this).addClass('selected');

					var row = dataTable.row( this );
					var data = row.data();
					selectedProject = data;
					ProjectPreviewService.get({
						id : selectedProject.projectId
					}, function(success) {
						$scope.preview = success;
					}, function(error) {
						toastError(error);
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
		} ]);

function updatePreviewPane(preview) {
	if (preview == null) {
		$('#previewpane').html('');
	} else {
		console.log(preview);
		$('#previewpane').html(preview.objectName);
	}
	
}
