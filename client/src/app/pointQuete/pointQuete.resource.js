/**
 * Created by tmanson on 03/05/2016.
 */

angular.module('redCrossQuestClient').factory('PointQueteResource', function($resource, $localStorage) {
  return $resource('/rest/:roleId/ul/:ulId/pointQuetes/:id/:action',
    {
      roleId: function () { return $localStorage.currentUser.roleId},
      ulId  : function () { return $localStorage.currentUser.ulId  },
      id    : '@id'
    }, {
    update: {
      method: 'PUT' // this method issues a PUT request
    },
    search:{
        method: 'GET',
        isArray: false,
        params: {
          action: 'search'
        }
      }
  });
});
