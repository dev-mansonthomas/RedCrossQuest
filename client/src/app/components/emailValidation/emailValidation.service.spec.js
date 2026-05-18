(function() {
  'use strict';

  describe('service EmailValidationService', function() {
    var EmailValidationService;

    beforeEach(module('redCrossQuestClient'));
    beforeEach(inject(function(_EmailValidationService_) {
      EmailValidationService = _EmailValidationService_;
    }));

    it('should be registered', function() {
      expect(EmailValidationService).not.toEqual(null);
      expect(EmailValidationService.isValidCroixRougeEmail).toEqual(jasmine.any(Function));
    });

    describe('isValidCroixRougeEmail', function() {

      it('accepts a personal croix-rouge.fr address', function() {
        expect(EmailValidationService.isValidCroixRougeEmail('jean.dupont@croix-rouge.fr')).toBe(true);
      });

      it('accepts mixed case and trims', function() {
        expect(EmailValidationService.isValidCroixRougeEmail('  Jean.Dupont@Croix-Rouge.FR  ')).toBe(true);
      });

      it('does not match the "presiden" prefix inside an unrelated word', function() {
        expect(EmailValidationService.isValidCroixRougeEmail('presence.medicale@croix-rouge.fr')).toBe(true);
      });

      it('rejects empty/null/undefined', function() {
        expect(EmailValidationService.isValidCroixRougeEmail('')).toBe(false);
        expect(EmailValidationService.isValidCroixRougeEmail(null)).toBe(false);
        expect(EmailValidationService.isValidCroixRougeEmail(undefined)).toBe(false);
        expect(EmailValidationService.isValidCroixRougeEmail(42)).toBe(false);
      });

      it('rejects non croix-rouge.fr domain', function() {
        expect(EmailValidationService.isValidCroixRougeEmail('jdoe@gmail.com')).toBe(false);
        expect(EmailValidationService.isValidCroixRougeEmail('jdoe@croix-rouge.com')).toBe(false);
        expect(EmailValidationService.isValidCroixRougeEmail('jdoe@something-croix-rouge.fr.evil.com')).toBe(false);
      });

      it('rejects tresori*/trésori* role mailboxes (both ASCII and accented)', function() {
        expect(EmailValidationService.isValidCroixRougeEmail('tresorier75@croix-rouge.fr')).toBe(false);
        expect(EmailValidationService.isValidCroixRougeEmail('trésorier75@croix-rouge.fr')).toBe(false);
        expect(EmailValidationService.isValidCroixRougeEmail('tresoriere.ul@croix-rouge.fr')).toBe(false);
      });

      it('rejects presiden* role mailboxes', function() {
        expect(EmailValidationService.isValidCroixRougeEmail('president.ul123@croix-rouge.fr')).toBe(false);
        expect(EmailValidationService.isValidCroixRougeEmail('presidente@croix-rouge.fr')).toBe(false);
      });

      it('rejects dtXX@ DT generic mailboxes', function() {
        expect(EmailValidationService.isValidCroixRougeEmail('dt75@croix-rouge.fr')).toBe(false);
        expect(EmailValidationService.isValidCroixRougeEmail('dt06@croix-rouge.fr')).toBe(false);
      });

      it('accepts addresses that only contain dt[0-9]{2} but not at the start of local part', function() {
        expect(EmailValidationService.isValidCroixRougeEmail('jean.dt75@croix-rouge.fr')).toBe(true);
      });

      it('rejects logistique mailboxes', function() {
        expect(EmailValidationService.isValidCroixRougeEmail('logistique.idf@croix-rouge.fr')).toBe(false);
      });

      it('rejects ul.* generic mailboxes', function() {
        expect(EmailValidationService.isValidCroixRougeEmail('ul.paris15@croix-rouge.fr')).toBe(false);
        expect(EmailValidationService.isValidCroixRougeEmail('ul.nice@croix-rouge.fr')).toBe(false);
      });

      it('accepts a name that contains "ul" but is not the ul.* generic pattern', function() {
        expect(EmailValidationService.isValidCroixRougeEmail('paul.dupont@croix-rouge.fr')).toBe(true);
      });

      it('rejects uniforme mailboxes', function() {
        expect(EmailValidationService.isValidCroixRougeEmail('uniforme@croix-rouge.fr')).toBe(false);
        expect(EmailValidationService.isValidCroixRougeEmail('uniforme.idf@croix-rouge.fr')).toBe(false);
      });
    });
  });
})();
