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

    vm.pageNumber       = 1;
    vm.rowCount         = 0;

    $rootScope.$emit('title-updated', 'Liste des inscriptions de Quêteur en attente de validation');

    vm.registrationStatusList=[
      {id:0,label:'Inscription en attente'},
      {id:1,label:'Inscription validée'},
      {id:2,label:'Inscription rejetée'}
    ];

    vm.registrationStatus = 0;

    vm.doSearch=function()
    {
      vm.registrations = QueteurResource.listPendingQueteurRegistration({'registration_status':vm.registrationStatus, 'pageNumber':vm.pageNumber}).$promise.then(handleResult).catch(function(e){
        $log.error("error searching listPendingQueteurRegistration", e);
      });
    };

    vm.doSearch();


    function handleResult (pageableResponse)
    {
      vm.rowCount      = pageableResponse.count;
      vm.registrations = pageableResponse.rows ;

      $log.info("Find '"+vm.registrations.length+"' registrations");

      var counti     = vm.registrations.length;
      for(var i=0;i<counti;i++)
      {
        vm.registrations[i].created      = DateTimeHandlingService.handleServerDate(vm.registrations[i].created     ).stringVersion;
      }
    }
  }
})();

