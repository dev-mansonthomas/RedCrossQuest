/**
 * Created by tmanson on 03/05/2016.
 */

angular.module('client').factory('GraphResource', function($resource, $localStorage) {
  return $resource('/rest/:roleId/ul/:ulId/graph',
    {
      roleId: $localStorage.currentUser.roleId,
      ulId  : $localStorage.currentUser.ulId
    }, {
    create: {
      method: 'POST'
    }
  });
});
