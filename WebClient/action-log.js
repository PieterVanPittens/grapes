'use strict';

angular.module('grapes.action-log', ['ngRoute'])
.config(['$routeProvider', function($routeProvider) {
  $routeProvider.when('/actionlog', {
    templateUrl: 'WebClient/action-log.html',
    controller: 'ActionLogController'
	});
}])
.controller('ActionLogController',
[ '$scope', '$rootScope', '$location', '$resource', '$routeParams', function($scope, $rootScope, $location, $resource, $routeParams) {

	var ActionLogService = $resource("Api/actionlog?chunk=:chunk", {chunk: "@chunk"});

	$scope.items = [];
	$scope.isBusy = false;

	var chunk = 0;
	$scope.loadNext = function() {
		if ($scope.isBusy) {
			return;
		}
		$scope.isBusy = true;
		
		ActionLogService.query({chunk: chunk}, function(items) {

			for (var i = 0; i < items.length; i++) {
		        $scope.items.push(items[i]);
			}
		    $scope.isBusy = false;

		    chunk++;
			
		}, function(error) {
			toastError(error);
		});

		
		
	};
	
	

	/*
	setTimeout(function() {
		var chunk = 0;

	$('#infiniteActionLog').infinitescroll({
	  //dataSource is required to append additional content
	  dataSource: function(helpers, callback){
	  console.log("datasource");
	    //passing back more content
		page++;
	    callback({ content: 'sdfsdfsdfsfsdfsdfdsf'+page+'<br>' });
	  },
	  hybrid: false
	});
	}, 500);
	
	
	
	ActionLogService.query(function(actionlog) {
		$scope.actionlog = actionlog;
	
	}, function(error) {
		toastError(error);
	});
	*/
	
}]);

