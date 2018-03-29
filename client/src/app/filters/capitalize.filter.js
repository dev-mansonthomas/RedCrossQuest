/**
 * Created by tmanson on 10/06/2017.
 */

(function () {
  'use strict';

  angular
    .module('redCrossQuestClient')
    .filter('capitalize', capitalize);

  function capitalize()
  {
    return function (input, all)
    {
      var reg = (all) ? /([^\W_]+[^\s-]*) */g : /([^\W_]+[^\s-]*)/;

      return angular.isDefined(input) ? input.replace(reg, function (txt)
      {
        return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();
      }) : '';
    }
  }


})();



