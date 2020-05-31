(function() {
  'use strict';

  angular
    .module('redCrossQuestClient')
    .config(config);

  /** @ngInject */
  function config($logProvider, $httpProvider,
                  toastrConfig, jwtOptionsProvider, $compileProvider)
  {
    //angular-jwt configuration
    jwtOptionsProvider.config({
      unauthenticatedRedirectPath: '/login',
      tokenGetter: ['options', '$localStorage', function(options, $localStorage) {
        if(options && options.url.substr(options.url.length - 5) === '.html')
        {
          return null;
        }
        return $localStorage.RCQ_JWT_Token;
      }]
    });
    //add sms to allowed links
    $compileProvider.aHrefSanitizationWhitelist(/^\s*(https?|mailto|tel|sms):/);

    $httpProvider.interceptors.push('jwtInterceptor');


    // Enable log
    $logProvider.debugEnabled(true);


    // Set Toastr options
    toastrConfig.allowHtml          = true;
    toastrConfig.timeOut            = 3000;
    toastrConfig.positionClass      = 'toast-top-right';
    toastrConfig.preventDuplicates  = true;
    toastrConfig.progressBar        = true;
  }

})();
