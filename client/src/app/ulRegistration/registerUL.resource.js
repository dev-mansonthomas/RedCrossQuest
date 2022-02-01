/**
 * Created by tmanson on 03/05/2016.
 *
 */

angular.module('redCrossQuestClient').factory('RegisterULResource', function($resource) {
  return $resource('/rest/ul_registration/:action', {}, {
    searchUl: {
      method : 'GET',
      isArray: true
    },
    register: {
      method : 'POST',
      isArray: false
    },
    validateUlRegistration: {
      method : 'POST',
      isArray: false,
      params:{
        'action':'check_registration_code'
      }
    }
  });
});
