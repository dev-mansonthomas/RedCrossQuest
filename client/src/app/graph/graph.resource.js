/**
 * Created by tmanson on 03/05/2016.
 */

angular.module('redCrossQuestClient').factory('GraphResource', function($resource, $localStorage) {
  return $resource('/rest/:roleId/ul/:ulId/graph',
    {
      roleId: function () { return $localStorage.currentUser.roleId},
      ulId  : function () { return $localStorage.currentUser.ulId  }
    }, {
    create: {
      method: 'POST'
    }
  });
});
