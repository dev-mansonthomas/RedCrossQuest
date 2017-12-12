/**
 * Created by tmanson on 03/05/2016.
 */

angular.module('redCrossQuestClient').factory('TroncQueteurHistoryResource', function ($resource, $localStorage) {
  return $resource('/rest/:roleId/ul/:ulId/tronc_queteur_history/:id',
    {//supposed to return one row of the historic, not implemented yet
      roleId: $localStorage.currentUser.roleId,
      ulId  : $localStorage.currentUser.ulId,
      id    : '@id'
    },
    {
      getTroncQueteurHistoryForTroncQueteurId: {
        method: 'GET',
        isArray: true,
        params: {
          tronc_queteur_id: '@tronc_queteur_id'
        }
      }
    });
});
