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
                                $timeout,
                             ExportDataResource)
  {
    var vm = this;
    vm.currentUserRole = $localStorage.currentUser.roleId;
    vm.ulId = $localStorage.currentUser.ulId;
    vm.settings        = $localStorage.guiSettings;
    vm.running         = false;

    $rootScope.$emit('title-updated', 'Export des données de l\'Unité Locale');


    vm.exportDataSuccess = function(numberOfRowsExported)
    {
      vm.running              = false;
      vm.numberOfRowsExported = numberOfRowsExported;
      vm.errorWhileSending    = null;
    };


    vm.exportDataError = function(error)
    {
      vm.errorWhileSending = error;
      vm.running           = false;
    };

    vm.send = function()
    {
      vm.running = true;
      vm.password = Math.random().toString(36).slice(2) + Math.random().toString(36).slice(2);
      ExportDataResource.get({'password':vm.password});//.$promise.then(vm.exportDataSuccess, vm.exportDataError);


/*
      $http.post('/rest/'+vm.currentUserRole+'/ul/'+vm.ulId+'/exportData', { 'password': vm.password, 'year': '' })
        .then(function successCallback(response)
          {
            $log.error('response');
          },
          function errorCallBack(error){
            $log.error(error);
          });
*/

    };

  }
})();

