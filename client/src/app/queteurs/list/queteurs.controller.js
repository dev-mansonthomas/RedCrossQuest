/**
 * Created by tmanson on 15/04/2016.
 */

(function () {
  'use strict';

  angular
    .module('client')
    .controller('QueteursController', QueteursController);

  /** @ngInject */
  function QueteursController($log, QueteurResource, $localStorage)
  {
    var vm = this;
    vm.searchType = 0;

    vm.currentUserRole=$localStorage.currentUser.roleId;

    //initial search with type 0 (all queteur)
    vm.list = QueteurResource.query({'searchType':0});

    vm.doSearch=function()
    {
      $log.debug("search with type:'"+vm.searchType+"' "+vm.admin_ul_id);

      var searchParams = null;
      if(vm.currentUserRole === '9' && vm.admin_ul_id !== null)
      {
        searchParams = {'searchType':vm.searchType, 'admin_ul_id':vm.admin_ul_id};
      }
      else
      {
        searchParams = {'searchType':vm.searchType};
      }

      vm.list = QueteurResource.query(searchParams);
    }


  }
})();

