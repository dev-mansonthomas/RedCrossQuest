/**
 * Created by tmanson on 03/05/2016.
 */

angular
  .module('redCrossQuestClient')
  .factory('DateTimeHandlingService', function($log, moment){

    var instance = {};

    /**
     * Server date are stored in UTC TimeZone. Javascript native date do not handle timezone.
     * For example, a paris TimeZone of 14h20 will be stored in DB as "12H20UTC"
     * Without any processing, the date are displayed in UTC and not in the local TimeZone
     * This code returns a javascript date in the local TimeZone.
     * */
    instance.handleServerDate=function(serverDate)
    {
      if(serverDate == null)
      {
        return {
          dateInLocalTimeZone       : "",
          dateInLocalTimeZoneMoment : "",
          stringVersion             : ""
        };
      }

      if(typeof serverDate === 'string')
      {
        return {
          dateInLocalTimeZone       : moment(serverDate).toDate(),
          dateInLocalTimeZoneMoment : moment(serverDate),
          stringVersion             : serverDate
        };
      }

      //date store in UTC + Timezone offset with Carbon on php side.
      //Carbon date is updated to Paris Timezone, so no need of further manipulation
      //this parse the Carbon time without '000' ending in the UTC timezone, and then convert it to Europe/Paris (the value of the tronc_queteur.retour.timezone)

      //Convert it to local TimeZone            .substring(0,serverDate.date.length -3 ),"YYYY-MM-DD HH:mm:ss.SSS"
      var finalDateMoment = moment(serverDate.date.substring(0,serverDate.date.length -3 ), "YYYY-MM-DD HH:mm:ss.SSS");
      var finalDate       = finalDateMoment.toDate();
      // don't understand why, but I've to add the offset to get the local timezone date as a string
      var stringVersion   = finalDateMoment.format("YYYY-MM-DD HH:mm:ss");

      return {
        dateInLocalTimeZone       : finalDate,
        dateInLocalTimeZoneMoment : finalDateMoment,
        stringVersion             : stringVersion
      };

    };

    instance.handleDateWithoutTime=function(dateWithoutTime)
    {
      if(dateWithoutTime == null)
        return "";
      return moment(dateWithoutTime).format("YYYY-MM-DD");
    };

    return instance;
  });
