'use strict';

angular.module('grapes.issue', ['ngRoute'])
.config(['$routeProvider', function($routeProvider) {
  $routeProvider.when('/issue', {
    templateUrl: 'WebClient/issues.html',
    controller: 'IssuesController'
  }).when('/issue/:issueNr?', {
	    templateUrl: 'WebClient/issue.html',
	    controller: 'IssueController'
	});
}])
.controller('IssueController',
[ '$rootScope', '$scope', '$location', '$resource', '$routeParams', function($rootScope, $scope, $location, $resource, $routeParams) {
	$("body").removeClass();


	$rootScope.toggleStream(true);


	$scope.issueNr = $routeParams.issueNr;

	
	var IssueService = $resource("Api/issues/key/:issueNr", {issueNr: "@issueNr"},
		    {
        	'update': { method:'PATCH' }
		    }
			
	
	);
	
	
	IssueService.get({issueNr:$scope.issueNr}, function(issue) {
		$scope.issue = issue;
		
		$rootScope.$broadcast('refresh-stream', { objectTypeId: 2, objectId: issue.issueId });
		
	}, function(error) {
		toastError(error);
	});

	
	$scope.updateIssueSubject = function(data) {
	    return IssueService.update({issueNr: $scope.issueNr}, {subject: data}, function(result) { toastSuccess(result); }, function error(error) {toastError(error)}
		)};

	$scope.setInProgress = function() {
		return IssueService.update({issueNr: $scope.issueNr}, {statusId: 2}, function(result) {
				toastSuccess(result);
				$scope.issue.statusId = 2;
				$rootScope.$broadcast('refresh-stream', { objectTypeId: 2, objectId: $scope.issue.issueId });
			}, function error(error) {toastError(error)}
		)};
	$scope.setNew = function() {
		return IssueService.update({issueNr: $scope.issueNr}, {statusId: 1}, function(result) {
				toastSuccess(result);
				$scope.issue.statusId = 1;
				$rootScope.$broadcast('refresh-stream', { objectTypeId: 2, objectId: $scope.issue.issueId });
			}, function error(error) {toastError(error)}
		)};
	$scope.setResolved = function() {
		return IssueService.update({issueNr: $scope.issueNr}, {statusId: 3}, function(result) {
				toastSuccess(result);
				$scope.issue.statusId = 3;
				$rootScope.$broadcast('refresh-stream', { objectTypeId: 2, objectId: $scope.issue.issueId });
			}, function error(error) {toastError(error)}
		)};
	$scope.setClosed = function() {
		return IssueService.update({issueNr: $scope.issueNr}, {statusId: 4}, function(result) {
				toastSuccess(result);
				$scope.issue.statusId = 4;
				$rootScope.$broadcast('refresh-stream', { objectTypeId: 2, objectId: $scope.issue.issueId });
			}, function error(error) {toastError(error)}
		)};

}]);

