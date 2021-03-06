/**
 * Created by tmanson on 03/05/2016.
 */

angular.module('redCrossQuestClient').factory('SettingsResource', function ($resource, $localStorage)
{
  return $resource(
    '/rest/:roleId/settings/ul/:id/:action',
    {
      roleId: function () {return $localStorage.currentUser.roleId},
      id    : function () {return $localStorage.currentUser.ulId  }
    },
    {
     query:
        {
          isArray: false
        },
      //get setup info to determine if the administator has do to something
      getSetupStatus: {
        method: 'GET',
        params: {
          action: 'getSetupStatus'
        }
      },
      //get setup info to determine if the administator has do to something
      getAllSettings: {
        method: 'GET',
          params: {
          action: 'getAllSettings'
        }
      },
      //Get the application settings
      getULSettings: {
        method: 'GET'
      },
      createYear: {
        method: 'POST'
      },
      update: {
        method: 'PUT', // this method issues a PUT request
        params: {
          action: 'updateUL'
        }
      },
      updateRedQuestSettings: {
        method: 'PUT', // this method issues a PUT request
        params: {
          action: 'updateRedQuestSettings'
        }
      },
      updateRedCrossQuestSettings: {
        method: 'PUT', // this method issues a PUT request
        params: {
          action: 'updateRedCrossQuestSettings'
        }
      }
    });
});
