/**
 * Created by tmanson on 03/05/2016.
 */

angular.module('redCrossQuestClient').factory('DailyStatsResource', function ($resource, $localStorage) {
  return $resource('/rest/:roleId/ul/:ulId/dailyStats/:id',
    {
      roleId: function () { return $localStorage.currentUser.roleId},
      ulId  : function () { return $localStorage.currentUser.ulId  },
      id: '@id'
    }, {
      update: {
        method: 'PUT' // this method issues a PUT request
      },
      createYear: {
        method: 'POST'
      }
    });
});
