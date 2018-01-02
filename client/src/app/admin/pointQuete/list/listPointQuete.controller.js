/**
 * Created by tmanson on 15/04/2016.
 */

(function () {
  'use strict';

  angular
    .module('redCrossQuestClient')
    .controller('ListPointQueteController', ListPointQueteController);

  /** @ngInject */
  function ListPointQueteController($log, $localStorage, $location,
                                    PointQueteResource, DateTimeHandlingService)
  {
    var vm = this;
    vm.currentUserRole=$localStorage.currentUser.roleId;



    vm.pointsQuete = PointQueteResource.query().$promise.then(handleResult);

    vm.doSearch=function()
    {
      var searchParams = {'action':'search','q':vm.search, 'point_quete_type':vm.point_quete_type, 'active':vm.active};

      if(vm.currentUserRole === '9' && vm.admin_ul_id !== null)
      {
        searchParams['admin_ul_id']=vm.admin_ul_id;
      }

      vm.pointsQuete = PointQueteResource.query(searchParams).$promise.then(handleResult);

    };

    vm.createNewPointQuete=function()
    {
      $location.path("/pointsQuetes/edit").replace();
    }



    function handleResult (pointsQuete)
    {
      $log.info("Find '"+pointsQuete.length+"' pointsQuete");
      vm.pointsQuete = pointsQuete;
      var counti = pointsQuete.length;
      var i=0;
      for(i=0;i<counti;i++)
      {
        vm.pointsQuete[i].created      = DateTimeHandlingService.handleServerDate(vm.pointsQuete[i].created     ).stringVersion;
      }
    }



  }
})();

