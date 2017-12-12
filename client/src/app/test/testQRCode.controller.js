/**
 * Created by tmanson on 15/04/2016.
 */

(function() {
  'use strict';

  angular
    .module('redCrossQuestClient')
    .controller('TestQRCodeController', TestQRCodeController);

  /** @ngInject */
  function TestQRCodeController($log) {
    var vm = this;

    vm.current = { id: 0, lastName: 'Wayne', firstName: 'Bruce', secteur: '2', mobile: '0631107592', parentAuthorization:'',  temporaryVolunteerForm:''};
    vm.decodedData  = "Nothing found yet";
    vm.errorMessage = "No Error";

    vm.onSuccess = function(data) {
      vm.decodedData = data;
      $log.info(data);
    };
    vm.onError = function(error) {
      vm.errorMessage=error;
    };
    vm.onVideoError = function(error) {
      vm.errorMessageVideo = error;
      $log.error(error);
    };

  }
})();

