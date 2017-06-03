/**
 * Created by tmanson on 15/04/2016.
 */

(function() {
  'use strict';

  angular
    .module('client')
    .controller('GraphController', GraphController);

  /** @ngInject */
  function GraphController($log, GraphResource) {
    var vm = this;





    vm.grantAccessToGraph=function()
    {

      GraphResource.$create().$promise.then(
        function success(creationDate)
        {
          alert(creationDate);
        },
      function error(error)
      {
        $log.error(error);
      });

    }





  }
})();

