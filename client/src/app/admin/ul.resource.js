/**
 * Created by tmanson on 03/05/2016.
 */

angular.module('redCrossQuestClient').factory('UniteLocaleResource', function($resource, $localStorage) {
  return $resource('/rest/:roleId/ul/:action/:id',
    {
      roleId: function () { return $localStorage.currentUser.roleId},
      id    : '@id'
    }, {
    update: {
      method: 'PUT',
    },
      query:{
        isArray: true
      },
      listRegistrations:{
        isArray: false,
        params:
          {
            action: 'registrations'
          }
      },
      getRegistration:{
        isArray: false,
        params:
          {
            action: 'registrations'
          }
      },
      registrationDecision:{
        isArray: false,
        method: 'PUT',
        params:
          {
            action: 'registrations'
          }
      },
      get:{
        isArray: false
      }
  });
});
