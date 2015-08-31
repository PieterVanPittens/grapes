'use strict';

angular.module('grapes.project', ['ngRoute'])
    .config(['$routeProvider', function($routeProvider) {
        $routeProvider.when('/project/:identifier?', {
            templateUrl: 'WebClient/project.html',
            controller: 'ProjectController'
        });
    }])
    .controller('ProjectController',
    [ '$rootScope', '$scope', '$location', '$resource', '$routeParams', function($rootScope, $scope, $location, $resource, $routeParams) {
        $("body").removeClass();

        if ($routeParams.identifier) {
            $scope.identifier = $routeParams.identifier;
        }

        $('#tabs a').click(function (e) {
            e.preventDefault()
            $(this).tab('show')
        });

        var projectService = $resource("Api/projects/identifier/:identifier",  {identifier: '@identifier'});
        var componentService = $resource("Api/components/:id", {id: "@id"}, {
            update: {
                method: 'PUT' // this method issues a PUT request
            }
        });

        var aciService = $resource("Api/projects/:id/users",  {id: '@id'});


        var selectedId = "";

        projectService.get({
            identifier: $scope.identifier
        }, function(project) {
            $scope.project = project;


            $rootScope.toggleStream(true);
            $rootScope.$broadcast('refresh-stream', { objectTypeId: 3, objectId: project.projectId });





            $scope.updateRole = function(data) {
                var userId = data.currentTarget.dataset.userid;
                var roleId = data.currentTarget.value;

                aciService.save({id: $scope.project.projectId}, {userId: userId, roleId: roleId, projectId: $scope.project.projectId},
                function (result) {
                    toastSuccess(result);
                }, function (error) {
                        toastError(error);
                    }
                );
            };

            var isNew = false;


            var dataTableUsers = $('#usersTable').DataTable({
                "bPaginate" : false,
                "ajax" : "Api/projects/" + project.projectId + "/users/all",
                "bSortClasses": false,
                "stripeClasses":[],
                "order": [[ 1, "asc" ]],
                "dataSrc": "data",
                "columns" : [ {
                    "data" : "name"
                }, {
                    "data" : "displayName"
                }, {
                    "data" : "roleName"
                }],
                "columnDefs": [
                    {
                        // The `data` parameter refers to the data for the cell (defined by the
                        // `data` option, which defaults to the column being worked with, in
                        // this case `data: 0`.
                        "render": function ( data, type, row ) {

                            var id = "select" + row.userId;

                            var html = "<select id='" + id + "' data-userid='" + row.userId + "' data-roleid='" + row.roleId + "'>";
                            html += "<option value='null'" + (row.roleId == null ? " selected='selected'" : "")+ ">None</option>";
                            html += "<option value='3'" + (row.roleId == 3 ? " selected='selected'" : "")+ ">Member</option>";
                            html += "<option value='2'" + (row.roleId == 2 ? " selected='selected'" : "")+ ">Lead</option>";
                            html += "<option value='1'" + (row.roleId == 1 ? " selected='selected'" : "")+ ">Admin</option>";
                            html += "</select>";

                            $("#"+id).change(function($data) {$scope.updateRole($data);});


                            return html;
                        },
                        "targets": 2
                    }
                ]

            });

            var dataTableComponents = $('#componentsTable').DataTable({
                "bPaginate" : false,
                "ajax" : "Api/projects/" + project.projectId + "/components",
                "bSortClasses": false,
                "stripeClasses":[],
                "order": [[ 0, "asc" ]],
                "dataSrc": "data",
                "columns" : [ {
                    "data" : "name"
                }, {
                    "data" : "description"
                }]

            });

            var selectedComponent = "";


            $scope.openCreate = function() {
                $scope.component = { projectId: project.projectId };
                var modal = $('#modalComponent');
                modal.find('.modal-title').text('New Component');
                modal.modal('show');
                isNew = true;
            };

            $scope.openUpdate = function() {
                var modal = $('#modalComponent');
                modal.find('.modal-title').text('Edit Component');
                isNew = false;

                componentService.get({
                    id : selectedComponent.componentId
                }, function(success) {
                    $scope.component = success;
                    modal.modal('show');
                }, function(error) {
                    toastError(error);
                });
            };


            $scope.save = function(component) {
                $scope.component = component;
                if (isNew) {
                    componentService.save(component, function(success) {
                        //console.log(success);
                        $scope.master = angular.copy(component);
                        $('#modalComponent').modal('hide');
                        toastSuccess(success);
                        $('#componentsTable').DataTable().ajax.reload();

                    }, function(error) {
                        $scope.error = error;
                        toastError(error);
                    });
                } else {
                    componentService.update({id: component.componentId}, component, function(success) {
                        $scope.master = angular.copy(component);
                        $('#modalComponent').modal('hide');
                        toastSuccess(success);
                        $('#componentsTable').DataTable().ajax.reload();
                    }, function(error) {
                        toastError(error);
                    });
                }
            };

            $("#searchbox").keyup(function() {
                dataTableComponents.fnFilter(this.value);
            });

            $('#componentsTable').on('click', 'tr', function() {
                if ($(this).hasClass('selected')) {
                    $(this).removeClass('selected');
                    selectedComponent = null;
                } else {
                    dataTableComponents.$('tr.selected').removeClass('selected');
                    $(this).addClass('selected');

                    var row = dataTableComponents.row( this );
                    var data = row.data();
                    selectedComponent = data;
                }
            });


        }, function(error) {
            toastError(error);
        });


/*



*/






    }]);
