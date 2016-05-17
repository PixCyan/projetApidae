/**
 * Created by Nadia on 18/04/2016.
 */
$("document").ready(function() {
    $(".filtres").click(function() {
        console.log('ok');
        console.log('http://local.dev/Symfony/projetApidae/web/app_dev.php/recuperationJson/'  + $(this).val() + "/" + $(this).attr('name'));
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

                //récupération de la section contenant la liste des objets
                var sectionListe = $("#listeDobjets");
                sectionListe.empty();
                //parcours de chaque objets
                $.each(data, function(index) {
                    var newArticle = '<article id="article'+ index +'"></article>';
                    $(newArticle).addClass("objetListe").append($('<h2>'+ data[index].nom +'</h2>')).appendTo($(sectionListe));

                    if(data[index].multimedias.length > 0) {
                        console.log("img");
                        var newFirstImg = '<div id="divImg'+ index +'"></div>';
                        $(newFirstImg).addClass("imgListe"+ index).appendTo($('#article'+index));
                        $('#divImg'+ index).append('<a href="'+ data[index].multimedias[0].mul_url_liste +'"><img src="'+ data[index].multimedias[0].mul_url_liste +'"> </a>');
                    }
                    //$('#test'+index).append("<toto></toto>");

                    var newDiv = '<div id="divInfos'+ index +'"></div>';
                    $(newDiv).addClass("infosListe").append($('<p>'+ data[index].commune.com_nom +'</p>')).appendTo($('#article'+index));
                    if(data[index].tarif_en_clair) {
                        $('#divInfos'+ index).append('<p>'+ data[index].tarif_en_clair +'</p>');
                    }

                    var newDivDescr = '<div> </div>';
                    $(newDivDescr).addClass("description").append('<h4> Description : </h4>')
                        .append('<p>'+ data[index].traductions[0].tra_description_courte  +'</p>')
                        .appendTo($('#divInfos'+ index));
                });

                console.log('Fin');
            }
        });
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