/**
 * Created by tmanson on 15/04/2016.
 */

(function() {
  'use strict';

  angular
    .module('redCrossQuestClient')
    .controller('QueteurEditController', QueteurEditController);

  /** @ngInject */
  function QueteurEditController($rootScope, $scope, $log, $routeParams, $location, $localStorage, $timeout,
                                 QueteurResource, UserResource, TroncQueteurResource, UniteLocaleResource,
                                 moment, Upload, DateTimeHandlingService)
  {
    var vm = this;

    var queteurId     = $routeParams.id;
    vm.currentUserRole= $localStorage.currentUser.roleId;
    vm.ulId           = $localStorage.currentUser.ulId;


    vm.youngestBirthDate=moment().subtract(1  ,'years').toDate();
    vm.oldestBirthDate  =moment().subtract(100,'years').toDate();


    vm.typeBenevoleList=[
      {id:1,label:'Action Sociale'                        },
      {id:2,label:'Secours'                               },
      {id:3,label:'Bénévole d\'un Jour'                   },
      {id:4,label:'Ancien Bénévole, Inactif ou Adhérent'  },
      {id:5,label:'Commerçant'                            },
      {id:6,label:'Spécial'                               }
    ];

    vm.roleList=[
      {id:1,label:'Lecture Seule' },
      {id:2,label:'Opérateur'     },
      {id:3,label:'Compteur'      },
      {id:4,label:'Administrateur'}
    ];

    vm.roleDesc=[];
    vm.roleDesc[1] = 'Consultation des quêteurs et le graphique public';
    vm.roleDesc[2] = 'Liste/Ajout/Update des quêteurs, préparation/départ/retour des troncs, graphique opérationnel';
    vm.roleDesc[3] = 'Opérateur + Comptage des troncs et tous les graphiques opérationnel et compteur';
    vm.roleDesc[4] = 'Compteur + administration des utilisateurs et paramétrage de RCQ pour l\'UL et accès à tous les graphiques';

    vm.createNewQueteur=function()
    {
      vm.current          = new QueteurResource();
      vm.current.ul_id    = vm.ulId;
      vm.current.ul_name  = $localStorage.currentUser.ulName;
      vm.current.active   = true;

      vm.current.unAnonymizeConfirmed     = false;
      vm.current.anonymizeAskConfirmation = false;
      vm.current.doAnonymizeButtonDisabled= false;


    };

    vm.handleDate = function (theDate)
    {
      return DateTimeHandlingService.handleServerDate(theDate).stringVersion;
    };

    vm.handleQueteur = function(queteur)
    {

      try
      {
        vm.current = queteur;


        $rootScope.$emit('title-updated', 'Edition du quêteur - '+vm.current.id+' - '+vm.current.first_name+' '+vm.current.last_name);

        if(angular.isDefined(vm.current.created))
        {
          vm.current.created = vm.handleDate(vm.current.created);
        }
        if(angular.isDefined(vm.current.updated))
        {
          vm.current.updated = vm.handleDate(vm.current.updated);
        }

        vm.current.ul_id=vm.current.ul_id+"";// otherwise the generated select is ? number(348) ?

        if(queteur.referent_volunteer_entity != null)
        {
          vm.current.referent_volunteerQueteur = queteur.referent_volunteer_entity.first_name+' '+queteur.referent_volunteer_entity.last_name + ' - '+queteur.referent_volunteer_entity.nivol;
        }

        if(typeof vm.current.mobile === "string")
        {
          if(vm.current.mobile === "N/A")
          {
            vm.current.mobile = null;
          }
          try
          {
            vm.current.mobile = parseInt(vm.current.mobile.slice(1));
          }
          catch(e)
          {
            vm.current.mobile = null;
          }

        }

        /*lack of data with previous model (minor instead of birthdate), only for ULParisIV, minor and major where set fixed birthdate
        * if editing one of these ==> set birthdate to null to force user to update the data*/

        if(angular.isDefined(vm.current.birthdate))
        {
          var birthdate = vm.current.birthdate.date.toLocaleString().substr(0,10);

          if(birthdate === '1922-12-22' || birthdate === '2007-07-07')
          {
            vm.current.birthdate = null;
          }
          else
          {
            vm.current.birthdate = moment( queteur.birthdate.date.substring(0, queteur.birthdate.date.length -16 ),"YYYY-MM-DD").toDate();
            vm.computeAge();
          }
        }

        if(angular.isDefined(vm.current.anonymization_date))
        {
          vm.current.anonymization_date = vm.handleDate(vm.current.anonymization_date);
        }

        TroncQueteurResource.getTroncsOfQueteur({'queteur_id': queteurId}).$promise.then(
          function success(data)
          {
            var dataLength = data.length;
            for(var i=0;i<dataLength;i++)
            {
              data[i].depart            = vm.handleDate(data[i].depart);
              data[i].depart_theorique  = vm.handleDate(data[i].depart_theorique);
              data[i].retour            = vm.handleDate(data[i].retour);

              if(data[i].retour !==null && data[i].depart !== null)
              {
                data[i].duration = moment.duration(moment(data[i].retour).diff(moment(data[i].depart))).asMinutes();
              }
            }

            vm.current.troncs_queteur  = data;
          },
          function error(error)
          {
            $log.error(error);
          }

        );

      }
      catch(ex)
      {
        $log.error(queteur);
        $log.error(ex);
      }
    };

// Load data or create new queteur (after function definition)
    if (angular.isDefined(queteurId))
    {
      QueteurResource.get({ 'id': queteurId }).$promise.then(vm.handleQueteur);

    }
    else
    {
      vm.createNewQueteur();
      $rootScope.$emit('title-updated', 'Création d\'un nouveau quêtêur');
    }

    vm.savedSuccessfullyFunction=function(response)
    {
      vm.current.saveInProgress=false;
      if(typeof response.queteurId ==='number')
      {
        vm.goToQueteur(response.queteurId);
      }

      vm.savedSuccessfully                = true;
      vm.current.anonymization_token      = null;
      vm.current.anonymization_date       = null;
      vm.current.unAnonymizeConfirmed     = false;
      vm.current.anonymizeAskConfirmation = false;
      vm.current.doAnonymizeButtonDisabled= false;

      vm.current.birthdate = moment(vm.current.birthdate).toDate();

      $timeout(function () { vm.savedSuccessfully=false; }, 5000);
    };

    vm.errorWhileSavingFunction=function(error)
    {
      vm.current.saveInProgress=false;
      vm.errorWhileSaving=true;
      vm.errorWhileSavingDetails=error;
    };

    vm.uploadFiles=function()
    {
      var queteurId=vm.current.id;


      var upload = Upload.upload({
        url: "/rest/"+ $localStorage.currentUser.roleId+"/ul/"+ $localStorage.currentUser.ulId+"/queteurs/"+queteurId+"/fileUpload",
        data: {
          queteurId: queteurId,
          signedForms:
            [
              {queteur1Day        : vm.current.temporary_volunteer_form},
              {parentAuthorization: vm.current.parent_authorization_form}
            ]
        },
        method:'PUT'
      });

      upload.then(function success(response)
      {
        $log.info('file ' + (response.config.data.file ? response.config.data.file.name:'undefined') + 'is uploaded successfully. Response: ' + response.data);
      },
      function error(error)
      {
        $log.error(error);
      },
      function progress(evt)
      {
        $log.info('progress: ' + parseInt(100.0 * evt.loaded / evt.total) + '% file :'+ (evt.config.data.file ? evt.config.data.file.name:'undefined'));
      });

    };


    vm.back=function()
    {
      window.history.back();
    };

    vm.save = function ()
    {
      //vm.uploadFiles();

      if(angular.isDefined(vm.current.anonymization_token) &&
          vm.current.anonymization_token !=  null &&
          vm.current.anonymization_token !== ""   &&
         !vm.current.unAnonymizeConfirmed)
      {
        vm.current.unanonymizeAskConfirmation=true;
      }
      else
      {
        vm.current.saveInProgress=true;
        if (angular.isDefined(vm.current.id) && vm.current.id != null)
        {//WARNING : le 9 janvier (heure d'hiver), coté javascript la date envoyé est le jour d'avant à 23h
          // le fix ci dessous, envoie la date en string, qui est vue comme une date venant de la DB pour Entity.php
          vm.current.birthdate = DateTimeHandlingService.handleDateWithoutTime(vm.current.birthdate);
          vm.current.$update(vm.savedSuccessfullyFunction, vm.errorWhileSavingFunction);
        }
        else
        {
          vm.current.$save  (vm.savedSuccessfullyFunction, vm.errorWhileSavingFunction);
        }
      }
    };

    vm.confirmUnAnonymize=function()
    {
      vm.current.unAnonymizeConfirmed = true;
      vm.save();
    };



    vm.computeAge=function()
    {
      vm.current.age       = moment().diff(vm.current.birthdate, 'years');
    };



    /* SEARCH REFERENT */

    /**
     * Set the queteur.id of the selected queteur in the model
     * */
    $scope.$watch('queteur.current.referent_volunteerQueteur', function(newValue/*, oldValue*/)
    {
      if(newValue !== null && typeof newValue === "object")
      {
        try
        {
          $log.info("queteurID set to "+newValue.id);
          $scope.queteur.current.referent_volunteer = newValue.id;
        }
        catch(exception)
        {
          $log.debug(exception);
        }
      }
    });

    /**
     * Function used while performing a manual search for a Queteur
     * @param queryString the search string (search is performed on first_name, last_name, nivol)
     * */
    vm.searchQueteur=function(queryString)
    {
      $log.info("Queteur : Manual Search for '"+queryString+"', active and benevoleOnly");
      return QueteurResource.query({"q":queryString, "active":1, "benevoleOnly":1}).$promise.then(
        function success(response)
        {
          return response.map(
            function success(queteur)
            {
              queteur.full_name= queteur.first_name+' '+queteur.last_name+' - '+queteur.nivol;
              return queteur;
            },
            function error(reason)
            {
              $log.debug("error while searching for queteur with query='"+queryString+"' with reason='"+reason+"'");
            });
        },
        function error(reason)
        {
          $log.debug("error while searching for queteur with query='"+queryString+"' with reason='"+reason+"'");
        });
    };
    /* END  SEARCH REFERENT */

    /* SEARCH UNITE LOCALE */
    /**
     * Function used while performing a manual search for an Unité Locale
     * @param queryString the search string (search is performed on name, postal code, city)
     * */
    vm.searchUL=function(queryString)
    {
      $log.info("UL : Manual Search for '"+queryString+"'");
      return UniteLocaleResource.query({"q":queryString}).$promise.then(function success(response)
      {
        return response.map(function(ul)
          {
            ul.full_name=  ul.id + ' - ' + ul.name+' - '+ul.postal_code+' - '+ul.city;
            return ul;
          },
          function error(reason)
          {
            $log.debug("error while searching for ul with query='"+queryString+"' with reason='"+reason+"'");
          });
      });
    };

    //This watch change on queteur variable to update the queteurId field
    $scope.$watch('queteur.current.ul_name', function(newValue/*, oldValue*/)
    {
      if(newValue !== null && typeof newValue !==  "string" && typeof newValue !== "undefined")
      {
        try
        {
          $scope.queteur.current.ul_id   = newValue.id;
          $scope.queteur.current.ul_name = newValue.full_name;
        }
        catch(exception)
        {
          $log.debug(exception);
        }
      }
    });

    /* END SEARCH UNITE LOCALE */

    vm.userSavedSuccessfully=function(user)
    {
      vm.current.user=user;
      vm.savedSuccessfully=true;
      $timeout(function () { vm.savedSuccessfully=false; }, 5000);
    };

    vm.createUser=function()
    {
      vm.current.user = new UserResource();
      vm.current.user.queteur_id = vm.current.id;
      vm.current.user.nivol      = vm.current.nivol;

      vm.current.user.$save(vm.userSavedSuccessfully, vm.errorWhileSavingFunction);
    };

    vm.userSave=function()
    {
      var user = new UserResource();
      user.id     = vm.current.user.id;
      user.active = vm.current.user.active;
      user.role   = vm.current.user.role;

      user.$update(vm.userSavedSuccessfully, vm.errorWhileSavingFunction);
    };

    vm.reinitPassword=function()
    {
      var user = new UserResource();
      user.id           = vm.current.user.id;
      user.queteur_id   = vm.current.id;
      user.nivol        = vm.current.nivol;

      user.$reInitPassword(vm.userSavedSuccessfully, vm.errorWhileSavingFunction);
    };

    vm.searchSimilar=function()
    {
      if(!vm.current.id && (vm.current.first_name || vm.current.last_name || vm.current.nivol))
      {
        QueteurResource.searchSimilarQueteurs({ 'first_name': vm.current.first_name,'last_name': vm.current.last_name,'nivol': vm.current.nivol }).$promise.then(function(queteurs)
        {
          vm.current.similarQueteurs = queteurs;
        });
      }
    };

    vm.goToQueteur=function(queteurId)
    {
      $location.path('/queteurs/edit/' + queteurId).replace();
    };


    vm.anonymize=function()
    {
      vm.current.anonymizeAskConfirmation=true;
    };

    vm.doAnonymize=function()
    {
      vm.current.doAnonymizeButtonDisabled=true;
      vm.current.$anonymize(vm.handleQueteur, vm.errorWhileSavingFunction);
    };

  }
})();

