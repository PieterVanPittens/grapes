'use strict';
// Declare app level module which depends on views, and components
var grapes = angular.module('grapes', [
  'ngRoute',
  'ngCookies',
  'ngResource',
  'ngSanitize',
  'xeditable',
  'infinite-scroll',
  'grapes.home',
  'grapes.action-log',
  'grapes.project',
  'grapes.admin-users',
  'grapes.admin-components',
  'grapes.admin-projects',
  'grapes.wiki',
  'grapes.issues',
  'grapes.issue',
  'grapes.map',
  'grapes.admin-config'
]).
config(['$routeProvider', '$locationProvider', function($routeProvider, $locationProvider) {
	  $routeProvider
	  .otherwise({redirectTo: 'home'});
  //use the HTML5 History API
  //$locationProvider.html5Mode(true);
  
}])
	.directive('grapesProjects', function() {
	return {
		restrict: 'E',
		templateUrl: 'WebClient/grapes-projects/grapes-projects-template.html',
		controller: ['$scope', '$resource', function($scope, $resource) {

			$scope.getProjects = function(projects) {
				var service = $resource("Api/projects");
				service.get(function(projects) {
					$scope.projects = projects.data;
				});

			};
		}],
		link: function(scope, iElement, iAttrs, ctrl) {
			scope.getProjects();
		}
	}})


.run(function($rootScope, $q, $location, authService, editableOptions) {
	console.log('run');
	$rootScope.loadingDone = false;
	$rootScope.auth = authService;

	var loginPromise;
	loginPromise = authService.loginFromCookie();

	loginPromise.then(function (success) {
		console.log('loginpromise.then');
		$rootScope.auth = authService;
    	$rootScope.loadingDone = true;
		
		
	});

	editableOptions.theme = 'bs3'; // bootstrap3 theme

	toastr.options = {
			  "closeButton": false,
			  "debug": false,
			  "newestOnTop": true,
			  "progressBar": false,
			  "positionClass": "toast-bottom-full-width",
			  "preventDuplicates": false,
			  "onclick": null,
			  "showDuration": "400",
			  "hideDuration": "1000",
			  "timeOut": "2000",
			  "extendedTimeOut": "2000",
			  "showEasing": "swing",
			  "hideEasing": "linear",
			  "showMethod": "fadeIn",
			  "hideMethod": "fadeOut"
			}




		// allows other controllers to toggle activitystream on/of
		$rootScope.toggleStream = function(on) {
			if (on) {
				$("#wrapper").addClass("toggled");
			} else {
				$("#wrapper").removeClass("toggled");
			}
		}
	
})
.controller(
	'NavController',
		function($rootScope, $scope, $window, $location, $route, $resource, authService) {


			/* stream toggle */
			$("#stream-toggle").click(function(e) {
				e.preventDefault();
				$("#wrapper").toggleClass("toggled");
			});

		$scope.restart = function() {
			$location.path('/');
		    $window.location.reload();
		};
		
		
		/* login dialog */
		$scope.login = function() {
			var promise = authService.login($scope.username, $scope.password);
			
			promise.then(function() {
				$('#loginModal').modal('hide');
				$route.reload();
			}, function(error) {
				toastError(error);
			});
			
		};
		
		$scope.password = "";
		$scope.username = "";
		
        $('#username').keypress(function (e) {
      	  if (e.which == 13) {
        	    $scope.login();
      	    return false;
      	  }
      	});
        $('#password').keypress(function (e) {
      	  if (e.which == 13) {
      	    $scope.login();
      	    return false;
      	  }
      	});
	
		$('#loginModal').on('shown.bs.modal', function (e) {
				$("#username").focus();
			});
		

		/* logoff */
		$scope.logoff = function() {
			authService.logoff();
			$scope.restart();
		};
		
		/* create issue */
		$scope.createIssue = function() {
			var IssueService = $resource("Api/issues");
			IssueService.save($scope.issue
				, function(actionResult) {
					toastSuccess(actionResult);
					var newIssue = { 
							issueTypeId: $scope.issue.issueTypeId,
							componentId: $scope.issue.componentId,
							assignedToUserId: $scope.issue.assignedToUserId,
							projectId: $scope.issue.projectId
					}
					$scope.issue = newIssue;
					$("#subject").focus();

				}, function(error) {
					toastError(error);
				}
			);			
		}
		$('#createIssueModal').on('shown.bs.modal', function () {
			$("#subject").focus();

			$scope.issue = {issueTypeId: 1};


			var userId = $rootScope.auth.currentUser.userId;
			var ProjectsService = $resource("Api/users/" + userId + "/projects");
			ProjectsService.get(function(data) {
				$scope.projects = data.data;
			});

		
			$scope.$watch("issue.projectId", function(newValue, oldValue) {
				var ComponentsService = $resource("Api/projects/" + newValue + "/components");
				ComponentsService.get(function(data) {
					$scope.components = data.data;
				});
				var UsersService = $resource("Api/projects/" + newValue + "/users");
				UsersService.get(function(data) {
					$scope.users = data.data;
				});
			});
		});

			//Dropzone.options.myAwesomeDropzone = { maxFilesize: 1 };
			new Dropzone("#file-to-issues-zone", { url: "Api/projects/1/createIssuesFromFile" });


		})
.controller(
	'StreamController',
	[
	'$scope', '$resource', 
	function($scope, $resource) {
		$scope.stream = null;
		console.log('StreamController here!');

		$scope.freshFeedItem = function() {
			return 	{
				feed: '',
				objectTypeId: 12,
				objectId: 0,
				targetObjectTypeId: 12,
				targetObjectId: 0
			};
			
		};


		
		$scope.newFeedItem = $scope.freshFeedItem();

		$('#post-feed-button').click(function() {
			$scope.newFeedItem.targetObjectTypeId = $scope.targetObject.objectTypeId;
			$scope.newFeedItem.targetObjectId = $scope.targetObject.objectId;
			
			var ActivityStreamService = $resource("Api/activities");
			ActivityStreamService.save($scope.newFeedItem
				, function(actionResult) {
					toastSuccess(actionResult);
					$scope.$broadcast('refresh-stream', { objectTypeId: $scope.targetObject.objectTypeId, objectId: $scope.targetObject.objectId });
				}, function(error) {
					toastError(error);
				}
			);
			
		});
		
		 $scope.$on('refresh-stream', function(events, args) {
			 console.log('refresh-stream');
			 console.log(args);
			 $scope.newFeedItem = $scope.freshFeedItem();
			 $scope.targetObject = { objectTypeId: args.objectTypeId, objectId: args.objectId };
			 if (args.objectId == undefined) {
				 $scope.isStreamLoaded = false;
				 $scope.streamHasError = true;
				 console.log('no objectid');
			 } else {
				 switch (args.objectTypeId) {
				    case 1: // User
				    	var ActivityStreamService = $resource("Api/users/:userId/activities", {userId: "@userId"});
					 	ActivityStreamService.get({userId:args.objectId}, function(stream) {
				    		$scope.stream = stream;
							 $scope.isStreamLoaded = true;
							 $scope.hasStreamError = false;
					 	}, function(error) {
							 $scope.isStreamLoaded = false;
							 $scope.hasStreamError = true;
				    		toastError(error);
				    	});
					 	break;
				    case 2: // Issue
				    	var ActivityStreamService = $resource("Api/issues/:id/activities", {id: "@id"});
					 	ActivityStreamService.get({id:args.objectId}, function(stream) {
				    		$scope.stream = stream;
							 $scope.isStreamLoaded = true;
							 $scope.hasStreamError = false;
					 	}, function(error) {
							 $scope.isStreamLoaded = false;
							 $scope.hasStreamError = true;
				    		toastError(error);
				    	});
					 	break;
				    case 3: // Project
				    	var ActivityStreamService = $resource("Api/projects/:projectId/activities", {projectId: "@projectId"});
					 	ActivityStreamService.get({projectId:args.objectId}, function(stream) {
				    		$scope.stream = stream;
							 $scope.isStreamLoaded = true;
							 $scope.hasStreamError = false;
				    	}, function(error) {
				    		toastError(error);
							 $scope.isStreamLoaded = false;
							 $scope.hasStreamError = true;
				    	});
					 	break;
				    case 9: // Dashboard
				    	var ActivityStreamService = $resource("Api/dashboards/:id/activities", {id: "@id"});
					 	ActivityStreamService.get({id:args.objectId}, function(stream) {
				    		$scope.stream = stream;
							 $scope.isStreamLoaded = true;
							 $scope.hasStreamError = false;
				    	}, function(error) {
				    		toastError(error);
							 $scope.isStreamLoaded = false;
							 $scope.hasStreamError = true;
				    	});
					 	break;
				}				 
			 }
		 
		  });
			
		}
	]);
