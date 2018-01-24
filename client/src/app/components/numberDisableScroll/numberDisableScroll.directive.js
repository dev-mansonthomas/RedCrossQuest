(function() {
  'use strict';

  angular
    .module('redCrossQuestClient')
    .directive('input',function() {
      return {
        restrict: 'E',
        scope: {
          type: '@'
        },
        link : function (scope, $element) {
          if (scope.type == 'number') {
            $element.on('focus', function () {
              angular.element(this).on('mousewheel', function (e) {
                e.preventDefault();
              });
            });
            $element.on('blur', function () {
              angular.element(this).off('mousewheel');
            });
          }
        }
      }
    });

})();
