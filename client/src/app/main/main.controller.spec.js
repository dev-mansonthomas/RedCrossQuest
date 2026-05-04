(function() {
  'use strict';

  describe('controllers', function(){
    var vm;
    var $timeout;
    var toastr;

    beforeEach(module('redCrossQuestClient'));
    // Preload templates compiled by ng-html2js (cf. karma.conf.js, moduleName
    // = 'client') so the default route's templateUrl resolves from the cache
    // rather than hitting $httpBackend with an Unexpected request when
    // $timeout.flush() triggers $digest.
    beforeEach(module('client'));

    // The controller reads $localStorage.currentUser.* synchronously and
    // calls SettingsResource / PointQueteService at construction time;
    // provide enough of a shape for it to instantiate without throwing.
    beforeEach(module(function($provide) {
      var noopPromise = { $promise: { then: function() { return { catch: function() {} }; } } };
      $provide.value('$localStorage', {
        currentUser: { username: 'u', ulName: 'ul', ulId: 1, d: 'D', roleId: 1 }
      });
      $provide.value('SettingsResource',  { getAllSettings: function() { return noopPromise; },
                                            getSetupStatus: function() { return noopPromise; } });
      $provide.value('PointQueteService', { loadPointQuete: function() {} });
    }));

    beforeEach(inject(function(_$controller_, _$timeout_, _toastr_, $rootScope) {
      spyOn(_toastr_, 'info').and.callThrough();

      // MainController declares $scope as an explicit dependency (cf.
      // main.controller.js), so we must provide a fresh scope as a local;
      // strict-DI mode rejects relying on $rootScope to resolve $scope.
      vm = _$controller_('MainController', { $scope: $rootScope.$new() });
      $timeout = _$timeout_;
      toastr = _toastr_;
    }));

    it('should have a timestamp creation date', function() {
      expect(vm.creationDate).toEqual(jasmine.any(Number));
    });

    it('should define animate class after delaying timeout ', function() {
      $timeout.flush();
      expect(vm.classAnimation).toEqual('rubberBand');
    });

    it('should show a Toastr info and stop animation when invoke showToastr()', function() {
      vm.showToastr();
      expect(toastr.info).toHaveBeenCalled();
      expect(vm.classAnimation).toEqual('');
    });

    it('should expose an awesomeThings array', function() {
      // The Yeoman scaffold's "more than 5 awesome things" no longer applies:
      // the controller now initialises vm.awesomeThings to [] and never
      // populates it, so we assert the type only.
      expect(angular.isArray(vm.awesomeThings)).toBeTruthy();
    });
  });
})();
