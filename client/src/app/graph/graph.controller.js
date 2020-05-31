/**
 * Created by tmanson on 15/04/2016.
 */

(function() {
  'use strict';

  angular
    .module('redCrossQuestClient')
    .controller('GraphController', GraphController);

  /** @ngInject */
  function GraphController($rootScope, $log, $interval, $localStorage,
                           GraphResource, DateTimeHandlingService)
  {
    var vm = this;

    $rootScope.$emit('title-updated', 'Accès aux graphiques');

    vm.currentUserRole = $localStorage.currentUser.roleId;
    vm.currentUlId     = $localStorage.currentUser.ulId;
    //show debug info on how the countdown is calculated
    vm.showDebug = false;

    vm.tokenAndExpirationDate = GraphResource.get();
    vm.tokenAndExpirationDate.$promise.then(function success(data){
      vm.showGraphs     = true;
      vm.tokenAndExpirationDate.token_expiration_local = DateTimeHandlingService.handleServerDate(data.tokenExpiration).stringVersion;
      vm.token = data.validToken;
    }).catch(function(e){
      $log.error("error searching for Graph", e);
    });
  }
})();

