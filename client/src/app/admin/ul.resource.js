/**
 * Created by tmanson on 03/05/2016.
 */

angular.module('redCrossQuestClient').factory('UniteLocaleResource', function($resource, $localStorage) {
  return $resource('/rest/:roleId/ul/:id',
    {
      roleId: function () { return $localStorage.currentUser.roleId},
      id    : '@id'
    }, {
    update: {
      method: 'PUT',
    }
  });
});
