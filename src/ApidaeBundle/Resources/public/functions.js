/**
 * Created by Nadia on 18/04/2016.
 */
$("document").ready(function() {
    $(".filtres").click(function() {
        console.log('ok');
        console.log('http://local.dev/Symfony/projetApidae/web/app_dev.php/recuperationJson/'  + $(this).val() + "test");
        $.ajax({
            type : $(this).attr( 'method' ),
            url  : 'http://local.dev/Symfony/projetApidae/web/app_dev.php/recuperationJson/'  + $(this).val()  + "test",
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
