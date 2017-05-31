/**
 * Created by tmanson on 15/04/2016.
 */

(function() {
  'use strict';

  angular
    .module('client')
    .controller('GraphController', GraphController);

  /** @ngInject */
  function GraphController($log) {
    var vm = this;

    var serverUrl    = "https://spotfire.cloud.tibco.com/spotfire/wp/";
    var analysisPath = "/users/mansonthomas/Public/SpotfireCloud";


    var parameters = "ul=2;"
    var app = new spotfire.webPlayer.Application(serverUrl, null, analysisPath, parameters, false);
    var doc = app.openDocument("container");





  }
})();

