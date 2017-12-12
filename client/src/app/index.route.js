(function () {
  'use strict';

  angular
    .module('redCrossQuestClient')
    .config(routeConfig);

  function routeConfig($routeProvider) {
    $routeProvider
      .when('/', {
        templateUrl: 'app/main/main.html',
        controller: 'MainController',
        controllerAs: 'main'
      })
      .when('/login', {
        templateUrl: 'app/authentication/login/login.html',
        controller: 'LoginController',
        controllerAs: 'vm'
      })
      .when('/resetPassword', {
        templateUrl: 'app/authentication/resetPassword/resetPassword.html',
        controller: 'ResetPasswordController',
        controllerAs: 'vm'
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
      .when('/troncs/retour/:id', {
        templateUrl: 'app/troncs/retour/retourTronc.html',
        controller: 'RetourTroncController',
        controllerAs: 'retourTronc'
      })
      .when('/tronc_queteur/', {
        templateUrl: 'app/troncs/troncQueteur/troncQueteur.html',
        controller: 'TroncQueteurController',
        controllerAs: 'troncQueteur'
      })
      .when('/tronc_queteur/edit/:id', {
        templateUrl: 'app/troncs/troncQueteur/troncQueteur.html',
        controller: 'TroncQueteurController',
        controllerAs: 'troncQueteur'
      })

      // ============== Daily Stats ==============
      .when('/dailyStats', {
        templateUrl: 'app/admin/dailyStats/list/listDailyStats.html',
        controller: 'DailyStatsController',
        controllerAs: 'ds'
      })
      // ============== QRCode Generator ==============

      .when('/QRCode/troncs', {
        templateUrl: 'app/admin/QRCode/troncs/QRCodeTroncs.html',
        controller: 'QRCodeTroncsController',
        controllerAs: 'qrcTroncs'
      })
      .when('/QRCode/queteurs', {
        templateUrl: 'app/admin/QRCode/queteurs/QRCodeQueteurs.html',
        controller: 'QRCodeQueteursController',
        controllerAs: 'qrcQueteurs'
      })

      // ============== Graph Spotfire ==============
      .when('/graph', {
        templateUrl: 'app/graph/graph.html',
        controller: 'GraphController',
        controllerAs: 'graph'
      })

      // ============== Points Quetes ==============
      .when('/pointsQuetes', {
        templateUrl: 'app/admin/pointQuete/list/listPointQuete.html',
        controller: 'ListPointQueteController',
        controllerAs: 'pq'
      })
      .when('/pointsQuetes/edit', {
      templateUrl: 'app/admin/pointQuete/edit/editPointQuete.html',
      controller: 'EditPointQueteController',
      controllerAs: 'pqe'
    })
      .when('/pointsQuetes/edit/:id', {
        templateUrl: 'app/admin/pointQuete/edit/editPointQuete.html',
        controller: 'EditPointQueteController',
        controllerAs: 'pqe'
      })
      // ============== OTHERWISE ==============
      .otherwise({
        redirectTo: '/'
      });
  }

})();
