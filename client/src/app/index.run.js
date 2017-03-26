(function() {
  'use strict';

  angular
    .module('client')
    .run(runBlock);



  /** @ngInject */
  function runBlock(authManager /*$rootScope, $http, $location, $localStorage, $log*/)
  {
    //Angular JWT
    authManager.redirectWhenUnauthenticated();

  }

})();
