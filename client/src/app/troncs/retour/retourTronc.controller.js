/**
 * Created by tmanson on 15/04/2016.
 */

(function() {
  'use strict';

  angular
    .module('client')
    .controller('RetourTroncController', RetourTroncController);

  /** @ngInject */
  function RetourTroncController($log) {
    var vm = this;

    vm.current = { id: 0, lastName: 'Wayne', firstName: 'Bruce', secteur: '2', mobile: '0631107592', parentAuthorization:'',  temporaryVolunteerForm:''};

    vm.save = save;

    function save()
    {
      $log.debug("Saved called");
      $log.debug(vm.current);
    }

  }
})();

