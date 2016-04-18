/**
 * Created by Nadia on 18/04/2016.
 */
function chercher(libelleCategorie) {
    alert('http://local.dev/Symfony/projetApidae/web/app_dev.php/hebergements/'  + $(this).val() + libelleCategorie);
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