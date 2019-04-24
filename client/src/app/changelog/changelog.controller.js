/**
 * Created by tmanson on 15/04/2016.
 */

(function() {
  'use strict';

  angular
    .module('redCrossQuestClient')
    .controller('ChangelogController', ChangelogController);

  /** @ngInject */
  function ChangelogController($rootScope)
  {
    $rootScope.$emit('title-updated', 'Changelog');
  }
})();

