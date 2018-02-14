/**
 * Created by tmanson on 03/05/2016.
 */

angular.module('redCrossQuestClient').factory('SettingsResource', function ($resource, $localStorage) {
  return $resource('/rest/:roleId/settings/ul/:ulId/',
    {
      roleId: $localStorage.currentUser.roleId,
      ulId  : $localStorage.currentUser.ulId,
      id    : '@id'
    }, {
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
