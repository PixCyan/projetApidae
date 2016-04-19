/**
 * Created by Nadia on 18/04/2016.
 */
$("document").ready(function() {
    $(".filtres").click(function() {
        console.log('ok');
        console.log('http://local.dev/Symfony/projetApidae/web/app_dev.php/recherche/hebergements/'  + $(this).val() + "-test");
        $.ajax({
            type : $(this).attr( 'method' ),
            url  : 'http://local.dev/Symfony/projetApidae/web/app_dev.php/recherche/hebergements/'  + $(this).val() + "-test",
            beforeSend: function() {
                console.log('en attente');
            },
            success: function(reponse) {
                //TODO function de réponse ajax
                alert('Ca marche');
            }
        });
    })
});

function chercher(libelleCategorie) {
    alert('filtres');
    console.log('http://local.dev/Symfony/projetApidae/web/app_dev.php/hebergements/'  + $(this).val() + libelleCategorie);
    $.ajax({
        type : $(this).attr( 'method' ),
        url  : 'http://local.dev/Symfony/projetApidae/web/app_dev.php/hebergements/'  + $(this).val() + libelleCategorie,
        beforeSend: function() {
          console.log('en attente');
        },
        success: function(reponse) {
        //TODO function de réponse ajax
        alert('Ca marche');
        }
    });
}