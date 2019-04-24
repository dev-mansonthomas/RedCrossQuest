/**
 * Created by tmanson on 03/05/2016.
 */

angular.module('redCrossQuestClient').factory('SettingsResource', function ($resource, $localStorage)
{
  return $resource(
    '/rest/:roleId/settings/ul/:id',
    {
      roleId: function () {return $localStorage.currentUser.roleId},
      id    : function () {return $localStorage.currentUser.ulId  }
    },
    {
      query:
        {
          method: 'GET',
          isArray: false
        },
      //get setup info to determine if the administator has do to something
      getSetupStatus: {
        method: 'GET',
        params: {
          action: 'getSetupStatus'
        }
      },



      createYear: {
        method: 'POST'
      }
    });
});
