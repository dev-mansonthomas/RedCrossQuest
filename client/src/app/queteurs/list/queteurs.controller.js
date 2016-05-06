/**
 * Created by tmanson on 15/04/2016.
 */

(function () {
  'use strict';

  angular
    .module('client')
    .controller('QueteursController', QueteursController);

  /** @ngInject */
  function QueteursController($log, QueteurResource)
  {
    var vm = this;
    vm.searchType = 0;
    //initial search with type 0 (all queteur)
    vm.list = QueteurResource.query({'searchType':0});

    vm.doSearch=function()
    {
      $log.debug("search with type:'"+vm.searchType+"'")
      vm.list = QueteurResource.query({'searchType':vm.searchType});
    }


  }
})();

