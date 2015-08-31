'use strict';

angular.module('grapes.admin-config', ['ngRoute'])

.config(['$routeProvider', function($routeProvider) {
  $routeProvider.when('/config', {
    templateUrl: 'WebClient/admin-config.html',
    controller: 'ConfigCtrl'
  });
}])

.controller('ConfigCtrl', [function() {

}]);