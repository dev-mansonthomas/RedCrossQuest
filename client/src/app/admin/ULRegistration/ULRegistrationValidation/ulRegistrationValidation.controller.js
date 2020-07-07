/**
 * Created by tmanson on 15/04/2016.
 */

(function () {
  'use strict';

  angular
    .module('redCrossQuestClient')
    .controller('ULRegistrationValidationController', ULRegistrationValidationController);

  /** @ngInject */
  function ULRegistrationValidationController($rootScope, $log, $localStorage, $routeParams, $timeout, $location, DateTimeHandlingService, UniteLocaleResource)
  {
    var vm = this;
    $rootScope.$emit('title-updated', "Validation de l'inscription d'une UL ");

    var registrationId      = $routeParams.id;

    UniteLocaleResource.getRegistration({id:registrationId}).$promise.then(handleResult).catch(function(e){
      $log.error("error getting ULRegistration "+registrationId, e);
    });

    function handleResult (uniteLocalDetails)
    {
      vm.settings = uniteLocalDetails;
      vm.settings.date_demarrage_rcq = DateTimeHandlingService.handleServerDate(vm.settings.date_demarrage_rcq).stringVersion;
      vm.settings.created            = DateTimeHandlingService.handleServerDate(vm.settings.created           ).stringVersion;
      vm.settings.approval_date      = DateTimeHandlingService.handleServerDate(vm.settings.approval_date     ).stringVersion;
    }


    vm.save = function ()
    {
      vm.settings.$registrationDecision(savedSuccessfully, errorWhileSaving);
    };





    function savedSuccessfully()
    {
      vm.savedSuccessfully= true;
      vm.errorWhileSaving = false;
      $timeout(function () { vm.savedSuccessfully=false; }, 5000);
    }

    function errorWhileSaving(error)
    {
      vm.errorWhileSaving        = true;
      vm.errorWhileSavingDetails = JSON.stringify(error.data);
    }

  }
})();

