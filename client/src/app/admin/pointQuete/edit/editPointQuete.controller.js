/**
 * Created by tmanson on 15/04/2016.
 */

(function () {
  'use strict';

  angular
    .module('redCrossQuestClient')
    .controller('EditPointQueteController', EditPointQueteController);

  /** @ngInject */
  function EditPointQueteController($log, $localStorage, $routeParams, $timeout,
                                    PointQueteResource, DateTimeHandlingService)
  {
    var vm = this;
    vm.currentUserRole=$localStorage.currentUser.roleId;
    var pointQueteId = $routeParams.id;


    vm.createNewPointQuete=function()
    {
      vm.current          = new PointQueteResource();
      vm.current.ul_id    = $localStorage.currentUser.ulId;
      vm.current.ul_name  = $localStorage.currentUser.ulName;
    };


    if (angular.isDefined(pointQueteId))
    {
      PointQueteResource.get({ 'id': pointQueteId }).$promise.then(function(pointQuete)
      {
        vm.current = pointQuete;
        vm.current.created      = DateTimeHandlingService.handleServerDate(vm.current.created).stringVersion;
      });
    }
    else
    {
      vm.createNewPointQuete();
    }

    vm.save = function ()
    {
      //vm.uploadFiles();

      if (angular.isDefined(vm.current.id))
      {
        vm.current.$update(savedSuccessfully, errorWhileSaving);
      }
      else
      {
        vm.current.$save(savedSuccessfully, errorWhileSaving);
      }

    };

    function savedSuccessfully(pointQuete)
    {
      vm.savedSuccessfully= true;
      vm.current          = pointQuete;
      vm.current.created  = DateTimeHandlingService.handleServerDate(vm.current.created).stringVersion;
      $timeout(function () { vm.savedSuccessfully=false; }, 5000);
    }

    function errorWhileSaving(error)
    {
      vm.errorWhileSaving=true;
      vm.errorWhileSavingDetails=error;
    }
  }
})();

