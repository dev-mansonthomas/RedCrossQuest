/**
 * Created by tmanson on 15/04/2016.
 */

(function () {
  'use strict';

  angular
    .module('redCrossQuestClient')
    .controller('ExportDataController', ExportDataController);

  /** @ngInject */
  function ExportDataController($rootScope, $log, $localStorage, $http,
                                $timeout, ExportDataResource)
  {
    var vm = this;
    vm.currentUserRole = $localStorage.currentUser.roleId;
    vm.ulId            = $localStorage.currentUser.ulId;
    vm.settings        = $localStorage.guiSettings;
    vm.running         = false;

    $rootScope.$emit('title-updated', 'Export des données de l\'Unité Locale');


    vm.exportDataSuccess = function(response)
    {
      vm.running           = false;
      vm.errorWhileSending = null;

      vm.status        = response.status;
      vm.email         = response.email;
      vm.fileName      = response.fileName;
      vm.numberOfRows  = response.numberOfRows;
      vm.exportInProgress = false;
    };


    vm.exportDataError = function(error)
    {
      vm.errorWhileSending = error;
      vm.running           = false;
      vm.exportInProgress = false;
    };

    vm.send = function()
    {
      if (document.activeElement) {
        document.activeElement.blur();
      }
      vm.running  = true;
      vm.status   = null;
      //vm.password = Math.random().toString(36).slice(2) + Math.random().toString(36).slice(2);
      vm.exportInProgress = true;
      // Send a non-empty body to bypass the anti-scan fail-fast filter in
      // server/public/rest/index.php which rejects POST/PUT with Content-Length: 0
      ExportDataResource.save({}, {export: true}).$promise.then(vm.exportDataSuccess, vm.exportDataError);
    };

  }
})();

