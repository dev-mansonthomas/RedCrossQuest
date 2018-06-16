/**
 * Created by tmanson on 15/04/2016.
 */

(function () {
  'use strict';

  angular
    .module('redCrossQuestClient')
    .controller('MailingController', MailingController);

  /** @ngInject */
  function MailingController($rootScope, $log, $localStorage, $timeout,
                             MailingResource, DateTimeHandlingService)
  {
    var vm = this;
    vm.currentUserRole = $localStorage.currentUser.roleId;
    vm.settings        = $localStorage.guiSettings;




  }
})();

