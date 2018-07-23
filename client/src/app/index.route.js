(function () {
  'use strict';

  angular
    .module('redCrossQuestClient')
    .config(routeConfig);

  function routeConfig($routeProvider) {
    $routeProvider
      .when('/', {
        templateUrl : 'app/main/main.html',
        controller  : 'MainController',
        controllerAs: 'main'
      })
      .when('/login', {
        templateUrl : 'app/authentication/login/login.html',
        controller  : 'LoginController',
        controllerAs: 'vm'
      })
      .when('/login/:login', {
        templateUrl : 'app/authentication/login/login.html',
        controller  : 'LoginController',
        controllerAs: 'vm'
      })
      .when('/resetPassword', {
        templateUrl : 'app/authentication/resetPassword/resetPassword.html',
        controller  : 'ResetPasswordController',
        controllerAs: 'vm'
      })
      // ============== QUETEURS ==============
      .when('/queteurs', {
        templateUrl : 'app/queteurs/list/listQueteurs.html',
        controller  : 'QueteursController',
        controllerAs: 'q'
      })
      .when('/queteurs/edit', {
        templateUrl : 'app/queteurs/edit/editQueteur.html',
        controller  : 'QueteurEditController',
        controllerAs: 'queteur'
      })
      .when('/queteurs/edit/:id', {
        templateUrl : 'app/queteurs/edit/editQueteur.html',
        controller  : 'QueteurEditController',
        controllerAs: 'queteur'
      })
      // ============== TRONCS ==============
      .when('/troncs', {
        templateUrl : 'app/troncs/list/listTroncs.html',
        controller  : 'TroncsController',
        controllerAs: 't'
      })
      .when('/troncs/edit', {
        templateUrl : 'app/troncs/edit/editTronc.html',
        controller  : 'TroncEditController',
        controllerAs: 'tronc'
      })
      .when('/troncs/edit/:id', {
        templateUrl : 'app/troncs/edit/editTronc.html',
        controller  : 'TroncEditController',
        controllerAs: 'tronc'
      })
      .when('/troncs/prepa', {
        templateUrl : 'app/troncs/preparation/preparationTronc.html',
        controller  : 'PreparationTroncController',
        controllerAs: 'pt'
      })
      .when('/troncs/depart', {
        templateUrl : 'app/troncs/depart/departTronc.html',
        controller  : 'DepartTroncController',
        controllerAs: 'dt'
      })
      .when('/troncs/retour', {
        templateUrl : 'app/troncs/retour/retourTronc.html',
        controller  : 'RetourTroncController',
        controllerAs: 'rt'
      })
      .when('/troncs/retour/:id', {
        templateUrl : 'app/troncs/retour/retourTronc.html',
        controller  : 'RetourTroncController',
        controllerAs: 'rt'
      })
      .when('/tronc_queteur/', {
        templateUrl : 'app/troncs/troncQueteur/troncQueteur.html',
        controller  : 'TroncQueteurController',
        controllerAs: 'tq'
      })
      .when('/tronc_queteur/edit/:id', {
        templateUrl : 'app/troncs/troncQueteur/troncQueteur.html',
        controller  : 'TroncQueteurController',
        controllerAs: 'tq'
      })

      // ============== Daily Stats ==============
      .when('/dailyStats', {
        templateUrl : 'app/admin/dailyStats/list/listDailyStats.html',
        controller  : 'DailyStatsController',
        controllerAs: 'ds'
      })
      // ============== Yearly Goals ==============
      .when('/yearlyGoals', {
        templateUrl : 'app/admin/yearlyGoals/list/listYearlyGoals.html',
        controller  : 'YearlyGoalsController',
        controllerAs: 'yg'
      })

      // ============== Settings ==============
      .when('/settings', {
        templateUrl : 'app/admin/Settings/settings.html',
        controller  : 'SettingsController',
        controllerAs: 's'
      })
      // ============== QRCode Generator ==============

      .when('/QRCode/troncs', {
        templateUrl : 'app/admin/QRCode/troncs/QRCodeTroncs.html',
        controller  : 'QRCodeTroncsController',
        controllerAs: 'qrcTroncs'
      })
      .when('/QRCode/queteurs', {
        templateUrl : 'app/admin/QRCode/queteurs/QRCodeQueteurs.html',
        controller  : 'QRCodeQueteursController',
        controllerAs: 'qrcQueteurs'
      })

      // ============== Graph Spotfire ==============
      .when('/graph', {
        templateUrl : 'app/graph/graph.html',
        controller  : 'GraphController',
        controllerAs: 'g'
      })

      // ============== Points Quetes ==============
      .when('/pointsQuetes', {
        templateUrl : 'app/admin/pointQuete/list/listPointQuete.html',
        controller  : 'ListPointQueteController',
        controllerAs: 'pq'
      })
      .when('/pointsQuetes/edit', {
        templateUrl : 'app/admin/pointQuete/edit/editPointQuete.html',
        controller  : 'EditPointQueteController',
        controllerAs: 'pqe'
      })
      .when('/pointsQuetes/edit/:id', {
        templateUrl : 'app/admin/pointQuete/edit/editPointQuete.html',
        controller  : 'EditPointQueteController',
        controllerAs: 'pqe'
      })
      // ============== Recu Fiscal ==============
      .when('/recu_fiscal', {
        templateUrl : 'app/admin/recuFiscal/list/listRecuFiscal.html',
        controller  : 'ListRecuFiscalController',
        controllerAs: 'rf'
      })
      .when('/recu_fiscal/edit', {
        templateUrl : 'app/admin/recuFiscal/edit/editRecuFiscal.html',
        controller  : 'EditRecuFiscalController',
        controllerAs: 'rfe'
      })
      .when('/recu_fiscal/edit/:id', {
        templateUrl : 'app/admin/recuFiscal/edit/editRecuFiscal.html',
        controller  : 'EditRecuFiscalController',
        controllerAs: 'rfe'
      })
      // ============== MAILING   ==============
      .when('/mailing', {
        templateUrl : 'app/admin/mailing/launch.html',
        controller  : 'MailingController',
        controllerAs: 'mc'
      })
      // ============== OTHERWISE ==============
      .otherwise({
        redirectTo: '/'
      });
  }

})();
