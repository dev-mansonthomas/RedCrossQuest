/**
 * Created by tmanson on 15/04/2016.
 */

(function () {
  'use strict';

  angular
    .module('redCrossQuestClient')
    .controller('ListRegistrationController', ListRegistrationController);

  /** @ngInject */
  function ListRegistrationController($rootScope, $log, $localStorage, $location,
                                    QueteurResource, DateTimeHandlingService)
  {
    var vm             = this;
    vm.currentUserRole = $localStorage.currentUser.roleId;
    vm.ul              = $localStorage.guiSettings.ul;
    $rootScope.$emit('title-updated', 'Liste des inscriptions de Quêteur en attente de validation');

    vm.registrationStatusList=[
      {id:0,label:'Inscription en attente'},
      {id:1,label:'Inscription validée'},
      {id:2,label:'Inscription rejetée'}
    ];

    vm.registrationStatus = 0;

    vm.registrations = QueteurResource.listPendingQueteurRegistration({'registration_status':vm.registrationStatus}).$promise.then(handleResult);

    vm.doSearch=function()
    {
      vm.registrations = QueteurResource.listPendingQueteurRegistration({'registration_status':vm.registrationStatus}).$promise.then(handleResult);
    };


    function handleResult (registrations)
    {
      $log.info("Find '"+registrations.length+"' registrations");

      vm.registrations = registrations;
      var counti     = registrations.length;
      for(var i=0;i<counti;i++)
      {
        vm.registrations[i].created      = DateTimeHandlingService.handleServerDate(vm.registrations[i].created     ).stringVersion;
      }
    }
  }
})();

