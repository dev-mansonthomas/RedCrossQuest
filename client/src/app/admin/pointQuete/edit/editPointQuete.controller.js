/**
 * Created by tmanson on 15/04/2016.
 */

(function () {
  'use strict';

  angular
    .module('redCrossQuestClient')
    .controller('EditPointQueteController', EditPointQueteController);

  /** @ngInject */
  function EditPointQueteController($log, $localStorage, $routeParams,
                                    PointQueteResource, DateTimeHandlingService)
  {
    var vm = this;
    vm.currentUserRole=$localStorage.currentUser.roleId;
    var pointQueteId = $routeParams.id;


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


    vm.createNewPointQuete=function()
    {
      vm.current          = new PointQueteResource();
      vm.current.ul_id    = vm.ulId;
      vm.current.ul_name  = $localStorage.currentUser.ulName;
    };

  }
})();

