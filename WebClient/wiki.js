'use strict';

angular.module('grapes.wiki', ['ngRoute'])
.config(['$resourceProvider', function($resourceProvider) {
  // Don't strip trailing slashes from calculated URLs
  $resourceProvider.defaults.stripTrailingSlashes = true;
}])

.config(['$routeProvider', function($routeProvider) {
  $routeProvider.when('/wiki', {
    templateUrl: 'WebClient/wiki.html',
    controller: 'WikiController'
  }).when('/wiki/:pageName?', {
	    templateUrl: 'WebClient/wiki.html',
	    controller: 'WikiController'
	});
}])
.directive('wikiToc', function() {
	return {
		restrict: 'E',
		template: '<div class="wikiToc" id="wiki-toc"></div>',
		link: function($scope, element, attrs) {
			var tocHtml = "<div class='wikiToc'><ul>";
			$('#wikipage').find('h1').each(function(i, obj) {
				var link = "#";
				var label = obj.childNodes[0].data;
				tocHtml += "<li><a href='" + link + "'>" + label + "</li>";

			});
			
			tocHtml += "</ul></div>";
			$('#wikipage').prepend(tocHtml);
		}
	};
})
.factory("DocumentNameService", function($resource) {
	return $resource("Api/documents/name/:name", {name: "@name"});
}).factory("DocumentService", function($resource) {
	return $resource("Api/documents/:id", {id: "@id"});
}).factory("ContentWikiService", function($resource) {
	return $resource("Api/contents/:id/wiki", {id: "@id"});
}).factory("UserBadgesService", function($resource) {
	return $resource("Api/users/:userId/badges", {userId: "@userId"});
}).factory("ContentService", function($resource) {
	return $resource("Api/contents/:id", {id: "@id"});
}).factory("ProjectsService", function($resource) {
	return $resource("Api/projects");
}).factory("UsersService", function($resource) {
	return $resource("Api/users/:userId", {userId: "@userId"});
}).controller('WikiController',
	[ '$rootScope', '$scope', '$resource', '$location', '$routeParams', '$sce', 'DocumentNameService', 'DocumentService', 'ContentService', 'ContentWikiService', 'ProjectsService', 'UsersService', 'UserBadgesService',
	  function($rootScope, $scope, $resource, $location, $routeParams, $sce, DocumentNameService, DocumentService, ContentService, ContentWikiService, ProjectsService, UsersService, UserBadgesService) {
		  $("body").removeClass();
	var breadcrumbs = [
        {link: '#/wiki', label: 'Universe'}
        ];
	$scope.breadcrumbs = breadcrumbs;

	$scope.pageName = "Universe";
	if ($routeParams.pageName) {
		$scope.pageName = $routeParams.pageName;
	}
	$scope.breadcrumbs.push({link: '#/wiki/' + $scope.pageName, label: $scope.pageName });	
	$scope.loadPage = function() {
		DocumentNameService.get({
			name: $scope.pageName
		}, function(document) {
			var templateUrl = "WebClient/wiki.html";

			$scope.document = document;

			$rootScope.$broadcast('refresh-stream', { objectTypeId: document.objectTypeId, objectId: document.objectId });

			
			switch (document.objectTypeId) {

					default:
			    	templateUrl = "WebClient/wiki-generic.html";
						templateUrl = "Api/contents/" + document.latestContentId + "/wiki";
					break;
					
			}
			console.log(templateUrl);

			$scope.templateUrl = templateUrl;

	
			
		}, function(error) {
			$scope.templateUrl = "WebClient/wiki-notfound.html";
			$scope.pageName = $routeParams.pageName;
			toastError(error);
		});
	};

	$('#editWiki').on('shown.bs.modal', function () {
		ContentService.get({
			id: $scope.document.latestContentId
		}, function(content) {
			$scope.content = content;
		}, function(error) {
			toastError(error);
		});
		
		
		  $('#markdown').focus();
		});

	$('#createWiki').on('shown.bs.modal', function () {
		  $('#markdown2').focus();
		});

	
	$('#saveWiki').on('click', function(event) {
		  event.preventDefault(); // To prevent following the link (optional)
			ContentService.save($scope.content,
				function(success) {
				toastSuccess(success);
				$('#editWiki').modal('hide');
				$scope.loadPage();
			}, function(error) {
				toastError(error);
			});
		});

	$('#createPage').on('click', function(event) {
		var document = { name: $scope.pageName, content: $scope.markdown, documentTypeId: 1 };
		  DocumentService.save(document, function(result) {
				  toastSuccess(result);
					$('#createWiki').modal('hide');
					$scope.loadPage();
				  
			  }, function(error) {
					console.log(error);
				  toastError(error);
			  }
		  );
		});

	$scope.markdown = "";
	
	$scope.loadPage();
	
}]);

