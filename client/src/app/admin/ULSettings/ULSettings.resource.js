/**
 * Created by tmanson on 03/05/2016.
 */

angular.module('redCrossQuestClient').factory('ParametrageULResource', function ($resource, $localStorage) {
  return $resource('/rest/:roleId/ul/:ulId/settings/:id',
    {
      roleId: $localStorage.currentUser.roleId,
      ulId  : $localStorage.currentUser.ulId,
      id    : '@id'
    }, {
      update: {
        method: 'PUT' // this method issues a PUT request
      },
      createYear: {
        method: 'POST'
      }
    });
});
