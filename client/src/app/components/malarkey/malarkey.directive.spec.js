(function() {
  'use strict';

  // Yeoman scaffold spec, kept as a smoke test of the directive wiring.
  // The original assertions (githubContributor.getContributors fake, six
  // contributors, "Activated Contributors View" $log entry) targeted demo
  // behaviour that was removed when the directive was simplified to just
  // type the `extra-values` strings via the `malarkey` library.
  describe('directive malarkey', function() {
    var vm;
    var el;

    beforeEach(module('redCrossQuestClient'));
    // Preload templates compiled by ng-html2js (cf. karma.conf.js, moduleName
    // = 'client') so the router's default route doesn't hit $httpBackend with
    // an Unexpected request when $rootScope.$digest fires below.
    beforeEach(module('client'));
    beforeEach(inject(function($compile, $rootScope) {
      el = angular.element('<acme-malarkey extra-values="[\'Poney\', \'Monkey\']"></acme-malarkey>');

      $compile(el)($rootScope.$new());
      $rootScope.$digest();
      vm = el.isolateScope().vm;
    }));

    it('should be compiled', function() {
      expect(el.html()).not.toEqual(null);
    });

    it('should expose its controller-as alias on the isolate scope', function() {
      expect(vm).toEqual(jasmine.any(Object));
      expect(vm.contributors).toEqual(jasmine.any(Array));
    });
  });
})();
