(function() {
  'use strict';

  angular
    .module('client')
    .config(routeConfig);

  function routeConfig($routeProvider) {
    $routeProvider
      .when('/', {
        templateUrl: 'app/main/main.html',
        controller: 'MainController',
        controllerAs: 'main'
      })
      .when('/testQRCode', {
        templateUrl: 'app/test/testQRCode.html',
        controller: 'TestQRCodeController',
        controllerAs: 'TestQRCode'
      })
      // ============== QUETEURS ==============
      .when('/queteurs', {
        templateUrl: 'app/queteurs/list/listQueteurs.html',
        controller: 'QueteursController',
        controllerAs: 'queteurs'
      })
      .when('/queteurs/edit', {
        templateUrl: 'app/queteurs/edit/editQueteur.html',
        controller: 'QueteurEditController',
        controllerAs: 'queteurEdit'
      })
      .when('/queteurs/edit/:id', {
        templateUrl: 'app/queteurs/edit/editQueteur.html',
        controller: 'QueteurEditController',
        controllerAs: 'queteurEdit'
      })
      // ============== TRONCS ==============
      .when('/troncs', {
        templateUrl: 'app/troncs/list/listTroncs.html',
        controller: 'TroncsController',
        controllerAs: 'troncs'
      })
      .when('/troncs/edit', {
        templateUrl: 'app/troncs/edit/editTronc.html',
        controller: 'TroncEditController',
        controllerAs: 'troncEdit'
      })
      .when('/troncs/edit/:id', {
        templateUrl: 'app/troncs/edit/editTronc.html',
        controller: 'TroncEditController',
        controllerAs: 'troncEdit'
      })
      .when('/troncs/prepa', {
        templateUrl: 'app/troncs/preparation/preparationTronc.html',
        controller: 'PreparationTroncController',
        controllerAs: 'prepaTronc'
      })
      .when('/troncs/depart', {
        templateUrl: 'app/troncs/depart/departTronc.html',
        controller: 'DepartTroncController',
        controllerAs: 'departTronc'
      })
      .when('/troncs/retour', {
        templateUrl: 'app/troncs/retour/retourTronc.html',
        controller: 'RetourTroncController',
        controllerAs: 'retourTronc'
      })
      // ============== QRCode Generator ==============

      .when('/QRCode/troncs', {
        templateUrl: 'app/QRCode/troncs/QRCodeTroncs.html',
        controller: 'QRCodeTroncsController',
        controllerAs: 'qrcTroncs'
      })
      .when('/QRCode/queteurs', {
        templateUrl: 'app/QRCode/queteurs/QRCodeQueteurs.html',
        controller: 'QRCodeQueteursController',
        controllerAs: 'qrcQueteurs'
      })

      // ============== OTHERWISE ==============
      .otherwise({
        redirectTo: '/'
      });
  }

})();
