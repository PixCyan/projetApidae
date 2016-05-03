/**
 * Created by Nadia on 18/04/2016.
 */
$("document").ready(function() {
    $(".filtres").click(function() {
        console.log('ok');
        console.log('http://local.dev/Symfony/projetApidae/web/app_dev.php/recuperationJson/'  + $(this).val() + "/test");
        $.ajax({
            type : 'POST',
            url  : 'http://local.dev/Symfony/projetApidae/web/app_dev.php/recuperationJson/'  + $(this).val()  + "/test",
            contentType: "application/json; charset=utf-8",
            dataType: 'json',
            beforeSend: function() {
                console.log('en attente');
            },
            success: function(reponse) {
                //TODO function de réponse ajax
                console.log('Ca marche');
            }
        });
    })
});


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