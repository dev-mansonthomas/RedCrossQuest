(function() {
  'use strict';

  angular
    .module('redCrossQuestClient')
    .run(runBlock);

  /** @ngInject */
  function runBlock($rootScope, $http, $location, $localStorage, $log,
                    jwtHelper)
  {
    // any controller can change the page title using $rootScope.$emit('title-updated', 'my new title');
    $rootScope.$on('title-updated', function(event, newTitle) {
      $rootScope.pageTitle = 'RedCrossQuest - ' + newTitle;
    });


    //check if there's a token and it's not expired. Otherwise, redirect the page to the login page.
    $rootScope.$on('$routeChangeStart', function(event, next /*, current*/)
    {
      if (angular.isDefined(next.$$route) &&
                             (
                               next.$$route.originalPath === '/login'         ||
                               next.$$route.originalPath === '/resetPassword' ||
                               next.$$route.originalPath === '/ulRegistration'
                             ))
      {
        return;
      }


      var token = $localStorage.RCQ_JWT_Token;

      if (!token || jwtHelper.isTokenExpired(token))
      {
        $location.path('/login').replace();
      }
    });

    $rootScope.$on('mapInitialized', function(evt,map) {
      $rootScope.map = map;
      $rootScope.$apply();
    });

  }
})();
