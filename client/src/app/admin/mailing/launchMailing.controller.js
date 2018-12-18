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
                             MailingResource)
  {
    var vm = this;
    vm.currentUserRole = $localStorage.currentUser.roleId;
    vm.settings        = $localStorage.guiSettings;
    vm.running         = false;

    $rootScope.$emit('title-updated', 'Mailing de remerciement');

    vm.typeBenevoleList=[
      '',
      'Action Sociale'                        ,
      'Secours'                               ,
      'Bénévole d\'un Jour'                   ,
      'Ancien Bénévole, Inactif ou Adhérent'  ,
      'Commerçant'                            ,
      'Spécial'
    ];

    vm.handleMailingSummaryResponse=function(response)
    {
      vm.mailingSummary = response;
      vm.computeProgress();
    };


    MailingResource.get().$promise.then(vm.handleMailingSummaryResponse);


    vm.stopProcessing=function()
    {
      vm.stop = true;
    };

    vm.handleMailingSending=function(mailingReport)
    {
      vm.mailingReport = mailingReport;

      if(vm.mailingReport == null || vm.mailingReport.length === 0)
      {// no more email to send, stop processing.
        vm.stop    = true;
        vm.running = false;
      }
      //recompute statistics
      MailingResource.get().$promise.then(vm.handleMailingSummaryResponse);
      if(vm.stop !== true)
      {
        $timeout(function () {vm.send(); }, 2000);
      }
      else
      {//not in stop function, so that we let current batch to complete.
        vm.running = false;
      }
    };

    vm.handleMailingSendingError = function(error)
    {
      vm.errorWhileSending = error;
      vm.running = false;
    };

    vm.send = function()
    {
      vm.running = true;
      MailingResource.save(vm.handleMailingSending, vm.handleMailingSendingError);
    };


//{"UNSENT_EMAIL":[{"secteur":1,"count":1},{"secteur":2,"count":57},{"secteur":3,"count":51},{"secteur":6,"count":4}],"EMAIL_SUCCESS":[],"EMAIL_ERROR":[]}
    vm.computeProgress=function()
    {
      if(vm.mailingSummary == null || vm.mailingSummary["UNSENT_EMAIL"] == null)
      {
        return 0;
      }

      vm.totalToBeSent = 0;
      var unsent = vm.mailingSummary["UNSENT_EMAIL"];
      for(var i=0;i<unsent.length;i++)
      {
        vm.totalToBeSent+=unsent[i].count;
      }

      vm.totalSent = 0;
      var email_success = vm.mailingSummary["EMAIL_SUCCESS"];
      for(i=0;i<email_success.length;i++)
      {
        vm.totalSent+=email_success[i].count;
      }

      vm.totalError = 0;
      var email_error = vm.mailingSummary["EMAIL_ERROR"];
      for(i=0;i<email_error.length;i++)
      {
        vm.totalError+=email_error[i].count;
      }

      vm.totalEmail = vm.totalToBeSent + vm.totalSent + vm.totalError;

      vm.pourcentageSent  = (vm.totalSent /vm.totalEmail * 100).toFixed(2);
      vm.pourcentageError = (vm.totalError/vm.totalEmail * 100).toFixed(2);

    };

  }
})();

