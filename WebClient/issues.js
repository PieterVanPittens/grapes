'use strict';

angular.module('grapes.issues', ['ngRoute'])
.config(['$routeProvider', function($routeProvider) {
  $routeProvider.when('/issues', {
    templateUrl: 'WebClient/issues.html',
    controller: 'IssuesController'
  });
}])
.controller('IssuesController',
		[ '$rootScope', '$scope', '$location', '$resource', function($rootScope, $scope, $location, $resource) {
			$("body").removeClass();
			$rootScope.toggleStream(false);


			var dataTable = $('#issuesTable').dataTable({
				"bPaginate" : false,
				"ajax" : "Api/issues",
				"bSortClasses": false,
				"stripeClasses":[],
				"order": [[ 1, "asc" ]],
				"dataSrc": "data",
				"columns" : [ {
					"data" : "issueNr"
				}, {
					"data" : "projectName"
				}, {
					"data" : "componentName"
				}, {
					"data" : "issueType"
				}, {
					"data" : "status"
				}, {
					"data" : "assignedTo"
				}, {
					"data" : "createdAt"
				}, {
					"data" : "createdBy"
				} ],
				
				"columnDefs": [
	               {
	                   // The `data` parameter refers to the data for the cell (defined by the
	                   // `data` option, which defaults to the column being worked with, in
	                   // this case `data: 0`.
	                   "render": function ( data, type, row ) {

	                	   
	                	   return renderIssueLink(row);
	                       //return '<a>' + data +'</a>'; // ('+ row[3]+')';
	                   },
	                   "targets": 0
	               },
					{
						"render": function ( data, type, row ) {

							var day = moment.unix(row.createdAt);
							return day.format("l LT");
						},
						"targets": 6
					}
	               ]
				
			});
			$('#issuesTable').on('click', 'tr', function() {
				if ($(this).hasClass('selected')) {
					$(this).removeClass('selected');
				} else {
					dataTable.$('tr.selected').removeClass('selected');
					$(this).addClass('selected');
				}
			});
			$('#button').click(function() {
				dataTable.row('.selected').remove().draw(false);
			});
			$("#searchbox").keyup(function() {
				dataTable.fnFilter(this.value);
			});
		} ]);

