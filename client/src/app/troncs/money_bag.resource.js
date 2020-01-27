/**
 * Created by tmanson on 03/05/2016.
 */

angular.module('redCrossQuestClient').factory('MoneyBagResource', function ($resource, $localStorage) {
  return $resource('/rest/:roleId/ul/:ulId/moneyBag/:action/:id',
    {
      roleId: function () { return $localStorage.currentUser.roleId},
      ulId  : function () { return $localStorage.currentUser.ulId  },
      id: '@id'
    },
    {
      searchMoneyBagId:{
        method: 'GET',
        isArray: true
      },
      coinsMoneyBagDetails:{
        method: 'GET',
        isArray: false,
        params: {
          action: 'coins'
        }
      },
      billsMoneyBagDetails:{
        method: 'GET',
        isArray: false,
        params: {
          action: 'bills'
        }
      }
    });
});
