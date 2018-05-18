/**
 * Created by tmanson on 03/05/2016.
 */

angular.module('redCrossQuestClient').factory('QueteurResource', function ($resource, $localStorage)
{
  return $resource('/rest/:roleId/ul/:ulId/queteurs/:id',
    {
      roleId: function () { return $localStorage.currentUser.roleId},
      ulId  : function () { return $localStorage.currentUser.ulId  },
      id    : '@id'
    },
    {
      update:
        {
          method: 'PUT'
        },
      anonymize:
        {
          method: 'PUT',
          params:
            {
              action: 'anonymize'
            }
        },
      searchSimilarQueteurs:
        {
          method: 'GET',
          isArray: true,
          params:
            {
              action: 'searchSimilarQueteurs',
              tronc_id: '@tronc_id'
            }
        }
    });
});
