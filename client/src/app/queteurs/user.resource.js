/**
 * Created by tmanson on 03/05/2016.
 */

angular.module('redCrossQuestClient').factory('UserResource', function($resource, $localStorage) {
  return $resource('/rest/:roleId/ul/:ulId/users/:id/:action',
    {
      roleId: function () { return $localStorage.currentUser.roleId},
      ulId  : function () { return $localStorage.currentUser.ulId  },
      id    : '@id'
    }, {
    update: {
      method: 'PUT'
    },
    reInitPassword: {
      method: 'PUT', // this method issues a PUT request
      params:{
        action:'reInitPassword'
      }
    }
  });
});
