(function() {
  'use strict';

  angular
    .module('client')
    .run(runBlock);

  /** @ngInject */
  function runBlock($rootScope, $http, $location, $localStorage, $log, jwtHelper)
  {
    //check if there's a token and it's not expired. Otherwise, redirect the page to the login page.
    $rootScope.$on('$routeChangeStart', function(event, next/*, current*/)
    {
      if (next == 'login' || next.$$route.originalPath == '/resetPassword') return;

      var token = $localStorage.RCQ_JWT_Token;

      if (!token || jwtHelper.isTokenExpired(token)) {
        $location.path('/login').replace();
      }
    });
  }
})();
