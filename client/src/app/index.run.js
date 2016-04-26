(function() {
  'use strict';

  angular
    .module('client')
    .run(runBlock);



  /** @ngInject */
  function runBlock($log) {

    $log.debug('runBlock end');
  }

})();
