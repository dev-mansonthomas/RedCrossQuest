/**
 * Created by tmanson on 03/05/2016.
 */

angular.module('redCrossQuestClient').factory('YearlyGoalsResource', function ($resource, $localStorage) {
  return $resource('/rest/:roleId/ul/:ulId/yearlyGoals/:id',
    {
      roleId: function () { return $localStorage.currentUser.roleId},
      ulId  : function () { return $localStorage.currentUser.ulId  },
      id    : '@id'
    }, {
      update: {
        method: 'PUT' // this method issues a PUT request
      },
      createYear: {
        method: 'POST'
      },
      getByYear:{
        method: 'GET'
      }
    });
});
