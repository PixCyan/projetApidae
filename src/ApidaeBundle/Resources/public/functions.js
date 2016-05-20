/**
 * Created by Nadia on 18/04/2016.
 */
$("document").ready(function() {
    $(".filtres").click(function() {
        console.log('http://local.dev/Symfony/projetApidae/web/app_dev.php/recuperationJson/'  + $(this).val() + "/" + $(this).attr('name'));
        if($(this).attr('checked')) {
            console.log("checked");
            $.ajax({
                type : 'POST',
                url  : 'http://local.dev/Symfony/projetApidae/web/app_dev.php/recuperationJson/'  + $(this).val()  + "/" + $(this).attr('name'),
                contentType: "application/json; charset=utf-8",
                dataType: 'json',
                beforeSend: function() {
                    console.log('en attente');
                },
                success: function(data) {
                    //TODO function de réponse ajax
                    console.log('Ca marche');
                    var services = data.services;
                    var paiements = data.modesPaiements;
                    var classments = data.classements;
                    var objets = data.objets;
                    var categories = data.categories;
                    var tourismes = data.tourismesAdaptes;

                    //récupération de la section contenant la liste des objets
                    var sectionListe = $("#listeDobjets");
                    sectionListe.empty();
                    //parcours de chaque objets
                    $.each(objets, function(index) {
                        var newArticle = '<article id="article'+ index +'"></article>';
                        $(newArticle).addClass("objetListe").append($('<h2>'+ objets[index].nom +'</h2>')).appendTo($(sectionListe));

                        if(objets[index].multimedias && objets[index].multimedias.length > 0) {
                            var newFirstImg = '<div id="divImg'+ index +'"></div>';
                            $(newFirstImg).addClass("imgListe"+ index).appendTo($('#article'+index));
                            if(objets[index].multimedias[0].mul_url_liste) {
                                $('#divImg'+ index).append('<a href="'+ objets[index].multimedias[0].mul_url_liste +'"><img src="'+ objets[index].multimedias[0].mul_url_liste +'"> </a>');
                            } //TODO else ?
                        }
                        //$('#test'+index).append("<toto></toto>");

                        var newDiv = '<div id="divInfos'+ index +'"></div>';
                        $(newDiv).addClass("infosListe").appendTo($('#article'+index));
                        if(objets[index].commune) {
                            $('#divInfos'+ index).append($('<p>'+ objets[index].commune.com_nom +'</p>'));
                        }

                        if(objets[index].tarif_en_clair) {
                            $('#divInfos'+ index).append('<p>'+ objets[index].tarif_en_clair +'</p>');
                        }

                        var newDivDescr = '<div></div>';
                        $(newDivDescr).addClass("description").append('<h4> Description : </h4>')
                            .append('<p>'+ objets[index].traductions[0].tra_description_courte  +'</p>')
                            .appendTo($('#divInfos'+ index));

                        var newDivLiens = '<div></div>';
                        var url = '{{ path("offre", {"id": "'+ objets[index].id_obj +'"}) }}';
                        $(newDivLiens).addClass("lienDetailsListe")
                            .append('<a href="'+ url +'">Voir le détail</a>')
                            .appendTo($('#divInfos'+ index));
                    });

                    //--------- Traitement des filtres
                    $(".filtres").each(function(){
                        console.log("disabled");
                        if(!$(this).attr('checked')) {
                            $(this).attr('disabled', 'true');
                            $("label[for='"+$(this).attr('id')+"']").css("color", "#D9D9D9");
                        }
                    });

                    $.each(services, function(i) {
                        var service = $("#"+ services[i].ser_id);
                        console.log(services[i].ser_id);
                        if(service.attr('name') == "services") {
                            service.removeAttr('disabled');
                            $("label[for='"+ services[i].ser_id +"']").css("color", "black");
                        }
                    });

                    $.each(classments, function(i) {
                        var service = $("#"+ classments[i].lab_id);
                        console.log(classments[i].lab_id);
                        if(service.attr('name') == "classements") {
                            service.removeAttr('disabled');
                            $("label[for='"+ classments[i].lab_id +"']").css("color", "black");
                        }
                    });

                    $.each(categories, function(i) {
                        var service = $("#"+ categories[i].cat_id);
                        console.log(categories[i].cat_id);
                        if(service.attr('name') == "categories") {
                            service.removeAttr('disabled');
                            $("label[for='"+ categories[i].cat_id +"']").css("color", "black");
                        }
                    });

                    $.each(paiements, function(i) {
                        var service = $("#"+ paiements[i].ser_id);
                        console.log(paiements[i].ser_id);
                        if(service.attr('name') == "services") {
                            service.removeAttr('disabled');
                            $("label[for='"+ paiements[i].ser_id +"']").css("color", "black");
                        }
                    });

                    $.each(tourismes, function(i) {
                        var service = $("#"+ tourismes[i].ser_id);
                        console.log(tourismes[i].ser_id);
                        if(service.attr('name') == "services") {
                            service.removeAttr('disabled');
                            $("label[for='"+ tourismes[i].ser_id +"']").css("color", "black");
                        }
                    });


                    console.log('Fin');
                }
            });
        } else {
            //TODO if unchecked
            console.log("unckecked");
        }
    })
});


//TODO ajouter le lien pour détails
//TODO mettre la bonne langue

/*
Exemple
 $('.imgProjet').click(function() {
     var idProjet = $(this).attr('id').split('-')[1];
     $.ajax({
     url: '{{path('ajax_get_projet')}}',
     type: 'POST',
     data: {'id': idProjet},
     dataType: 'json',
     success: function(data) {
         afficherImages(data.photos);
         afficherProjet(data.projet, data.etapes);
     }
 });
 });
 */