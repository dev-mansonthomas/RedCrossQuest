/**
 * Created by tmanson on 15/04/2016.
 */

(function() {
  'use strict';

  angular
    .module('redCrossQuestClient')
    .controller('ChangelogController', ChangelogController);

  /** @ngInject */
  function ChangelogController($rootScope, $localStorage)
  {
    var vm = this;
    
    $rootScope.$emit('title-updated', 'Changelog');

    vm.username       = $localStorage.currentUser.username;
    vm.ulName         = $localStorage.currentUser.ulName;
    vm.ulId           = $localStorage.currentUser.ulId;
    vm.id             = $localStorage.currentUser.id;
    vm.queteurId      = $localStorage.currentUser.queteurId;
    vm.currentUserRole= $localStorage.currentUser.roleId;
  }
})();

