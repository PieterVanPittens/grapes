'use strict';

var selectedUserId;

angular.module('grapes.admin-users', [ 'ngRoute' ])

.config([ '$routeProvider', function($routeProvider) {
	$routeProvider.when('/users', {
		templateUrl : 'WebClient/admin-users.html',
		controller : 'UsersCtrl'
	});
} ])
.factory("UserService", function($resource) {
	return $resource("Api/users/:id");
}).factory("UserPreviewService", function($resource) {
	return $resource("Api/users/:id/preview", {id: "@id"}	);
}).controller('UsersCtrl',
		[ '$rootScope', '$scope', 'UserService', 'UserPreviewService', function($rootScope, $scope, UserService, UserPreviewService) {
			$("body").removeClass();

			$rootScope.toggleStream(false);


			$scope.openCreate = function() {
				$scope.project = {};
				var modal = $('#modalForm');
				modal.find('.modal-title').text('New User');
				modal.modal('show');
				isNew = true;
			};

			$scope.openUpdate = function() {
				var modal = $('#modalForm');
				modal.find('.modal-title').text('Edit User');
				isNew = false;
				
				if (selectedId != null) {
					UserService.get({
						id : selectedId
					}, function(success) {
						$scope.user= success;
						modal.modal('show');
					}, function(error) {
						toast(error);
					});
				}
			};
			$scope.openUser = function() {
				console.log(selectedName);
				if (selectedName != null) {
					$location.path("/user/" + selectedName);
				}
			};
			$scope.save = function(user) {
				var user;
				$scope.user = user;
				if (isNew) {
					UserService.save(user, function(success) {
						//console.log(success);
						$scope.master = angular.copy(project);
						$('#modalForm').modal('hide');
						toastSuccess(success);
						$('#projectsTable').dataTable()._fnAjaxUpdate();
						
					}, function(error) {
						console.log(error);
						toastError(error);
					});
					
				} else {
					UserService.update(project, function(success) {
						$scope.master = angular.copy(project);
						$('#modalForm').modal('hide');
						toastr.success('User saved')
						$('#usersTable').dataTable()._fnAjaxUpdate();
					}, function(error) {
						toast(error);
					});					
				}

			};

			var dataTable = $('#usersTable').DataTable({
				"bPaginate" : false,
				"ajax" : "Api/users",
				"bSortClasses": false,
				"stripeClasses":[],
				"order": [[ 1, "asc" ]],
				"dataSrc": "data",
				"columns" : [{
					"data" : "userId"
				}, {
					"data" : "name"
				}, {
					"data" : "displayName"
				}, {
					"data" : "email"
				}, {
					"data" : "points"
				}, {
					"data" : "isConfirmed"
				} ],
				"columnDefs": [
					{
						"targets": [ 0 ],
						"visible": false,
						"searchable": false
					}
				]
			});

			$('#usersTable').on('click', 'tr', function() {
				if ($(this).hasClass('selected')) {
					$(this).removeClass('selected');
					selectedId = null;
					selectedName = null;
					$scope.preview = null;					
				} else {
					dataTable.$('tr.selected').removeClass('selected');
					$(this).addClass('selected');

					var row = dataTable.row( this );
					var data = row.data();

					selectedId = data.userId;
					selectedName = data.name;
					UserPreviewService.get({
						id : selectedId
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
