'use strict';

var adminProjectDashboardApp = angular.module('grapes.admin-project-dashboard', [ 'ngRoute' ])

.config([ '$routeProvider', function($routeProvider) {
	$routeProvider.when('/project/:projectName?', {
		templateUrl : 'WebClient/admin-project-dashboard.html',
		controller : 'ProjectDashboardController'
	});
} ])
.factory("ProjectwService", function($resource) {
	return $resource("Api/projects/name/:name",  {name: '@name'});
})
.controller(
		'ProjectDashboardController',
		[
				'$scope', '$routeParams', 'ProjectwService',
				function($scope, $routeParams, ProjectwService) {
					var projectName = $routeParams.projectName;
					ProjectwService.get({
						name: projectName
					}, function(projectJson) {
						$scope.project = projectJson;

					}, function(error) {
						toast(error);
					}
					);

					
				} ]);
