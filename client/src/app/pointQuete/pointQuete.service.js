/**
 * Created by tmanson on 03/05/2016.
 */

angular
  .module('redCrossQuestClient')
  .factory('PointQueteService', PointQueteService);

function PointQueteService ($localStorage, PointQueteResource)
{
  var service = {};

  service.loadPointQuete      = loadPointQuete;

  return service;

  function loadPointQuete(callback)
  {
    PointQueteResource.
    query().
    $promise.
    then(function success(pointQueteList)
      {
        $localStorage.pointQuete=pointQueteList;

        $localStorage.pointsQueteHash = [];
        pointQueteList.forEach(function(onePointQuete){
          $localStorage.pointsQueteHash[onePointQuete.id]=onePointQuete;
        });
        if(callback)
          callback();
      });

  }
}
