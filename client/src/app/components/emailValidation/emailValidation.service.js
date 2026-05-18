/**
 * Validates that a logged-in user's email is a personal Croix-Rouge address.
 *
 * Rules:
 *  - must end with @croix-rouge.fr
 *  - must NOT match any of the forbidden patterns below (generic/role-based
 *    addresses that are not tied to a single volunteer).
 *
 * The list is intentionally kept simple and is expected to evolve.
 */
angular
  .module('redCrossQuestClient')
  .factory('EmailValidationService', function(){

    var DOMAIN_REGEX = /@croix-rouge\.fr$/i;

    // Email local parts are typically ASCII so accented variants ("trésori")
    // rarely exist in mailboxes but are kept for safety.
    var FORBIDDEN_PATTERNS = [
      /(trésori|tresori)/i,
      /presiden/i,
      /^dt[0-9]{2}@/i,
      /logistique/i,
      /^ul\./i,
      /uniforme/i
    ];

    var instance = {};

    instance.isValidCroixRougeEmail = function(email)
    {
      if (email == null || typeof email !== 'string' || email.length === 0)
      {
        return false;
      }

      var trimmed = email.trim().toLowerCase();

      if (!DOMAIN_REGEX.test(trimmed))
      {
        return false;
      }

      for (var i = 0; i < FORBIDDEN_PATTERNS.length; i++)
      {
        if (FORBIDDEN_PATTERNS[i].test(trimmed))
        {
          return false;
        }
      }

      return true;
    };

    return instance;
  });
