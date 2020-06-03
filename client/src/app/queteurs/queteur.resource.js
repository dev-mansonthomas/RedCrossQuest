/**
 * Created by tmanson on 03/05/2016.
 */

angular.module('redCrossQuestClient').factory('QueteurResource', function ($resource, $localStorage)
{
  return $resource('/rest/:roleId/ul/:ulId/queteurs/:id/:action',
    {
      roleId: function () { return $localStorage.currentUser.roleId},
      ulId  : function () { return $localStorage.currentUser.ulId  },
      id: '@id'
    },
    {
      update: {
       method: 'PUT'
      },
      query:{
        isArray: false
      },
      exportData:{
        method: 'POST',
        params:
          {
            action: 'exportData'
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
      isArray: false,
      params:
        {
          action: 'listPendingQueteurRegistration'
        }
    },
    approveQueteurRegistration:
      {
        method:'POST',
        params:
          {
            action:'approveQueteurRegistration'
          }
      },
      countInactiveQueteurs:
        {
          method: 'GET',
          params:
            {
              action: 'countInactiveQueteurs'
            }
        }
      ,
      disableInactiveQueteurs:
        {
          method:'POST',
          params:
            {
              action:'disableInactiveQueteurs'
            }
        },

    });
});
