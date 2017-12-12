/**
 * Created by tmanson on 15/04/2016.
 */

(function () {
  'use strict';

  angular
    .module('redCrossQuestClient')
    .controller('QueteursController', QueteursController);

  /** @ngInject */
  function QueteursController($log, QueteurResource, $localStorage)
  {
    var vm = this;
    vm.searchType = 0;

    vm.currentUserRole=$localStorage.currentUser.roleId;

    vm.typeBenevoleList=[
      {id:1,label:'Action Sociale'},
      {id:2,label:'Secours'},
      {id:3,label:'Non Bénévole'},
      {id:4,label:'Ancien Bénévole, Inactif ou Adhérent'},
      {id:5,label:'Commerçant'},
      {id:6,label:'Spécial'}
    ];


    //initial search with type 0 (all queteur)
    vm.list = QueteurResource.query({'searchType':0});

    vm.doSearch=function()
    {
      $log.debug("search with type:'"+vm.searchType+"' "+vm.admin_ul_id);

      var searchParams = {'q':vm.search, 'searchType':vm.searchType,  'active':vm.active};


      if(vm.currentUserRole === '9' && vm.admin_ul_id !== null)
      {
        searchParams['admin_ul_id']=vm.admin_ul_id;
      }

      vm.list = QueteurResource.query(searchParams);
    }


  }
})();

