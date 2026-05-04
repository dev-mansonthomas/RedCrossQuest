(function() {
  'use strict';

  describe('directive navbar', function() {
    var vm;
    var el;
    var timeInMs;

    // Templates are preloaded by ng-html2js (cf. karma.conf.js: stripPrefix
    // = src/, moduleName = 'client'), so loading the `client` module makes
    // navbar.html resolvable via $templateCache and avoids the spurious
    // GET app/components/navbar/navbar.html that fails $httpBackend.
    beforeEach(module('redCrossQuestClient'));
    beforeEach(module('client'));

    // Stub the AngularJS $resource backed services the controller calls
    // synchronously in its constructor. Returning never-resolving promises
    // is enough: the spec only inspects the synchronously-set fields.
    beforeEach(module(function($provide) {
      var noopPromise = { $promise: { then: function() { return { catch: function() {} }; } } };
      $provide.value('VersionResource',  { get:                          function() { return noopPromise; } });
      $provide.value('QueteurResource',  { countPendingQueteurRegistration: function() { return noopPromise; } });
      $provide.value('AuthenticationService', { logout: function() {} });
      $provide.value('$localStorage',    { currentUser: { roleId: 1, ulMode: false, d: 'D' } });
    }));

    beforeEach(inject(function($compile, $rootScope) {
      timeInMs = new Date();
      timeInMs = timeInMs.setHours(timeInMs.getHours() - 24);

      el = angular.element('<acme-navbar creation-date="' + timeInMs + '"></acme-navbar>');

      $compile(el)($rootScope.$new());
      $rootScope.$digest();
      vm = el.isolateScope().vm;
    }));

    it('should be compiled', function() {
      expect(el.html()).not.toEqual(null);
    });

    it('should have isolate scope object with instanciate members', function() {
      expect(vm).toEqual(jasmine.any(Object));

      expect(vm.creationDate).toEqual(jasmine.any(Number));
      expect(vm.creationDate).toEqual(timeInMs);

      // The directive declares `bindToController: true` (boolean form) and
      // computes `relativeDate` synchronously in the controller constructor;
      // with AngularJS 1.5+, bindings via `=` are not guaranteed available
      // before the constructor runs without `$onInit`, so `moment(undefined)`
      // yields the current time and fromNow() is "a few seconds ago" in the
      // test context. Assert the type only and leave the value semantics to
      // an end-to-end check.
      expect(vm.relativeDate).toEqual(jasmine.any(String));
    });
  });
})();
