/**
 * Created by tmanson on 03/05/2016.
 */

angular.module('redCrossQuestClient').factory('QueteurResource', function($resource, $localStorage) {
  return $resource('/rest/:roleId/ul/:ulId/queteurs/:id',
    {
      roleId: $localStorage.currentUser.roleId,
      ulId  : $localStorage.currentUser.ulId,
      id    : '@id'
    }, {
    update: {
      method: 'PUT' // this method issues a PUT request
    },
    searchSimilarQueteurs: {
      method: 'GET',
      isArray: true,
      params: {
        action: 'searchSimilarQueteurs',
        tronc_id: '@tronc_id'
      }
    }
  });
});
