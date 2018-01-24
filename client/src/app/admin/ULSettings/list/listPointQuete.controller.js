/**
 * Created by tmanson on 15/04/2016.
 */

(function () {
  'use strict';

  angular
    .module('redCrossQuestClient')
    .controller('ULSettingsController', ULSettingsController);

  /** @ngInject */
  function ULSettingsController($log, $localStorage, $location,
                                ULSettingsResource, DateTimeHandlingService)
  {
    var vm = this;
    vm.currentUserRole=$localStorage.currentUser.roleId;



    vm.settings = ULSettingsResource.query().$promise.then(handleResult);


    function handleResult (settings)
    {
      $log.info("Find '"+settings.length+"' settings");
      vm.settings = settings;
      var counti = settings.length;
      var i=0;
      for(i=0;i<counti;i++)
      {
        vm.settings[i].created      = DateTimeHandlingService.handleServerDate(vm.settings[i].created     ).stringVersion;
        vm.settings[i].updated      = DateTimeHandlingService.handleServerDate(vm.settings[i].updated     ).stringVersion;
      }
    }



  }
})();

