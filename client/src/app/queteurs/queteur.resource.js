/**
 * Created by tmanson on 03/05/2016.
 */

angular.module('redCrossQuestClient').factory('QueteurResource', function ($resource, $localStorage)
{
  return $resource('/rest/:roleId/ul/:ulId/queteurs/:id',
    {
      roleId: function () { return $localStorage.currentUser.roleId},
      ulId  : function () { return $localStorage.currentUser.ulId  },
      id: '@id'
    },
    {
      update: {
        method: 'PUT',
        params:
          {
            action: 'update'
          }
      },
      anonymize:
        {
          method: 'PUT',
          params:
            {
              action: 'anonymize'
            }
        },
      associateRegistrationWithExistingQueteur:
       {
         method: 'PUT',
         params:
           {
             action: 'associateRegistrationWithExistingQueteur'
           }
       },

     markAllAsPrinted: {
        method: 'PUT',
        params: {
          action: 'markAllAsPrinted'
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
        },
      getQueteurRegistration:
      {
        method: 'GET',
        params:
        {
          action: 'getQueteurRegistration'
        }
      }
      ,
      countPendingQueteurRegistration:
        {
          method: 'GET',
          params:
            {
              action: 'countPendingQueteurRegistration'
            }
        }
      ,
      listPendingQueteurRegistration:
        {
          method: 'GET',
          isArray: true,
          params:
            {
              action: 'listPendingQueteurRegistration'
            }
        }
    });
});
