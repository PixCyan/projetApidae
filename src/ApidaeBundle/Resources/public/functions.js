/**
 * Created by Nadia on 18/04/2016.
 */

var PATH = "http://apidae.swad.fr/web/bundles/apidae/imgApidae/";
var objetFavori;

$("document").ready(function() {
    $(".filtres").click(function() {
        //console.log('http://apidae.swad.fr/web/app_dev.php/fr/recuperationJson/'  + $(this).val() + "/" + $(this).attr('name'));
        if($(this).attr('checked')) {
            console.log("checked");
            $.ajax({
                type : 'POST',
                url  : 'http://apidae.swad.fr/fr/recuperationJson/'  + $(this).val()  + "/" + $(this).attr('name')  + "/" + $('#selectionId').text() + "/true",
                //url : 'http://local.dev/Symfony/projetApidae/web/app_dev.php/fr/recuperationJson/'  + $(this).val()  + "/" + $(this).attr('name'),
                contentType: "application/json; charset=utf-8",
                dataType: 'json',
                beforeSend: function() {
                    console.log('en attente');
                },
                success: function(data) {
                    getObjets(data);
                }
            });
        } else {
            console.log("unckecked");
            $.ajax({
                type: 'POST',
                url: 'http://apidae.swad.fr/fr/recuperationJson/' + $(this).val() + "/" + $(this).attr('name') + "/" + $('#selectionId').text()+ "/false" ,
                //url : 'http://local.dev/Symfony/projetApidae/web/app_dev.php/fr/recuperationJson/'  + $(this).val()  + "/" + $(this).attr('name'),
                contentType: "application/json; charset=utf-8",
                dataType: 'json',
                beforeSend: function () {
                    console.log('en attente');
                },
                success: function (data) {
                    getObjets(data);
                }
            });
        }
    });

    $('#resetFiltres').on('click', function () {
        var Status = $(this).val();
        $.ajax({
            type: 'POST',
            url: 'http://apidae.swad.fr/fr/recuperationJson/0/0/' + $('#selectionId').text()+ "/reset" ,
            //url : 'http://local.dev/Symfony/projetApidae/web/app_dev.php/fr/recuperationJson/'  + $(this).val()  + "/" + $(this).attr('name'),
            contentType: "application/json; charset=utf-8",
            dataType: 'json',
            beforeSend: function () {
                console.log('en attente');
            },
            success: function (data) {
                getObjets(data);
                $('input').each(function(){
                    $(this).removeAttr('checked');
                })
            }
        });
    });

    $(document).on('click', '.favoris', function() {
        objetFavori = $(this).attr('id');
        console.log(objetFavori);
    });


    $(".choixPanier").click(function() {
        console.log('http://apidae.swad.fr/fr/panier/ajouterObjet/'+ objetFavori +'/'+ $(this).attr('id'));
        $.ajax({
         type: 'POST',
         url: 'http://apidae.swad.fr/fr/panier/ajouterObjet/'+ objetFavori +'/'+ $(this).attr('id'),
         //url : 'http://local.dev/Symfony/projetApidae/web/app_dev.php/fr/recuperationJson/'  + $(this).val()  + "/" + $(this).attr('name'),
         contentType: "application/json; charset=utf-8",
         beforeSend: function () {
         console.log('en attente');
         },
         success: function (data) {
             objetFavori = undefined;
            console.log('Fin');
         }
         });
    });

    $(".choixNew").click(function() {
        console.log('http://apidae.swad.fr/fr/panier/nouveau/'+ objetFavori);
        window.location.href = 'http://apidae.swad.fr/fr/panier/nouveau/'+ objetFavori;
    });

});

function getObjets(data) {
    var services = data.services;
    var paiements = data.modesPaiements;
    var classments = data.classements;
    var objets = data.objets;
    var categories = data.categories;
    var tourismes = data.tourismesAdaptes;
    var langue = $('#langue').text();

    //récupération de la section contenant la liste des objets
    var divListe = $('#listesItems');
    $(divListe).empty();
    console.log('LANGUE : ' + langue);

    console.log("COUNT : "+ objets.length);

    //parcours de chaque objets
    $.each(objets, function(index) {
        var divObjet = '<div class="package-list-item clearfix" id="divObj'+ index +'"></div>';
        var divImage = '<div class="image"></div>';
        var divContentObjet = '<div class="content" id="divContObj'+ index +'"></div>';
        var divRow = '<div class="row gap-10" id="divRow'+ index +'"></div>';
        var divColonneInfos = '<div class="col-sm-12 col-md-9" id="divColInfos'+ index +'"></div>';
        var divColonneActions = '<div class="col-sm-12 col-md-3 text-right text-left-sm" id="divColAct'+ index +'"></div>';
        $(divObjet).appendTo($(divListe));

        if(objets[index].multimedias && objets[index].multimedias.length > 0) {
            if(objets[index].multimedias[0].mul_url_diapo != null) {
                $(divImage).append('<img src="'+ getPathMultimedia(objets[index].multimedias[0].mul_url_diapo, objets[index].id_obj )+'">').appendTo($('#divObj'+ index));
            } else {
                var nextImg = getNextImgPath(objets[index].multimedias[0]);
                if(nextImg != null) {
                    $(divImage).append('<img src="'+ getPathMultimedia(nextImg, objets[index].id_obj )+'">').appendTo($('#divObj'+ index));
                } else {
                    $(divImage).append('<img src="'+ imgDefaut +'">').appendTo($('#divObj'+ index));
                }
            }
        }  else {
            $(divImage).append('<img src="'+ imgDefaut +'">').appendTo($('#divObj'+ index));
        }

        //--- Informations réduites objet :
        $(divContentObjet).append('<h5>' + getLangueLib(objets[index].nom, langue) + '<a href="#"><button class="btn favoris" id="'+objets[index].id_obj+'" data-toggle="modal" data-target="#choix_panier"><i class=\"fa fa-heart-o\"></i></button></a></h5>').appendTo($('#divObj'+ index));
        $(divRow).appendTo($('#divContObj'+ index));

        if(objets[index].commune) {
            $(divColonneInfos).append('<ul class="list-info"><li><span class="icon"><i class="fa fa-map-marker"></i></span>' +
                ' <span class="font600">'+ getLangueLib(objets[index].commune.com_nom, langue) +'</span></li></ul>').appendTo($('#divRow'+ index));
        }

        //--- Tarifs en clair
        if(objets[index].tarif_en_clair) {
            $('#divColInfos'+ index).append('<p class="line18">'+ getLangueLib(objets[index].tarif_en_clair, langue) +'</p>').appendTo($('#divRow'+ index));
        }

        //--- Description
        var trad = '';
        if(langue == 'Fr') {
            trad = objets[index].traductions[0].tra_description_courte;
        } else if(langue == 'En') {
            trad = objets[index].traductions[1].tra_description_courte;
        }
        $('#divColInfos'+ index).append('<p class="line18">'+ trad  +'</p>');

        //--- Options/Actions :
        //var url = "{{ path('offre', {'id': '"+ objets[index].id_obj +"'}) }}";
        var url = Routing.generate('offre', {'_locale': langue.toLowerCase() ,'id': objets[index].id_obj });

        $(divColonneActions).append('<a href="'+ url +'" class="btn btn-primary btn-sm">'+ voirDetails +'</a>').appendTo($('#divRow'+ index));


        //var path = '{{ path("modifierOffre", {"offreId": objet.idObjet }) }}';
        var path = Routing.generate('modifierOffre', {'_locale': langue.toLowerCase() ,"offreId": objets[index].id_obj });
        if(admin == 1) {
            var optAdmin = ' <div class="optionsAdmin"><a href="'+ path +'">'+ modifier +'</a></div>';
            $($('#divColAct'+ index)).append(optAdmin);
        }
    });

    //--------- Traitement des filtres
    $('#titreClassement').text(" (" + data.countClassements + ")");
    $('#titreTypeHabitation').text( " (" + data.countCategories + ")");
    $('#titreTourisme').text(" (" + data.countTourisme + ")");
    $('#titreServices').text(" (" + data.countServices + ")");
    $('#titrePaiement').text(" (" + data.countPaiements + ")");
    console.log("Count paiements : " + data.countPaiements);

    $('#objetsTotal').text(objets.length);

    $(".filtres").each(function(){
        console.log("disabled");
        if(!$(this).attr('checked')) {
            $(this).attr('disabled', 'true');
            $("label[for='"+$(this).attr('id')+"']").css("color", "#D9D9D9");
        }
    });

    $.each(services, function(i) {
        var service = $("#"+ services[i].ser_id);
        //console.log(services[i].ser_id);
        if(service.attr('name') == "services") {
            service.removeAttr('disabled');
            $("label[for='"+ services[i].ser_id +"']").css("color", "black");
        }
    });

    $.each(classments, function(i) {
        var service = $("#"+ classments[i].lab_id);
        //console.log(classments[i].lab_id);
        if(service.attr('name') == "classements") {
            service.removeAttr('disabled');
            $("label[for='"+ classments[i].lab_id +"']").css("color", "black");
        }
    });

    $.each(categories, function(i) {
        var service = $("#"+ categories[i].cat_id);
        //console.log(categories[i].cat_id);
        if(service.attr('name') == "categories") {
            service.removeAttr('disabled');
            $("label[for='"+ categories[i].cat_id +"']").css("color", "black");
        }
    });

    $.each(paiements, function(i) {
        var service = $("#"+ paiements[i].ser_id);
        //console.log(paiements[i].ser_id);
        if(service.attr('name') == "paiements") {
            service.removeAttr('disabled');
            $("label[for='"+ paiements[i].ser_id +"']").css("color", "black");
        }
    });

    $.each(tourismes, function(i) {
        var service = $("#"+ tourismes[i].ser_id);
        //console.log(tourismes[i].ser_id);
        if(service.attr('name') == "tourismes") {
            service.removeAttr('disabled');
            $("label[for='"+ tourismes[i].ser_id +"']").css("color", "black");
        }
    });
    console.log('Fin');
}

function getPathMultimedia(url, idObj) {
    //Traitement de la châine PATH
    var array = url.split('/');
    var name = array.pop();
    var path = PATH + idObj + "/" + name;

    return path;
}

function getNextImgPath(multimedias) {
    var res = null;
    $.each(multimedias, function(key, value){
        if(value.mulUrlDiapo == null && res != null) {
            res = value.mulUrlDiapo;
        }
    });
    return res;
}

function getLangueLib(str, locale) {
    if (locale == undefined || locale == '') {
        locale = 'Fr';
    }
    var debut = strpos(str, '@' + locale + ':');
    if (debut === false) {
        return str;
    }
    debut += strlen('@' + locale + ':');
    var fin = strpos(str, '@', debut);
    return substr(str, debut, fin - debut);
}


function strpos(haystack, needle, offset) {
    var i = (haystack + '').indexOf(needle, (offset || 0))
    return i === -1 ? false : i
}

function strlen (string) {
    var str = string + '';
    var iniVal = (typeof require !== 'undefined' ? require('../info/ini_get')('unicode.semantics') : undefined) || 'off'
    if (iniVal === 'off') {
        return str.length
    }

    var i = 0;
    var lgth = 0;

    var getWholeChar = function (str, i) {
        var code = str.charCodeAt(i);
        var next = '';
        var prev = '';
        if (code >= 0xD800 && code <= 0xDBFF) {
            // High surrogate (could change last hex to 0xDB7F to
            // treat high private surrogates as single characters)
            if (str.length <= (i + 1)) {
                throw new Error('High surrogate without following low surrogate')
            }
            next = str.charCodeAt(i + 1);
            if (next < 0xDC00 || next > 0xDFFF) {
                throw new Error('High surrogate without following low surrogate')
            }
            return str.charAt(i) + str.charAt(i + 1)
        } else if (code >= 0xDC00 && code <= 0xDFFF) {
            // Low surrogate
            if (i === 0) {
                throw new Error('Low surrogate without preceding high surrogate')
            }
            prev = str.charCodeAt(i - 1);
            if (prev < 0xD800 || prev > 0xDBFF) {
                // (could change last hex to 0xDB7F to treat high private surrogates
                // as single characters)
                throw new Error('Low surrogate without preceding high surrogate')
            }
            // We can pass over low surrogates now as the second
            // component in a pair which we have already processed
            return false
        }
        return str.charAt(i)
    };

    for (i = 0, lgth = 0; i < str.length; i++) {
        if ((getWholeChar(str, i)) === false) {
            continue
        }
        // Adapt this line at the top of any loop, passing in the whole string and
        // the current iteration and returning a variable to represent the individual character;
        // purpose is to treat the first part of a surrogate pair as the whole character and then
        // ignore the second part
        lgth++
    }

    return lgth
}

function substr (str, start, len) {

    str += ''
    var end = str.length

    var iniVal = (typeof require !== 'undefined' ? require('../info/ini_get')('unicode.emantics') : undefined) || 'off'

    if (iniVal === 'off') {
        // assumes there are no non-BMP characters;
        // if there may be such characters, then it is best to turn it on (critical in true XHTML/XML)
        if (start < 0) {
            start += end
        }
        if (typeof len !== 'undefined') {
            if (len < 0) {
                end = len + end
            } else {
                end = len + start
            }
        }

        // PHP returns false if start does not fall within the string.
        // PHP returns false if the calculated end comes before the calculated start.
        // PHP returns an empty string if start and end are the same.
        // Otherwise, PHP returns the portion of the string from start to end.
        if (start >= str.length || start < 0 || start > end) {
            return false
        }

        return str.slice(start, end)
    }

    // Full-blown Unicode including non-Basic-Multilingual-Plane characters
    var i = 0
    var allBMP = true
    var es = 0
    var el = 0
    var se = 0
    var ret = ''

    for (i = 0; i < str.length; i++) {
        if (/[\uD800-\uDBFF]/.test(str.charAt(i)) && /[\uDC00-\uDFFF]/.test(str.charAt(i + 1))) {
            allBMP = false
            break
        }
    }

    if (!allBMP) {
        if (start < 0) {
            for (i = end - 1, es = (start += end); i >= es; i--) {
                if (/[\uDC00-\uDFFF]/.test(str.charAt(i)) && /[\uD800-\uDBFF]/.test(str.charAt(i - 1))) {
                    start--
                    es--
                }
            }
        } else {
            var surrogatePairs = /[\uD800-\uDBFF][\uDC00-\uDFFF]/g
            while ((surrogatePairs.exec(str)) !== null) {
                var li = surrogatePairs.lastIndex
                if (li - 2 < start) {
                    start++
                } else {
                    break
                }
            }
        }

        if (start >= end || start < 0) {
            return false
        }
        if (len < 0) {
            for (i = end - 1, el = (end += len); i >= el; i--) {
                if (/[\uDC00-\uDFFF]/.test(str.charAt(i)) && /[\uD800-\uDBFF]/.test(str.charAt(i - 1))) {
                    end--
                    el--
                }
            }
            if (start > end) {
                return false
            }
            return str.slice(start, end)
        } else {
            se = start + len
            for (i = start; i < se; i++) {
                ret += str.charAt(i)
                if (/[\uD800-\uDBFF]/.test(str.charAt(i)) && /[\uDC00-\uDFFF]/.test(str.charAt(i + 1))) {
                    // Go one further, since one of the "characters" is part of a surrogate pair
                    se++
                }
            }
            return ret
        }
    }
}

