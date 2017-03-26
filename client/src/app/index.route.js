(function () {
  'use strict';

  angular
    .module('client')
    .config(routeConfig);

  function routeConfig($routeProvider) {
    $routeProvider
      .when('/', {
        templateUrl: 'app/main/main.html',
        controller: 'MainController',
        controllerAs: 'main',
        requiresLogin: true
      })
      .when('/login', {
        templateUrl: 'app/authentication/login/login.html',
        controller: 'LoginController',
        controllerAs: 'vm'
      })
      .when('/testQRCode', {
        templateUrl: 'app/test/testQRCode.html',
        controller: 'TestQRCodeController',
        controllerAs: 'TestQRCode',
        requiresLogin: true
      })
      // ============== QUETEURS ==============
      .when('/queteurs', {
        templateUrl: 'app/queteurs/list/listQueteurs.html',
        controller: 'QueteursController',
        controllerAs: 'queteurs',
        requiresLogin: true
      })
      .when('/queteurs/edit', {
        templateUrl: 'app/queteurs/edit/editQueteur.html',
        controller: 'QueteurEditController',
        controllerAs: 'queteurEdit',
        requiresLogin: true
      })
      .when('/queteurs/edit/:id', {
        templateUrl: 'app/queteurs/edit/editQueteur.html',
        controller: 'QueteurEditController',
        controllerAs: 'queteurEdit',
        requiresLogin: true
      })
      // ============== TRONCS ==============
      .when('/troncs', {
        templateUrl: 'app/troncs/list/listTroncs.html',
        controller: 'TroncsController',
        controllerAs: 'troncs',
        requiresLogin: true
      })
      .when('/troncs/edit', {
        templateUrl: 'app/troncs/edit/editTronc.html',
        controller: 'TroncEditController',
        controllerAs: 'troncEdit',
        requiresLogin: true
      })
      .when('/troncs/edit/:id', {
        templateUrl: 'app/troncs/edit/editTronc.html',
        controller: 'TroncEditController',
        controllerAs: 'troncEdit',
        requiresLogin: true
      })
      .when('/troncs/prepa', {
        templateUrl: 'app/troncs/preparation/preparationTronc.html',
        controller: 'PreparationTroncController',
        controllerAs: 'prepaTronc',
        requiresLogin: true
      })
      .when('/troncs/depart', {
        templateUrl: 'app/troncs/depart/departTronc.html',
        controller: 'DepartTroncController',
        controllerAs: 'departTronc',
        requiresLogin: true
      })
      .when('/troncs/retour', {
        templateUrl: 'app/troncs/retour/retourTronc.html',
        controller: 'RetourTroncController',
        controllerAs: 'retourTronc',
        requiresLogin: true
      })
      .when('/troncs/retour/:id', {
        templateUrl: 'app/troncs/retour/retourTronc.html',
        controller: 'RetourTroncController',
        controllerAs: 'retourTronc',
        requiresLogin: true
      })
      // ============== QRCode Generator ==============

      .when('/QRCode/troncs', {
        templateUrl: 'app/QRCode/troncs/QRCodeTroncs.html',
        controller: 'QRCodeTroncsController',
        controllerAs: 'qrcTroncs',
        requiresLogin: true
      })
      .when('/QRCode/queteurs', {
        templateUrl: 'app/QRCode/queteurs/QRCodeQueteurs.html',
        controller: 'QRCodeQueteursController',
        controllerAs: 'qrcQueteurs',
        requiresLogin: true
      })

      // ============== OTHERWISE ==============
      .otherwise({
        redirectTo: '/'
      });
  }

})();
