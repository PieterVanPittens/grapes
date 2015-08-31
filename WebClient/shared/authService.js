    'use strict';

    angular
        .module('grapes')
        .factory('authService', 

    function authService($http, $q, $cookieStore, base64Service) {
        var service = {};
    	service.loggedIn = false;
    	service.publicUser = false;
    	service.currentUser = {};

        service.isLoggedIn = function () {
        	return service.loggedIn;
        }
        service.isPublicUser = function () {
        	return service.publicUser;
        }
        service.login = function(username, password) {
        	var deferred = $q.defer();

        	$http({
			       withCredentials: false,
			       method: 'post',
			       url: 'Api/authenticate',
			       headers: {'Content-Type': 'application/x-www-form-urlencoded'},
			       data: { username: username, password: password }
			 })
             .success(function (success) {
            	 deferred.resolve(success);
            	 if (success.userId == 1) {
                 	service.publicUser = true;
            	 } else {
                 	service.publicUser = false;
            	 }
            	 var authdata = base64Service.encode(success.name + ':' + success.password);
                 service.currentUser = success;
                 $http.defaults.headers.common.Authorization = 'Basic ' + authdata; // jshint ignore:line
                 $cookieStore.put('grapesuser', authdata);
            	 service.loggedIn = true;

             }).error(function(error) {
            	 deferred.reject(error);
             	alert('Could not login. Error: ' + error.message);
             	service.loggedIn = false;
             });
        	return deferred.promise;
             
        }

        /**
         * login public user
         */
        service.loginPublic = function() {
        	return service.login("guest", "guest");
        }

        /**
         * initial login: either cookie or public
         */
        service.loginFromCookie = function() {
            var authdata = $cookieStore.get('grapesuser');
            if (authdata != undefined && authdata != null) {
            	var decoded = base64Service.decode(authdata);
            	var tokens = decoded.split(":");
            	var username = tokens[0];
            	var password = tokens[1];
            	
            	return service.login(username, password);
            } else {
            	return service.login("guest", "guest");
            }
        }

        service.logoff = function() {
        	service.currentUser = null;
        	$http.defaults.headers.common.Authorization = "";
        	$cookieStore.remove("grapesuser");
        }
        
        return service;

});

