(function() {
  'use strict';

  angular
    .module('client', ['ngAnimate', 'ngCookies', 'ngTouch',
            'ngSanitize', 'ngMessages', 'ngAria', 'ngResource',
            'ngRoute', 'ui.bootstrap', 'toastr', 'qrScanner']);

  angular
    .module('queteurs', ['ngRoute', 'ngResource']);


})();