//javascript
var Scheda = new Array();
var oid = Number(location.search.match(/oid=([0-9]+)/)[1]);
var smoid_search = null;

function createRequestObject() {
    var ro;
    var browser = navigator.appName;
    if (browser == "Microsoft Internet Explorer") {
        ro = new ActiveXObject("Microsoft.XMLHTTP");
    } else {
        ro = new XMLHttpRequest();
    }
    return ro;
}

function date_encode(mydate) {

    if (VF.dateEncode === 'iso')
        return mydate;

    var tk0 = mydate.split(' ');
    var d0 = tk0[0].split('-');
    if (d0.length != 3)
        return mydate;

    var ora0 = (tk0.length == 2) ? ' ' + tk0[1] : '';

    switch (VF.dateEncode) {

        case 'ita':
            return d0[2] + "/" + d0[1] + "/" + d0[0] + ora0;
            break;

        case 'eng':
            return d0[1] + "/" + d0[2] + "/" + d0[0] + ora0;
            break;
    }

}

function date_decode(mydate) {

    if (VF.dateEncode === 'iso')
        return mydate;

    var tk0 = mydate.split(' ');
    var d0 = tk0[0].split('/');
    if (d0.length != 3)
        return mydate;

    var ora0 = (tk0.length == 2) ? ' ' + tk0[1] : '';

    switch (VF.dateEncode) {

        case 'ita':
            return d0[2] + "-" + d0[1] + "-" + d0[0] + ora0;
            break;

        case 'eng':
            return d0[2] + "-" + d0[0] + "-" + d0[1] + ora0;
            break;
    }

}


/**
 * Ex sndReq 
 * @param String action
 * @param Integer offset
 * @param Boolean verifica_modifica
 * @returns {Boolean}
 */
function sndReq(action, offset, verifica_modifica) {

    jQuery('#refresh').show();

    // test se si è in fase di modifica
    if (verifica_modifica && VF.record_bloccato) {

        // se non sono state operate modifiche sui campi sblocca senza chiedere nulla
        if (VF.campi_mod.length == 0) {
            unlock();
            VF.record_bloccato = false;
        } else {
            // Mostra il CONFIRM
            var tralascia_modifiche = confirm(_("Warning") + "\n" + _('The record was not saved.') + "\n" + _('Discard changes?'));

            // sblocca e vai dove vuoi
            if (tralascia_modifiche) {
                unlock();
                VF.record_bloccato = false;
            } else {
                return false;
            }
        }

    }
    // PRENDI LA CHIAVE PRIMARIA DEL RECORD
    inputs = jQuery('input');

    if (VF.max == 0) {
        document.forms.singleform.reset();
        return false;
    } else if (!offset || offset == 'min') {
        VF.counter = 0;
    } else if (offset == 'next') {
        if (VF.counter + 1 == VF.max)
            return false;
        VF.counter++;
    } else if (offset == 'prev' && VF.counter > 0) {
        VF.counter--;
    } else if (offset == 'prev10' && VF.counter > 0) {
        VF.counter = (VF.counter - VF.passoVeloce);
    } else if (offset == 'next10' && VF.counter < VF.max) {
        VF.counter = (VF.counter + VF.passoVeloce);
    } else if (offset == 'max') {
        VF.counter = (VF.max - 1);
    } else if (offset == 'manual') {

        VF.counter = $('campo_goto').value - 1;
        if (VF.counter > (VF.max - 1)) {
            VF.counter = (VF.max - 1);
        } else if ((VF.counter < 1) || isNaN(VF.counter)) {
            VF.counter = 0;
        }
    } else if (offset == 'id') {
        if (VF.idRecord == 0) {
            VF.idRecord = VF.localIDRecord;
        }
    }

    stato_p();

    // disattiva la ricerca
    if (VF.ricerca) {
        annulla_ricerca();
    }

    // disattiva le modifiche
    annulla_campi(false);

    var url_string_rpc = VF.pathRelativo + '/rpc.php?action=' + action + '&c=' + VF.counter + '&id=' + VF.idRecord + '&hash=' + Math.random();
    url_string_rpc += '&' + window.location.search.substr(1);

    var data_type = 'xml';

    if (typeof (VF['outputType']) == 'string' && VF['outputType'] == 'JSON') {
        var data_type = 'json';
    }

    jQuery.ajax({
        url: url_string_rpc,
        dataType: data_type,
        complete: function () {
            jQuery('#refresh').hide();
        },
        success: function (output) {
            if (data_type === 'xml') {
                load_record_response_xml(output);
            } else {
                load_record_response_json(output);
            }
        }
    });
}


function pulisci_SUB() {

    jQuery('.table-submask-vis').hide();
    for (i = 0; i < VF.sottomaschere.length; i++) {
        jQuery('#sm_' + VF.sottomaschere[i]).css('font-weight', 'normal');
        jQuery('#sm_' + VF.sottomaschere[i]).val(VF.sottomaschere_alias[i]);
    }
}

/**
 * 
 * @param string action Table name
 * @returns void
 */
function sndReqUpdate(action) {

    inputs = jQuery('input');

    var post_string = '';

    for (j = 0; j < inputs.length; j++) {

        // PK
        if (inputs[j].id.substring(0, 3) == "pk_") {
            post_string += inputs[j].name + "=";
            post_string += inputs[j].value + "&";
        }

        // aggiungi gli hidden
        if (inputs[j].id.substring(0, 5) == "dati_" && inputs[j].type == 'hidden') {

            if (jQuery(inputs[j]).hasClass('autocomp_from_hidden') && (jQuery(inputs[j]).val() == '' || jQuery(inputs[j]).val() == null)) {
                continue;
            }

            VF.campi_mod[VF.campi_mod.length] = inputs[j].id;

            if (inputs[j].className == 'nomodify') {
                // Impostazioni per gli hidden DEFAULT
                var span_hidden = 'hd_' + inputs[j].id;
                variabile_hidden = $(span_hidden).innerHTML;
                inputs[j].value = variabile_hidden;
            }
        }


    }


    // AGGIUNGI GLI EVENTUALI FCK
    if (VF.fck_attivo) {
        for (i = 0; i < VF.fck_vars.length; i++) {

            editorfck = CKEDITOR.instances['dati_' + VF.fck_vars[i]];

            // attribuisco all'hidden che si chiama come il fckeditor il valore assunto dallo stesso
            $('dati_' + VF.fck_vars[i]).value = editorfck.getData();
        }
    }


    for (g = 0; g < VF.campi_mod.length; g++) {

        valore_post = null;

        // opzioni per i campi password
        if ($(VF.campi_mod[g]).type == 'password') {

            if ($(VF.campi_mod[g]).type == 'password'
                    && $(VF.campi_mod[g]).title == 'md5'
                    && $(VF.campi_mod[g]).value.length != 32
                    && $(VF.campi_mod[g]).value.length > 0
                    ) {

                $(VF.campi_mod[g]).value = hex_md5($F(VF.campi_mod[g]));
            } else if ($(VF.campi_mod[g]).type == 'password'
                    && $(VF.campi_mod[g]).title == 'sha1'
                    && $(VF.campi_mod[g]).value.length != 40
                    && $(VF.campi_mod[g]).value.length > 0
                    ) {

                $(VF.campi_mod[g]).value = hex_sha1($F(VF.campi_mod[g]));
            }

            valore_post = $(VF.campi_mod[g]).value;
        }

        // opzioni per le date
        else if ($(VF.campi_mod[g]).hasClassName('data')) {
            valore_post = date_decode($F(VF.campi_mod[g]));
        } else {
            valore_post = $(VF.campi_mod[g]).value;
        }

        post_string += $(VF.campi_mod[g]).name + "=";

        if (encodeURIComponent) {
            post_string += encodeURIComponent(valore_post) + "&";
        } else {
            post_string += escape(valore_post) + "&";
        }
    }


    var urlString = VF.pathRelativo + '/rpc.php?post=update&action=' + action + '&c=' + VF.counter;
    jQuery.ajax({
        url: urlString,
        type: 'POST',
        data: post_string,
        dataType: 'json',
        success: function (res) {

            if (res.error === false) {

                if (res.aff_rows > 0) {
                    setStatus(_('Record updated correctly'), 3500, 'risposta-giallo');
                    VF.campi_mod = new Array();
                } else {
                    setStatus(_('No changes in update'), 3500, 'risposta-arancio');
                }
            } else {
                setStatus(_('Error updating record') + ': ' + res.error_code.code + ' ' + res.error_code.msg, 6000, 'risposta-arancio');
            }
        }
    });

}

/**
 * 
 * @param string action
 * @returns void
 */
function sndReqPostNew(action) {

    var urlString = VF.pathRelativo + '/rpc.php?post=new&action=' + action;

    inputs = jQuery('input');

    var post_string = '';

    for (j = 0; j < inputs.length; j++) {

        // PK
        if (inputs[j].id.substring(0, 3) == "pk_") {
            post_string += inputs[j].name + "=";
            post_string += inputs[j].value + "&";
        }

        // aggiungi gli hidden
        if (inputs[j].id.substring(0, 5) == "dati_" && inputs[j].type == 'hidden') {

            if (jQuery(inputs[j]).hasClass('autocomp_from_hidden') && (jQuery(inputs[j]).val() == '' || jQuery(inputs[j]).val() == null)) {
                continue;
            }

            VF.campi_mod[VF.campi_mod.length] = inputs[j].id;

            // Impostazioni per gli hidden DEFAULT
            var span_hidden = 'hd_' + inputs[j].id;
            try {
                variabile_hidden = $(span_hidden).innerHTML;
                inputs[j].value = variabile_hidden;
            } catch (e) {

            }
        }
    }

    // AGGIUNGI GLI EVENTUALI FCK
    if (VF.fck_attivo) {
        for (i = 0; i < VF.fck_vars.length; i++) {

            editorfck = CKEDITOR.instances['dati_' + VF.fck_vars[i]];

            // attribuisco all'hidden che si chiama come il fckeditor il valore assunto dallo stesso
            $('dati_' + VF.fck_vars[i]).value = editorfck.getData();
        }
    }

    for (g = 0; g < VF.campi_mod.length; g++) {

        valore_post = '';

        // opzioni per i campi password
        if ($(VF.campi_mod[g]).type == 'password') {

            if ($(VF.campi_mod[g]).type == 'password'
                    && $(VF.campi_mod[g]).title == 'md5'
                    && $(VF.campi_mod[g]).value.length != 32
                    && $(VF.campi_mod[g]).value.length > 0
                    ) {

                $(VF.campi_mod[g]).value = hex_md5($F(VF.campi_mod[g]));

            } else if ($(VF.campi_mod[g]).type == 'password'
                    && $(VF.campi_mod[g]).title == 'sha1'
                    && $(VF.campi_mod[g]).value.length != 40
                    && $(VF.campi_mod[g]).value.length > 0
                    ) {

                $(VF.campi_mod[g]).value = hex_sha1($F(VF.campi_mod[g]));
            }
            valore_post = $(VF.campi_mod[g]).value;
        } else if ($(VF.campi_mod[g]).hasClassName('data')) {

            valore_post = date_decode($F(VF.campi_mod[g]));
        } else {

            valore_post = $(VF.campi_mod[g]).value;
        }

        post_string += $(VF.campi_mod[g]).name + "=";

        if (encodeURIComponent) {
            post_string += encodeURIComponent(valore_post) + "&";
        } else {
            post_string += escape(valore_post) + "&";
        }

    }

    jQuery.ajax({
        url: urlString,
        type: 'POST',
        data: post_string,
        dataType: 'json',
        success: function (res) {

            if (res.error != false) {
                setStatus(_('Unable to insert record') + ': ' + res.error_code.code + ' ' + res.error_code.msg, 6000, 'risposta-arancio');
            } 
            else {

                // cambia il valore di max
                VF.max = VF.max + 1;

                // aggiorna i contatori ed i pulsanti
                stato_p();

                // imposta l'idRecord
                VF.idRecord = res.id;
                sndReq(VF.tabella, 'id', false);

                // se è una scheda "nuovo valore"
                if (haveParent) {

                    jQuery.ajax({
                        url: VF.pathRelativo + '/rpc.refresh_iframe.php',
                        type: 'POST',
                        data: 'tabella=' + VF.parentTable + '&campo=' + VF.parentField,
                        success: function (hash_aggiornato) {

                            window.opener.document.getElementById('i_id_' + VF.parentField).src = 'files/html/' + hash_aggiornato + '.html';
                            window.opener.document.getElementById('i_id_' + VF.parentField).className = 'on';
                            window.opener.document.getElementById('i_id_' + VF.parentField).disabled = '';
                            window.opener.setStatus(_('Values of dropdown list') + ' ' + VF.parentField + ' ' + _('updated'), 6500, 'risposta-giallo');

                            setTimeout("self.close()", 800);
                            window.opener.focus();
                        }

                    });

                    window.opener.document.getElementById('i_id_' + VF.parentField).className = 'on';
                    window.opener.document.getElementById('i_id_' + VF.parentField).disabled = '';
                }

                setStatus(_('Record inserted correctly'), 3500, 'risposta-giallo');
                VF.campi_mod = new Array();
            }
        }
    });
}


/**
 * 
 * @param {type} action
 * @returns {undefined}
 */
function sndReqPostCerca(action) {

    if (VF.campi_mod.length > 0) {

        var post_string = '';
        var fromsub = '';

        for (var g = 0; g < VF.campi_mod.length; g++) {

            if (VF.campi_mod[g] == 'undefined') {
                continue;
            }

            if (isSearchFromSub(VF.campi_mod[g])) {
                fromsub = '&fromsub=' + smoid_search;
            }

            if (jQuery('#' + VF.campi_mod[g]).attr('type') == 'checkbox') {
                valore_ricerca = jQuery('#' + VF.campi_mod[g] + ':checked') ? 1 : 0;
            } else {
                valore_ricerca = (jQuery('#' + VF.campi_mod[g]).hasClass('data'))
                        ? date_decode(jQuery('#' + VF.campi_mod[g]).val()) : jQuery('#' + VF.campi_mod[g]).val();
            }

            post_string += jQuery('#' + VF.campi_mod[g]).attr('name') + "=";

            if (encodeURIComponent) {
                post_string += encodeURIComponent(valore_ricerca) + "&";
            } else {
                post_string += escape(valore_ricerca) + "&";
            }
        }

        var urlString = VF.pathRelativo + '/rpc.php?post=cerca&action=' + action + fromsub;
        jQuery.ajax({
            url: urlString,
            type: 'POST',
            data: post_string,
            success: function (risposta_sql) {
                search_response(risposta_sql);
            }
        });
    } else {
        setStatus(_('No search was attempted! <br/> Enter values in at least one field'), 3500, 'risposta-arancio');
    }
}

function search_response(risposta_sql) {
    var nRisultati = 0;
    var risultatiRicerca = new Array();

    if (risposta_sql.length == 0) {
        nRisultati = 0;
        // Scrivi il messaggio
        setStatus(_('No records found by this search'), 3500, 'risposta-verdino');
    } else if (risposta_sql.indexOf("|") == -1 && !isNaN(risposta_sql)) {
        nRisultati = 1
        VF.idRecord = risposta_sql;
        sndReq(VF.tabella, 'id', false);
        setStatus(_('1 record found and shown'), 3500, 'risposta-verdino');
    } else {
        risultatiRicerca = risposta_sql.split("|");
        nRisultati = risultatiRicerca.length;

        var qresults = new Ajax.Request(VF.pathRelativo + "/rpc.export_search.php",
                {
                    method: 'post',
                    parameters: 'table=' + VF.tabella + '&qresults=' + risposta_sql,
                    asynchronous: true
                });

        setStatus(nRisultati + ' ' + _('records found for this search'), 3500000, 'risposta-verdino');
        mostra_risultati_ricerca(risposta_sql);
        annulla_ricerca();
    }
}

function sndReqPostCercaFromGET(action, qs) {

    jQuery.ajax({
        url: VF.pathRelativo + '/rpc.php?post=cerca&action=' + action,
        type: 'POST',
        data: qs,
        success: function (risposta_sql) {
            search_response(risposta_sql);
        }
    });
}

function sndReqPostDelete(action) {
    var urlString = VF.pathRelativo + '/rpc.php?post=delete&action=' + action;

    inputs = jQuery('input');
    var post_string = '';

    for (j = 0; j < inputs.length; j++) {

        if (inputs[j].id.substring(0, 3) == "pk_") {
            post_string += inputs[j].name + "=";
            post_string += inputs[j].value + "&";
        }
    }

    jQuery.ajax({
        url: urlString,
        type: 'POST',
        data: post_string,
        success: function (risposta_sql) {

            if (risposta_sql == 1) {

                // cambia il valore di max
                VF.max = VF.max - 1;

                if (VF.max === 0) {
                    inizializza_pulsanti_modifica();
                } else {
                    // aggiorna i contatori ed i pulsanti
                    stato_p();
                }

                sndReq(VF.tabella, 'prev', false);

                setStatus(_('Record deleted correctly'), 3500, 'risposta-giallo');
            } else if (Number(risposta_sql) > 1) {
                erroreDB = erroreDBNum(risposta_sql);
                setStatus(erroreDB, 3500, 'risposta-arancio');
            } else {
                setStatus(_('Can not delete record'), 3500, 'risposta-arancio');
            }
        }
    });

}


/**
 * Ex sndReqPostDuplica
 * @param string action The table
 * @param int oid_sub
 * @param bool duplica_allegati
 * @param bool duplica_link
 * @returns void
 */
function duplicate_record(action, oid_sub, duplica_allegati, duplica_link) {

    var urlString = VF.pathRelativo + '/rpc.php?post=duplica&action=' + action + "&oid_sub=" + oid_sub + "&da=" + duplica_allegati + "&dl=" + duplica_link;

    inputs = jQuery('input');
    var post_string = '';

    for (j = 0; j < inputs.length; j++) {
        if (inputs[j].id.substring(0, 3) == "pk_") {
            post_string += inputs[j].name + "=";
            post_string += inputs[j].value + "&";
        }
    }

    jQuery.ajax({
        url: urlString,
        type: 'POST',
        data: post_string,
        success: function (risposta_sql) {

            var array_ris_duplica = risposta_sql.split("|");

            if (array_ris_duplica[0] == 1) {

                // cambia il valore di max
                VF.max = VF.max + 1;

                var IDduplicato = array_ris_duplica[1] - 0;
                sndReq(VF.tabella, 'id', false);

                $('popup-duplica').style.display = 'none';
                setStatus(_('Record duplicated correctly') + '. <a href="javascript:;" onclick="VF.idRecord=' + IDduplicato + ';sndReq(VF.tabella,\'id\',false);">' + _('Go to duplicated record') + '</a>', 10000, 'risposta-giallo');
            } else {
                setStatus(_('Can not update record'), 3500, 'risposta-arancio');
            }
        }
    });
}


/**
 * Take the key,value from the PK in the table
 * 
 * @returns {get_pk.Anonym$1|get_pk.Anonym$2}
 */
function get_pk() {

    var pkf = jQuery('input[id^="pk_"]');
    if (pkf.length > 0) {
        return {'key': pkf[0].id.substr(3), 'value': pkf[0].value};
    } else {
        return {};
    }
}


/**
 * Ex sndReqBlocca
 * Prova a bloccare un record in caso di modifica
 * @returns void
 */
function lock_record() {

    var PK = get_pk();

    var urlString = VF.pathRelativo + '/rpc.recordlock.php?tab=' + VF.tabella + '&col=' + PK.key + '&id=' + PK.value + '&blocca=1&hash=' + Math.random();

    jQuery.ajax({
        url: urlString,
        success: function (esito_sql) {

            if (esito_sql == 1) {
                attiva_campi('modifica');
                VF.tipo_salva = "modifica";
            } else {
                setStatus(_('This record is being edited by another user: Please try again later'), 4200, 'risposta-arancio');
            }
        }
    });
}


/**
 * Ex sndReqSblocca
 * @returns {undefined}
 */
function unlock() {

    var PK = get_pk();
    var urlString = VF.pathRelativo + '/rpc.recordlock.php?tab=' + VF.tabella + '&col=' + PK.key + '&id=' + PK.value + '&sblocca=1&hash=' + Math.random();

    jQuery.ajax({
        url: urlString,
        success: function (esito_sql) {
            if (esito_sql == 1) {
                VF.record_bloccato = false;
            }
        }
    });
}


/**
 * Ex sndReqGetone
 * @param {type} field
 * @param {type} id_record
 * @returns {undefined}
 */
function get_one(field, id_record) {

    var urlString = VF.pathRelativo + '/rpc.getone.php?oid=' + oid + '&id_record=' + id_record + '&field=' + field + '&hash=' + Math.random();

    jQuery.ajax({
        url: urlString,
        dataType: 'json',
        success: function (res) {
            jQuery('#dati_ac_' + res.field).val(res.value);
        }
    });
}


function stato_p() {

    var p_primo = jQuery('#p_primo')[0];
    var p_prev = jQuery('#p_prev')[0];
    var p_prev10 = jQuery('#p_prev10')[0];
    var p_next = jQuery('#p_next')[0];
    var p_next10 = jQuery('#p_next10')[0];
    var p_max = jQuery('#p_ultimo')[0];

    if (VF.counter === 0) {
        p_primo.disabled = true;
        p_prev.disabled = true;
        p_prev10.disabled = true;
    } else {
        p_primo.disabled = false;
        p_prev.disabled = false;
    }

    if (VF.counter - VF.passoVeloce < 0) {
        p_prev10.disabled = true;
    } else {
        p_prev10.disabled = false;
    }


    // next
    if (VF.counter + VF.passoVeloce >= VF.max) {
        p_next10.disabled = true;
    } else {
        p_next10.disabled = false;
    }

    if (VF.counter >= (VF.max - 1)) {
        p_max.disabled = true;
        p_next.disabled = true;
        p_next10.disabled = true;
    } else {
        p_max.disabled = false;
        p_next.disabled = false;
    }

    VF.modifiche_attive = false;
    jQuery('#p_save')[0].disabled = true;
    jQuery('#p_annulla')[0].disabled = true;


    if (VF.max > 0) {
        var html_w = _('Record') + ' <span id="goto" ondblclick="goto1();">' + (VF.counter + 1) + '</span> ' + _('of') + ' ' + VF.max;
        jQuery('#numeri').html(html_w);
        jQuery('#p_update')[0].disabled = false;
        jQuery('#p_delete')[0].disabled = false;
        jQuery('#p_cerca')[0].disabled = false;
    } else {
        jQuery('#p_update')[0].disabled = true;
        jQuery('#p_delete')[0].disabled = true;
        jQuery('#p_cerca')[0].disabled = true;
    }
}


/**
 * Ex handleResponse
 * @param object xml XML resource
 * @returns void
 */
function load_record_response_xml(xml) {

    // OPERAZIONI DI RESET	 ----------------------------------------------------------

    // Resetto i campi normali
    document.forms.singleform.reset();


    // Resetto gli evbentuali FCK editor
    if (VF.fck_attivo && VF.fck_vars.length > 0) {

        for (var f = 0; f < VF.fck_vars.length; f++) {
            CKEDITOR.instances["dati_" + VF.fck_vars[f]].setData('', blocca_ck);
        }
    }

    //---------------------------------------------------------------------------------

    var tag0 = xml.getElementsByTagName("recordset").item(0);
    var max = Number(tag0.attributes[0].value); // tot

    var tag1 = xml.getElementsByTagName("row").item(0);

    if (Number(VF.idRecord) > 0) {

        // prendo l'attributo 'offset' del tag row
        var takedCounter = tag1.attributes[0].value;
        VF.counter = Number(takedCounter) - 1;

        annulla();
    }

    // cancel all autocompleter_from
    jQuery('.autocomp_from_hidden').val('');

    var n_nodi_provvisorio = tag1.childNodes.length;
    var n_nodi = 0;
    var nomi_nodi = new Array();

    for (i = 0; i < n_nodi_provvisorio; i++) {

        // la condizione per Mozilla
        if (tag1.childNodes[i].nodeName != '#text') {
            n_nodi++;
            nomi_nodi[nomi_nodi.length] = tag1.childNodes[i].nodeName;
        }
    }

    Scheda = new Array();

    for (i = 0; i < n_nodi; i++) {

        try {
            valore = '';
            nome_nodo = nomi_nodi[i];
            var puntatore = xml.getElementsByTagName(nome_nodo).item(0);
            valore = (puntatore.firstChild.data) ? puntatore.firstChild.data : '';

            Scheda[i] = new Array(nome_nodo, valore);


            // IMPOSTA LA CHIAVE PRIMARIA DEL RECORD
            if ($("pk_" + nome_nodo)) {

                $("pk_" + nome_nodo).value = valore;
                VF.localIDRecord = valore;
            }

            // campi sola lettura
            if ($("dati_" + nome_nodo).className == 'onlyread-field') {

                $("dati_" + nome_nodo).innerHTML = valore + ' ';
            }

            // Esclusione per i campi hidden
            else if ($("dati_" + nome_nodo) && $("dati_" + nome_nodo).className != 'nomodify') {

                // Attribuzione DATA o DATETIME
                if ($("dati_" + nome_nodo).hasClassName('data')) {

                    $("dati_" + nome_nodo).value = date_encode(valore);
                }
                // Attribuzione GENERALE campo
                else {
                    $("dati_" + nome_nodo).value = valore;
                }


                // CONDIZIONE PER I CHECKBOX
                if ($("dati_" + nome_nodo).type == 'checkbox') {

                    if ($("dati_" + nome_nodo).value == '1' || (VF.PGdb == true && $("dati_" + nome_nodo).value == 't')) {
                        $("dati_" + nome_nodo).checked = true;
                    } else {
                        $("dati_" + nome_nodo).checked = false;
                    }

                }

                if (VF.fck_attivo && VF.fck_vars.inArray(nome_nodo)) {
                    CKEDITOR.instances["dati_" + nome_nodo].setData(valore);
                }
            }
        } catch (e) {
//					xmlError(e);
        }
    }

    VF.idRecord = 0;

    // prendi le sottomaschere
    if (VF.sottomaschere.length > 0) {
        richiediSUB();
    }

    // richiama gli allegati e i link se impostati
    if (VF.permettiAllegati != 0 || VF.permettiLink != 0) {
        richiediAL();
    }

    // call the embedded subforms
    if (VF.sm_embed.length > 0) {

        for (var l = 0; l < VF.sm_embed.length; l++) {

            richiediEMBED(VF.sm_embed[l]);
        }
    }

    for (var a = 0; a < VF.campiAutocompleterFrom.length; a++) {

        if (VF.campiAutocompleterFrom[a] != '') {
            tmp_var1 = VF.campiAutocompleterFrom[a].substr(5);
            tmp_val1 = $(VF.campiAutocompleterFrom[a]).value;
        }
        get_one(tmp_var1, tmp_val1);
    }
    stato_p();
}

/**
 * Ex handleResponseJSON
 * 
 * @param object json Resource JSON
 * @returns void
 */
function load_record_response_json(json) {

    // OPERAZIONI DI RESET	 ----------------------------------------------------------
    // 
    // Resetto i campi normali
    document.forms.singleform.reset();

    // Resetto gli evbentuali FCK editor
    if (VF.fck_attivo && (VF.fck_vars.length > 0)) {

        for (var f = 0; f < VF.fck_vars.length; f++) {
            CKEDITOR.instances["dati_" + VF.fck_vars[f]].setData('', blocca_ck);
        }
    }
    //---------------------------------------------------------------------------------

    var max = Number(json.tot); // tot

    // prendo l'offset del tag row
    if (Number(VF.idRecord) > 0) {
        var takedCounter = json.row[0].offset;
        VF.counter = Number(takedCounter) - 1;
        annulla();
    }

    // cancel all autocompleter_from
    jQuery('.autocomp_from_hidden').val('');

    var n_nodi = 0;
    var nomi_nodi = new Array();

    for (i in json.row[0].data) {
        nomi_nodi[n_nodi++] = i;
    }

    Scheda = new Array();

    for (i = 0; i < n_nodi; i++) {

        try {
            valore = '';
            nome_nodo = nomi_nodi[i];
            valore = json.row[0].data[nome_nodo];
            Scheda[i] = new Array(nome_nodo, valore);

            // IMPOSTA LA CHIAVE PRIMARIA DEL RECORD
            if ($("pk_" + nome_nodo)) {

                $("pk_" + nome_nodo).value = valore;
                VF.localIDRecord = valore;
            }

            // campi sola lettura
            if ($("dati_" + nome_nodo).className == 'onlyread-field') {
                $("dati_" + nome_nodo).innerHTML = valore + ' ';
            }

            // Esclusione per i campi hidden
            else if ($("dati_" + nome_nodo) && $("dati_" + nome_nodo).className != 'nomodify') {

                // Attribuzione DATA o DATETIME
                if ($("dati_" + nome_nodo).hasClassName('data')) {
                    $("dati_" + nome_nodo).value = date_encode(valore);
                }
                // Attribuzione GENERALE campo
                else {
                    $("dati_" + nome_nodo).value = valore;
                }


                // CONDIZIONE PER I CHECKBOX
                if ($("dati_" + nome_nodo).type == 'checkbox') {

                    if ($("dati_" + nome_nodo).value == '1' || (VF.PGdb == true && $("dati_" + nome_nodo).value == 't')) {
                        $("dati_" + nome_nodo).checked = true;
                    } else {
                        $("dati_" + nome_nodo).checked = false;
                    }
                }

                if (VF.fck_attivo && VF.fck_vars.inArray(nome_nodo)) {
                    CKEDITOR.instances["dati_" + nome_nodo].setData(valore);
                }
            }
        } catch (e) {
            // xmlError(e);
        }
    }

    VF.idRecord = 0;

    // prendi le sottomaschere
    if (VF.sottomaschere.length > 0) {
        richiediSUB();
    }

    // richiama gli allegati e i link se impostati
    if (VF.permettiAllegati != 0 || VF.permettiLink != 0) {
        richiediAL();
    }

    // call the embedded subforms
    if (VF.sm_embed.length > 0) {
        for (var l = 0; l < VF.sm_embed.length; l++) {
            richiediEMBED(VF.sm_embed[l]);
        }
    }

    if (VF.autoload_geom) {
        load_geojson();
    }

    for (var a = 0; a < VF.campiAutocompleterFrom.length; a++) {
        if (VF.campiAutocompleterFrom[a] != '') {
            var tmp_var1 = VF.campiAutocompleterFrom[a].substr(5);
            var tmp_val1 = $(VF.campiAutocompleterFrom[a]).value;
        }
        get_one(tmp_var1, tmp_val1);
    }

    stato_p();

}


function xmlError(e) {
    //there was an error, show the user
    alert(e);
} //end function xmlError


/* FUNZIONI PER L'INIZIALIZZAZIONE */

function inizializza_pulsanti_modifica() {

    if ((VF.tendineAttese - VF.nTendine) == 0 && (!VF.fck_attivo || VF.fck_pronti == VF.fck_vars.length)) {
        inizializza_scheda();
    }
    // altrimenti aspetta che le tendine siano caricate ed esegui le operazioni
}

function triggerLoadTendina() {

    VF.nTendine++;
    if ((VF.tendineAttese - VF.nTendine) == 0 && (!VF.fck_attivo || VF.fck_pronti == VF.fck_vars.length)) {
        inizializza_scheda();
    }
}


function FCKeditor_OnComplete(editorInstance) {

    if (editorInstance.Name) {
        VF.fck_pronti++;
    }

    if (VF.fck_pronti == VF.fck_vars.length && (VF.tendineAttese - VF.nTendine) == 0) {
        inizializza_scheda();
    }

}

function CKeditor_OnComplete() {


    // Temporary workaround for providing editor 'read-only' toggling functionality.
    (function ()
    {
        var cancelEvent = function (evt)
        {
            evt.cancel();
        };

        CKEDITOR.editor.prototype.readOnly = function (isReadOnly)
        {
            // Turn off contentEditable.
            this.document.$.body.disabled = isReadOnly;
            CKEDITOR.env.ie ? this.document.$.body.contentEditable = !isReadOnly
                    : this.document.$.designMode = isReadOnly ? "off" : "on";

            // Prevent key handling.
            this[ isReadOnly ? 'on' : 'removeListener' ]('key', cancelEvent, null, null, 1);
            this[ isReadOnly ? 'on' : 'removeListener' ]('selectionChange', cancelEvent, null, null, 1);

            // Disable all commands in wysiwyg mode.
            var command,
                    commands = this._.commands,
                    mode = this.mode;

            for (var name in commands)
            {
                command = commands[ name ];
                isReadOnly ? command.disable() : command[ command.modes[ mode ] ? 'enable' : 'disable' ]();
                this[ isReadOnly ? 'on' : 'removeListener' ]('state', cancelEvent, null, null, 0);
            }
        }
    })();


    VF.fck_pronti = VF.fck_vars.length;


    for (i = 0; i < VF.fck_vars.length; i++) {

        CKEDITOR.instances['dati_' + VF.fck_vars[i]].on('key', function (ee) {
            if (VF.modificaRecord || VF.nuovoRecord || VF.ricerca) {
                mod(ee.sender.name);
            } else {
                return false;
            }
        });
    }

    blocca_ck();

    if (VF.fck_pronti == VF.fck_vars.length && (VF.tendineAttese - VF.nTendine) == 0) {

        inizializza_scheda();
    }
}

function triggerFCK() {


}


function inizializza_scheda() {

    // nascondi i div preloader
    //jQuery('#pop-loader-contenitore').remove();
    jQuery('#loader-scheda0').remove();

    if (VF.max == 0) {
        jQuery('#p_update')[0].disabled = true;
        jQuery('#numeri').html(_('There are no records in this table'));
        jQuery('#refresh').hide();
        stato_p();
    } else {
        if (VF.counter == 0) {
            sndReq(VF.tabella, 'min', false);
        } else {
            sndReq(VF.tabella, 'id', false);
        }

    }

    $('p_save').disabled = true;
    $('p_annulla').disabled = true;

    VF.initScheda = true;

    // manda l'eventuale ricerca di GET
    if (VF.GETqs != '') {
        sndReqPostCercaFromGET(VF.tabella, VF.GETqs);
    }


}


function attiva_campi(classe) {

    if (classe == 'ricerca') {
        cn = 's';
    } else {
        cn = 'on';
    }

    inputs = jQuery('input');
    textareas = jQuery('textarea');
    selects = jQuery('select');

    for (j = 0; j < inputs.length; j++) {

        if (cn == 's' && inputs[j].hasClassName('hh_field')) {

            inputs[j].readOnly = false;
            chfield(inputs[j], cn);

        } else if (inputs[j].id.substring(0, 5) == "dati_"
                && inputs[j].type != 'hidden'
                && inputs[j].type != 'checkbox'
                && !inputs[j].hasClassName('hh_field')) {

            inputs[j].readOnly = false;
            chfield(inputs[j], cn);

        } else if (inputs[j].id.substring(0, 5) == "dati_" && inputs[j].type == 'checkbox') {
            inputs[j].disabled = false;
            chfield(inputs[j], cn);
            if (VF.PGdb == true) {
                inputs[j].value = (inputs[j].checked == true) ? 't' : 'f';
            } else {
                inputs[j].value = (inputs[j].checked == true) ? 1 : 0;
            }
        }
    }

    for (j = 0; j < textareas.length; j++) {
        if (textareas[j].id.substring(0, 5) == "dati_") {
            textareas[j].readOnly = false;
            chfield(textareas[j], cn);
        }
    }

    for (j = 0; j < selects.length; j++) {
        if (selects[j].id.substring(0, 5) == "dati_") {
            selects[j].disabled = false;
            chfield(selects[j], cn);
        }
    }

    // FCK
    if (VF.fck_attivo) {

        if (classe == 'ricerca') {
            for (var i = 0; i < VF.fck_vars.length; i++) {
                CKEDITOR.instances["dati_" + VF.fck_vars[i]].setData('', attiva_ck);
            }
        }
        attiva_ck();
    }


    $('p_update').disabled = true;
    $('p_annulla').disabled = false;


}

function disattiva_campi() {

    inputs = jQuery('input');
    textareas = jQuery('textarea');
    selects = jQuery('select');

    for (j = 0; j < inputs.length; j++) {
        if (inputs[j].id.substring(0, 5) == "dati_") {

            if (inputs[j].type == 'checkbox') {
                inputs[j].disabled = true;
            } else {
                inputs[j].readOnly = true;
            }

            // eccezione per gli hidden
            if (inputs[j].className != 'nomodify') {
                if (inputs[j].hasClassName('data')) {
                    inputs[j].className = 'off data';
                } else {
                    chfield(inputs[j], 'off');
                }

            }

        }
    }


    for (j = 0; j < textareas.length; j++) {
        if (textareas[j].id.substring(0, 5) == "dati_") {
            textareas[j].readOnly = true;
            chfield(textareas[j], 'off');
        }
    }


    for (j = 0; j < selects.length; j++) {
        if (selects[j].id.substring(0, 5) == "dati_") {
            selects[j].disabled = true;
            chfield(selects[j], 'off');
        }
    }

    // FCK
    if (VF.fck_attivo) {
        blocca_ck();
    }
}


function modifica() {

    VF.record_bloccato = true;
    VF.modificaRecord = true;
    lock_record();

}


function annulla_ricerca() {

    VF.ricerca = false;

    $('p_cerca').value = " " + _(' Search ') + " ";
    $('p_cerca').className = '';
    $('p_cerca').onClick = 'cerca();';

    $('p_annulla').disabled = true;
    $('p_insert').disabled = false;
    $('p_delete').disabled = false;

    jQuery('.pulsante-submask').each(function (i, e) {
        e.enable();
    });
}


function annulla() {


    if (VF.ricerca) {
        annulla_ricerca();
    } else {
        unlock();
    }

    VF.campi_mod = new Array();
    annulla_campi(true);
}

function annulla_campi(manda) {

    if (manda) {
        sndReq(VF.tabella, VF.counter, false);
    }


    disattiva_campi();

    VF.modifiche_attive = false;

    VF.nuovoRecord = false;
    VF.modificaRecord = false;

    $('p_save').disabled = true;
    $('p_annulla').disabled = true;
    $('p_update').disabled = false;

}


function mod(id) {
    if (!VF.ricerca) {
        VF.modifiche_attive = true;
        $('p_save').disabled = false;

    } else {
        // cattura della pressione di invio per la ricerca
        if (window.event) {
            k = (window.event) ? window.event.keyCode : id.which;
            if (k == 13) {
                invia_ricerca();
            }
        }
    }

    $('p_annulla').disabled = false;

    trovato = false;

    // search on autocompleter_from
    if (id.substr(0, 8) == 'dati_ac_') {
        id = 'dati_' + id.substr(8);
    }


    // se non c'� gia'
    for (t = 0; t < VF.campi_mod.length; t++) {
        if (VF.campi_mod[t] == id) {
            trovato = true;
        }
    }

    if (!trovato) {
        VF.campi_mod[VF.campi_mod.length] = id;
    }
}

function modfck(ofck) {
    if (VF.modificaRecord) {
        mod(ofck.Name);
    }
}

function salva() {

    msg = controlla_dati();

    if (msg != '') {
        alert(_("Warning!") + "\n" + msg);
        return false;
    } 
    else {

        // Controllo sui campi YAV 
        if (VF.jstest) {
            test_yav = performCheck('singleform', rules, 'classic');
            if (!test_yav)
                return false;
        }

        if (VF.tipo_salva == 'modifica') {
            unlock();
            sndReqUpdate(VF.tabella);
            VF.modificaRecord = false;
        } 
        else if (VF.tipo_salva == 'nuovo') {
            sndReqPostNew(VF.tabella);
            VF.nuovoRecord = false;
        }
        
        VF.modifiche_attive = true;
        $('p_save').disabled = true;
        $('p_annulla').disabled = true;

        annulla_campi(false);
    }
}


function nuovo_record() {

    $('p_save').disabled = false;
    $('p_annulla').disabled = false;
    $('p_update').disabled = true;

    f = document.forms.singleform;

    f.reset();

    attiva_campi('nuovo');

    VF.tipo_salva = "nuovo";

    VF.nuovoRecord = true;

    pulisci_SUB();

    // Metti una variabile 'new' su localIDRecord utile per allegati e link
    VF.localIDRecord = 'new';
    richiediAL();

    if (VF.fck_attivo) {
        for (i = 0; i < VF.fck_vars.length; i++) {
            CKEDITOR.instances["dati_" + VF.fck_vars[i]].setData('', attiva_ck);
        }
    }

    // READONLY fields
    jQuery('.hh_field').each(function () {
        jQuery(this).val('');
    });
    jQuery('.autocomp_from_hidden').each(function () {
        jQuery(this).val('');
    });

}


function cerca() {

    if (VF.ricerca) {
        invia_ricerca();
    }

    $('p_save').disabled = true;
    $('p_annulla').disabled = false;
    $('p_update').disabled = true;
    $('p_insert').disabled = true;
    $('p_delete').disabled = true;

    inputs = jQuery('input');
    textareas = jQuery('textarea');

    // eventually embedded subforms
    jQuery('.embed-nodata').hide();
    jQuery('.sub-search').show();


    jQuery('.table-submask-vis').each(function () {

        table_on_search(jQuery(this));
    });


    f = document.forms.singleform;
    f.reset();

    attiva_campi('ricerca');

    // Metti una variabile 'new' su localIDRecord utile per allegati e link
    VF.localIDRecord = 'ric';
    richiediAL();

    VF.ricerca = true;

    $('p_cerca').value = _('Start search');
    $('p_cerca').className = 'var';

    jQuery('.pulsante-submask').each(function (i, e) {
        e.disable();
    });
}

function table_on_search(obj) {

    var trs = jQuery(obj).find('tr');

    for (var i = 2; i < trs.length; i++) {
        jQuery(trs[i]).remove();
    }

    jQuery(trs[1]).find('input,checkbox,select,textarea').each(function () {
        jQuery(this).val('');
    });
}


function isSearchFromSub() {

    smoid_search = null;

    if (VF.campi_mod.length == 0) {

        return false;
    } else {

        var pattern = /^dati__[0-9]+__.*/g;

        for (g = 0; g < VF.campi_mod.length; g++) {

            if (VF.campi_mod[g].match(pattern)) {

                smoid_search = jQuery("#" + VF.campi_mod[g]).parents().find('table.table-submask').attr('id').substr(7);
                return true;
            }
        }

        return false;
    }
}


function invia_ricerca() {

    sndReqPostCerca(VF.tabella);
}


function elimina() {
    if (confirm(_('Do you really want to delete this record? This operation cannot be undone.'))) {
        sndReqPostDelete(VF.tabella);
    }
}


function duplica() {
    if (confirm(_('Do you really want to duplicate the record? Data on subforms and any attachments and links will not be duplicated automatically.'))) {
        duplicate_record(VF.tabella);
    }
}

function prepara_duplica() {

    var mydiv = $('popup-duplica');

    var arg_sub = '';
    var DA = 0;
    var DL = 0;


    ii = mydiv.getElementsByTagName('input');

    for (i = 0; i < ii.length; i++) {

        if (ii[i].name.substr(0, 7) == 'sotto__' && ii[i].checked == true) {
            arg_sub += ii[i].name.substr(7) + '_';
        } else if (ii[i].name == 'duplica_allegati' && ii[i].checked == true) {

            DA = 1;
        } else if (ii[i].name == 'duplica_link' && ii[i].checked == true) {

            DL = 1;
        }

    }

    duplicate_record(VF.tabella, arg_sub, DA, DL);

}


function controlla_dati() {

    var msg_controllo = '';
    var errore = false;

    for (var i = 0; i < VF.campiReq.length; i++) {

        nome_campo = "dati_" + VF.campiReq[i];

        if ($(nome_campo).className == 'onlyread-field') {
            // skip
        } else if ($(nome_campo).value == '' || $(nome_campo).value == null) {
            msg_controllo += _('The field') + ' ' + VF.campiReq[i] + ' ' + _('must be completed') + '\n';
            errore = true;
        }
    }

    return msg_controllo;

}

function debug_var() {

    var str = '';
    var val = '';
    for (i in VF) {
        val = (typeof (VF[i]) == 'object') ? '[' + VF[i].join(',') + ']' : VF[i];
        str += typeof (VF[i]) + ' ' + i + ": " + val + "\n";
    }
    alert(str);
    return str;
}


function setStatus(messaggio, tempo, classe) {
    $('feedback').style.visibility = "visible";
    $('risposta').innerHTML = messaggio;
    $('risposta').className = classe;
    setTimeout("$('feedback').style.visibility = 'hidden'; ", tempo);
}


function erroreDBNum(n) {

    n = n - 0;

    // Codici Errori di Postgres
    if (VF.PGdb) {

        if (n == 1451) {
            return _('Can not delete the record <br/> there are related records');
        } else if (n == 23505) {
            return _('Can\'t add record - Duplicate key');
        } else if (n == 23503) {
            return _('Unable to add a record - The reference is missing and/or not connected to the reference table.');
        } else if (n == 1345) {
            return _('Can not delete from join view');
        } else {
            return _('Can not do this <br/> (Error Code:') + n + ')';
        }
    }
    // Codici Errori di MYSQL
    else {

        if (n == 1451) {
            return _('Can not delete the record <br/> there are related records');
        } else if (n == 1022) {
            return _('Can\'t add record - Duplicate key');
        } else if (n == 1452) {
            return _('Unable to add a record - The reference is missing and/or not connected to the reference table.');
        } else if (n == 1395) {
            return _('Can not delete from join view');
        } else {
            return _('Can not do this <br/> (Error Code:') + n + ')';
        }
    }

}


function goto1() {

    attuale_numero = $('goto').innerHTML;
    if (!isNaN(attuale_numero - 0)) {
        $('goto').innerHTML = '<input type="text" class="micro" size="5" name="campo_goto" id="campo_goto" value="' + attuale_numero + '" onkeypress="return noNumbers(event)"/></form>';
    }

    $('campo_goto').focus;

}


function noNumbers(e)
{
    var keynum;
    var keychar;
    var numcheck;

    if (window.event) { // IE

        keynum = e.keyCode
    } else if (e.which) { // Netscape/Firefox/Opera

        keynum = e.which
    }

//	alert(keynum);
    if (keynum == 13) {

        if ($('campo_goto').value.substring(0, 3) == 'id:') {
            // apre il record di id:
            VF.idRecord = $('campo_goto').value.substring(3);
            sndReq(VF.tabella, 'id', false);
        } else {
            // apre il record numero:
            sndReq(VF.tabella, 'manual', false);
        }
    }
    /*keychar = String.fromCharCode(keynum)
     numcheck = /\d/
     return !numcheck.test(keychar)*/
}

function catturaInvio(e)
{
    var keynum
    var keychar
    var numcheck

    if (window.event) { // IE

        keynum = e.keyCode
    } else if (e.which) { // Netscape/Firefox/Opera

        keynum = e.which
    }

//	alert(keynum);
    if (keynum == 13) {

        invia_ricerca();
    }

    //alert(keynum);
}


function switch_vista() {

    if (VF.focusScheda) {

        // Switch:
        $('scheda1').style.display = 'none';
        $('scheda-tabella').style.display = '';

        $('p_prev').style.display = 'none';
        $('p_next').style.display = 'none';

        if ($('popup-hotkeys') != undefined) {
            $('popup-hotkeys').style.display = 'none';
        }

        // Inizializza la tabella
        VF.focusScheda = false;

        if (!VF.ricerca && VF.max > 0) {
            caricaGrid();
        }

        if (VF.usaHistory) {

            var segnalibro = new Object();
            segnalibro.pos = "tab";
            segnalibro.counterHist = VF.counter;
            segnalibro.idRecordHist = VF.idRecord;
            dhtmlHistory.add("tab", segnalibro);
        }
    } else {

        // Switch:
        $('scheda1').style.display = '';
        $('scheda-tabella').style.display = 'none';

        $('p_prev').style.display = '';
        $('p_next').style.display = '';

        $('popup-hotkeys').style.display = '';

        VF.focusScheda = true;

        if (VF.usaHistory) {

            var segnalibro = new Object();
            segnalibro.pos = "scheda";
            segnalibro.counterHist = VF.counter;
            segnalibro.idRecordHist = VF.idRecord;
            dhtmlHistory.add("scheda", segnalibro);
        }

        if (VF.ricerca) {

            $('p_annulla').enable();
        }
    }

}


// FUNZIONI DI HISTORY


function history_initialize() {

    window.dhtmlHistory.create({
        toJSON: function (o) {
            return Object.toJSON(o);
        }, fromJSON: function (s) {
            return s.evalJSON();
        }
    });

    // initialize the DHTML History framework
    dhtmlHistory.initialize();

    // subscribe to DHTML history change events
    dhtmlHistory.addListener(historyChange);

    var segnalibro = new Object();
    segnalibro.pos = 'scheda';
    segnalibro.counterHist = VF.counter;
    segnalibro.idRecordHist = VF.idRecord;
    dhtmlHistory.add('scheda', segnalibro);
}

function historyChange(newLocation, historyData) {

    if (newLocation == 'scheda' && VF.focusScheda == false) {
        switch_vista();
    } else if (newLocation == 'tab' && VF.focusScheda == true) {
        switch_vista();
    }
}


// ------------------------------ fine history	


function openWindow(url, name, percent) {
    var w = 630, h = 440; // default sizes
    if (window.screen) {
        w = window.screen.availWidth * percent / 100;
        h = window.screen.availHeight * percent / 100;
    }

    window.open(url, name, 'width=' + w + ',height=' + h + ' ,toolbar=yes, location=no,status=yes,menubar=no,scrollbars=yes,resizable=yes');
}


function apri_submask(id_table_parent, id_submask, on_shadowbox) {

    inputs = jQuery('input');

    for (i = 0; i < inputs.length; i++) {
        if (inputs[i].id.substring(0, 3) == "pk_") {
            var pk = inputs[i].value;
            var var_pk_id = inputs[i].id.substring(3);
        }
    }

    // se la chiave da passare è la PK
    if (var_pk_id == VF.fkparent[id_submask]) {

        var link_submask = 'sottomaschera.php?oid_parent=' + id_table_parent + '&id_submask=' + id_submask + '&pk=' + pk;

        if (on_shadowbox == true) {
            Shadowbox.open({content: link_submask, player: 'iframe', width: '780'});
        } else {
            openWindow(link_submask, 'submask_' + id_submask, 60);
        }
    } else {
        campo_fk_sub = $('dati_' + VF.fkparent[id_submask]);
        val_fk_sub = $(campo_fk_sub).value;

        var link_submask = 'sottomaschera.php?oid_parent=' + id_table_parent + '&id_submask=' + id_submask + '&pk=' + val_fk_sub;

        if (on_shadowbox == true) {
            Shadowbox.open({content: link_submask, player: 'iframe', width: '780'});
        } else {
            openWindow(link_submask, 'submask_' + id_submask, 60);
        }
    }
}


Array.prototype.inArray = function (value) {
    var i;
    for (i = 0; i < this.length; i++) {
        // Matches identical (===), not just similar (==).
        if (this[i] === value) {
            return true;
        }
    }
    return false;
};


function attiva_modifica_fck(editorInstance, nome_campo) {
    //editorInstance.Events.AttachEvent( 'OnSelectionChange', modfck ) ;
}

function blocca_ck() {

    for (i = 0; i < VF.fck_vars.length; i++) {

        CKEDITOR.instances['dati_' + VF.fck_vars[i]].readOnly(true);
        CKEDITOR.instances['dati_' + VF.fck_vars[i]].document.$.body.style.backgroundColor = '#EEEEFF';

    }
}


function attiva_ck() {

    for (i = 0; i < VF.fck_vars.length; i++) {
        CKEDITOR.instances['dati_' + VF.fck_vars[i]].readOnly(false);
        if (VF.ricerca) {
            var color_ck = '#EEFFEE';
        } else {
            var color_ck = '#FFFFEE';
        }
        CKEDITOR.instances['dati_' + VF.fck_vars[i]].document.$.body.style.backgroundColor = color_ck;
    }
}

function hotKeys(event) {

    // Get details of the event dependent upon browser
    event = (event) ? event : ((window.event) ? event : null);

    // We have found the event.
    if (event) {

        // Hotkeys require that either the control key or the alt key is being held down
        if (event.ctrlKey || event.altKey || event.metaKey) {

            // Pick up the Unicode value of the character of the depressed key.
            var charCode = (event.charCode) ? event.charCode : ((event.which) ? event.which : event.keyCode);

            // Convert Unicode character to its lowercase ASCII equivalent
            //      var myChar = String.fromCharCode (charCode).toLowerCase();
            var myChar = charCode;

            // Convert it back into uppercase if the shift key is being held down
//	      if (event.shiftKey) {myChar = myChar.toUpperCase();}

            // Now scan through the user-defined array to see if character has been defined.
            for (var i = 0; i < keyActions.length; i++) {

                // See if the next array element contains the Hotkey character
                if (keyActions[i].character == myChar
                        && (
                                (keyActions[i].mod == 'ALT+SHIFT' && (event.altKey || event.metaKey) && event.shiftKey)
                                ||
                                (keyActions[i].mod == 'CTRL+SHIFT' && event.ctrlKey && event.shiftKey)
                                ||
                                (keyActions[i].mod == 'CTRL' && event.ctrlKey)
                                ||
                                (keyActions[i].mod == 'ALT' && (event.altKey || event.metaKey))
                                ||
                                (keyActions[i].mod == '' && event.metaKey)
                                )
                        ) {

                    // Yes - pick up the action from the table
                    var action;

                    // If the action is a hyperlink, create JavaScript instruction in an anonymous function
                    if (keyActions[i].actionType.toLowerCase() == "link") {
                        action = new Function('location.href  ="' + keyActions[i].param + '"');
                    }

                    // If the action is JavaScript, embed it in an anonymous function
                    else if (keyActions[i].actionType.toLowerCase() == "code") {
                        action = new Function(keyActions[i].param);
                    }

                    // Error - unrecognised action.
                    else {
                        alert(_('Hotkey Function Error: Action should be "link" or "code"'));
                        break;
                    }

                    // At last perform the required action from within an anonymous function.
                    action();

                    // Hotkey actioned - exit from the for loop.
                    break;
                }
            }
        }
    }
} //-- fine funzione


function chfield(obj, cl) {

    if (obj.hasClassName('off'))
        obj.removeClassName('off');
    if (obj.hasClassName('on'))
        obj.removeClassName('on');
    if (obj.hasClassName('s'))
        obj.removeClassName('s');

    obj.addClassName(cl);
}


function get_autocompleter_from_id(text, li) {

    // [1]=filed name
    // [2]=val
    var tkk = li.id.split('___');

    $('dati_' + tkk[1]).value = tkk[2];
}


function get_scheda_val(campo) {

    var parsed_campo = campo.split(':');

    for (i = 0; i < Scheda.length; i++) {
        if (undefined != Scheda[i] && parsed_campo[0] == Scheda[i][0]) {

            // options: nome_campo, nome_campo:value
            if (parsed_campo[1] == 'label') {

                // Case autocompleter_from
                if (jQuery('#dati_' + parsed_campo[0]).prop("tagName") == 'INPUT') {
                    v = jQuery('#dati_ac_' + parsed_campo[0]).val();
                }
                // Case select_from
                else {
                    v = jQuery('#dati_' + parsed_campo[0] + '>option:selected').text();
                }

                return (undefined != v) ? v : '';
            } else {
                return Scheda[i][1];
            }
        }
    }

    // fake else
    return '';
}


function entry_table_search() {

    switch_vista();
    table_search_mode(1);
}


function table_search_mode(mode) {

    if (mode == 0) {

        $('buttons_on_research').hide();
        $('pulsanti').show();
        $('counter_container').show();
    } else {
        $('buttons_on_research').show();
        $('pulsanti').hide();
        $('counter_container').hide();
    }
}


function exit_table_search() {

    table_search_mode(0);
    switch_vista();
    setStatus('');
    annulla();
}


// -------------------------------------------------------


/**
 * @TODO
 * @returns void
 */
function check_perm() {
    var urlString = VF.pathRelativo + '/rpc.perm.php?action=' + VF.tabella_alias + '&id=' + VF.localIDRecord + '&hash=' + Math.random();

    jQuery.ajax({
        url: urlString,
        success: function (Perm) {

        }
    });
}


/*
 * AJAX CALLS ---------------------------------
 */

function richiediAL() {

    var urlString = VF.pathRelativo + '/rpc.allegati_link.php?action=' + VF.tabella_alias + '&id=' + VF.localIDRecord + '&hash=' + Math.random()
    jQuery.ajax({
        url: urlString,
        success: function (AL) {

            // aggiorna i campi
            var arrayAL = AL.split(",");
            // allegati
            if (VF.permettiAllegati == 1) {
                if (arrayAL[0] > 0) {
                    $('href_tab_allegati').innerHTML = '<strong>' + _('attachments') + ' (' + arrayAL[0] + ')</strong>';
                } else {
                    $('href_tab_allegati').innerHTML = _('attachments') + ' (0)';
                }
            }

            // Link
            if (VF.permettiLink == 1) {
                if (arrayAL[1] > 0) {
                    $('href_tab_link').innerHTML = '<strong>' + _('link') + ' (' + arrayAL[1] + ')</strong>';
                } else {
                    $('href_tab_link').innerHTML = _('link') + ' (0)';
                }
            }
        }
    });
}


function richiediSUB() {

    var stringaSUB = VF.pathRelativo + '/rpc.subcount.php?action=' + VF.tabella + '&id=' + VF.localIDRecord + '&subs=' + VF.sottomaschere.join('|') + '&hash=' + Math.random();
    jQuery.ajax({
        url: stringaSUB,
        success: function (SUB) {

            // aggiorna i campi
            var arraySUB = SUB.split(",");

            for (i = 0; i < VF.sottomaschere.length; i++) {

                var val_sub = ((arraySUB[i] - 0) > 0) ? " (" + arraySUB[i] + ")" : "";
                $('sm_' + VF.sottomaschere[i]).style.fontWeight = ((arraySUB[i] - 0) > 0) ? 'bold' : 'normal';
                $('sm_' + VF.sottomaschere[i]).value = VF.sottomaschere_alias[i] + val_sub;
            }
        }
    });
}


function richiediEMBED(sm_embed_id) {

    var urlSUB = 'sottomaschera.php?oid_parent=' + oid + '&pk=' + VF.localIDRecord + '&id_submask=' + sm_embed_id;
    jQuery.ajax({
        url: urlSUB,
        success: function (htmlEMBED) {
            $('sm_embed_' + sm_embed_id).innerHTML = htmlEMBED;
        }
    }).done(function () {
        jQuery('#sm_embed_' + sm_embed_id + ' .select_values').each(function (i, el) {
            var hash_js = jQuery(el).data('require');
            var target = jQuery(el).data('target');
            console.log(target);
            jQuery.getJSON(VF.basePath + '/files/html/' + hash_js + '.json', function (data) {
                jQuery(el).children('.toup-' + target).each(function (j, subel) {
                    // Blank value
                    jQuery(subel).append(jQuery('<option>', {value: '', text: ''}));
                    // values
                    for (var i = 0; i < data.length; i++) {
                        jQuery(subel).append(jQuery('<option>', {
                            value: data[i][0],
                            text: data[i][1]
                        }));
                    }
                    // Set value
                    jQuery(subel).val(jQuery(subel).data('startval'));
                });
            });
        });
    });
}


function doOnRowSelected(idRiga) {

    setStatus(_('Loading...'), 3500, 'risposta-verdino');
    VF.idRecord = idRiga;
    sndReq(VF.tabella, 'id', false);
    switch_vista();
    $('buttons_on_research').hide();
    $('pulsanti').show();
    setStatus(_('Loading done'), 3500, 'risposta-verdino');
}


function caricaGrid() {
    if (VF.htmltable == 'datatables') {
        loadDataTables();
    }
    // 'dhtmlxgrid'
    else {
        loadDHTMLXGrid();
    }
}


function loadDHTMLXGrid() {

    if (!VF.initGrid) {

        var FIELD_NAME_COL = VF.xg_alias;

        mygrid = new dhtmlXGridObject('gridbox');
        mygrid.imgURL = "js/dhtmlxgrid4/codebase/imgs/";
        mygrid.setHeader("#," + FIELD_NAME_COL);
        mygrid.setInitWidths("40," + VF.xg_misure);
        mygrid.setColAlign("center," + VF.xg_align);
        mygrid.setColTypes("ro," + VF.xg_tipo);
        mygrid.setColSorting("int," + VF.xg_sort);
        mygrid.setSkin("dhx_" + VF.skin);
        mygrid.attachEvent("onRowDblClicked", doOnRowSelected);
        mygrid.enableMultiline(false);

        togli = (VF.counter % VF.xg_pages);
        nextData = VF.counter - togli;

        var w_obj = jQuery.query.get('w');
        var w_string = '';

        jQuery.each(w_obj, function (k, v) {
            w_string += '&w[' + k + ']=' + v;
        });

        var stringUrl = VF.pathRelativo + "/rpc.xmlgrid.php?ty=dhtmlxgrid_json&t=" + VF.tabella + "&gid=" + VF.gid + "&of=" + nextData + "&hash=" + Math.random() + w_string;

        // Attach event for sort
        mygrid.attachEvent("onBeforeSorting", function (ind, type, direction) {

            var col_name = FIELD_NAME_COL.split(',')[ind - 1];
            var stringUrlSort = stringUrl + "&ord=" + jQuery.trim(col_name) + '&sort=' + direction;

            this.clearAndLoad(stringUrlSort, dhtmlxgrid_resize, 'json');
            this.setSortImgState(true, ind, direction); //sets a correct sorting image
            return false;

        });

        // Initialize
        mygrid.init();

        // Se non è in contesto di ricerca, dopo l'inizializzazione carica i dati
        if (!VF.ricerca) {
            mygrid.load(stringUrl, dhtmlxgrid_resize, 'json');
        }

        VF.initGrid = true;
    } else {

        reloadGrid();
    }

}

function doAfterRefresh() {

}


function loadDataTables() {

    if (!VF.initGrid) {

        togli = (VF.counter % VF.xg_pages);
        nextData = VF.counter - togli;

        var w_obj = jQuery.query.get('w');
        var w_string = '';

        jQuery.each(w_obj, function (k, v) {
            w_string += '&w[' + k + ']=' + v;
        });


        jQuery('#gridTableView').dataTable({
            ajax: VF.pathRelativo + "/rpc.jsongrid.php?t=" + VF.tabella + "&gid=" + VF.gid + "&of=" + nextData + "&hash=" + Math.random() + w_string,
            scrollX: true,
        });

        VF.initGrid = true;
    } else {

        reloadGrid();
    }

}

function reloadGrid() {

    var w_obj = jQuery.query.get('w');
    var w_string = '';

    jQuery.each(w_obj, function (k, v) {
        w_string += '&w[' + k + ']=' + v;
    });

    if (!VF.focusScheda) {
        togli = (VF.counter % VF.xg_pages);
        nextData = VF.counter - togli;

        if (VF.htmltable == 'dhtmlxgrid') {
            mygrid.clearAll();
            if (!VF.ricerca) {
                var stringURL = VF.pathRelativo + "/rpc.xmlgrid.php?ty=dhtmlxgrid_json&t=" + VF.tabella + "&gid=" + VF.gid + "&of=" + nextData + "&hash=" + Math.random() + w_string
                mygrid.load(stringURL, null, 'json');
            }
        }
    }
}

function mostra_risultati_ricerca(ids_ricerca) {

    entry_table_search();

    if (!initGrid) {
        caricaGrid();
    }

    jQuery.ajax({
        url: VF.pathRelativo + "/rpc.save_search.php",
        type: 'post',
        data: 'q=' + ids_ricerca,
        asynch: false,
        success: function (res) {
            ids_ricerca = res;
            mygrid.clearAll();
            var stringURL = VF.pathRelativo + "/rpc.xmlgrid.php?ty=dhtmlxgrid_json&t=" + VF.tabella + "&gid=" + VF.gid + "&q=" + res + "&hash=" + Math.random();
            mygrid.load(stringURL, null, 'json');
        }
    });
}


function isset(varname) {
    if (typeof (window[varname]) != "undefined") {
        return true;
    } else {
        return false;
    }
}

/** 
 * Set the auto height of the dhtmlxgrid
 * @returns void
 */
function dhtmlxgrid_resize(loadtype) {

    var goffset = Math.ceil(jQuery('#gridbox').offset().top);
    var wh = jQuery(window).outerHeight(true);
    var new_h = wh - goffset - 8;

    var gtable = jQuery('#gridbox .objbox table').outerHeight(true);
    var gcont = jQuery('#gridbox .objbox').outerHeight(true);

    var h;
    if (loadtype === 'reload') {
        var maxgcont = (gtable > gcont) ? gtable : gcont;
        var gridboxh = jQuery('#gridbox .xhdr').outerHeight(true) + maxgcont;
        h = (new_h > gridboxh) ? gridboxh : new_h;
    } else if (loadtype === 'init') {
        h = (new_h > gtable) ? gtable : new_h;
    } else {
        h = new_h;
    }

    jQuery('#gridbox').height(h);

    if (loadtype === true) {
        reloadGrid();
    }
}

var decodeHtmlEntity = function (str) {
    return str.replace(/&#(\d+);/g, function (match, dec) {
        return String.fromCharCode(dec);
    });
};


function show_map_geojson(field, data) {

    if (typeof window['map'] == 'object') {
        map.removeLayer(myGeoJSON2);
    } else {
        map = L.map('map-' + field).setView([51.505, -0.09], 13);
        // create the tile layer with correct attribution
        var osmUrl = 'http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
        var osmAttrib = 'Map data © <a href="http://openstreetmap.org">OpenStreetMap</a> contributors';
        var osm = new L.TileLayer(osmUrl, {attribution: osmAttrib});
        map.addLayer(osm);
    }
    myGeoJSON2 = L.geoJson(data);
    myGeoJSON2.addTo(map);
    map.fitBounds(myGeoJSON2.getBounds());
}

function clear_map_geojson() {
    if (typeof window['myGeoJSON2'] == 'object') {
        map.removeLayer(myGeoJSON2);
    }
}

function load_geojson() {

    var url = VF.pathRelativo + "/rpc.geojson.php";

    jQuery.ajax({
        url: url,
        dataType: 'json',
        type: 'post',
        data: 't=' + VF.tabella + '&pk=' + jQuery('input.pkname').data('pkname') + '&pkvalue=' + jQuery('input.pkname').val() + '&f=' + VF.geom_field,
        success: function (res) {
            var o = {
                type: "Feature",
                geometry: res
            };
            show_map_geojson(VF.geom_field, o);
        }
    });
}

jQuery(document).ready(function () {

    if (window.location.hash == '#tab') {
        switch_vista();
    }

    jQuery('[name^="dati["]').on('change keypress keyup', function () {
        if (!jQuery(this).attr('readonly'))
            mod(jQuery(this).attr('id'));
    });

    jQuery('#refresh').html('<img src="./img/refresh1.gif" width="12" heigth="12" alt="caricamento..." /> ' + _('Updating ...'));

    // Ajax setup
    jQuery.ajaxSetup({
        beforeSend: function () {
        },
        complete: function () {
        },
        success: function () {}
    });

    // get fiters
    jQuery('.cancel_filter').on('click', function () {
        var filter_to_canc = jQuery(this).attr('rel');
        new_url = jQuery.query.REMOVE('w[' + filter_to_canc + ']');
        window.location.search = new_url;
    });

    jQuery('#p_duplica').on('click', function () {
        jQuery('#popup-duplica').toggle();
    });

    jQuery('.cancel_all_filter').on('click', function () {

        new_url = jQuery.query.REMOVE('w');
        window.location.search = new_url;
    });

    if (VF.fck_attivo) {
        var cccc = 0;
        CKEDITOR.on('instanceReady', function (ev) {
            cccc++;
            if (cccc == VF.fck_vars.length) {
                CKeditor_OnComplete();
            }
        });
    }

    // filter on field content
    jQuery('.filter_by_field').on('click', function () {
        var fld = jQuery(this).data('k');
        var qs = window.location.search + "&w[" + fld + "]=" + jQuery('#dati_' + fld).val();
        window.location = qs;
    });

    jQuery('.select_values').each(function (i, el) {
        var hash_js = jQuery(el).data('require');
        var target = jQuery(el).data('target');
        jQuery.getJSON(VF.basePath + '/files/html/' + hash_js + '.json', function (RS) {

            // Blank value
            jQuery('#dati_' + target).append(jQuery('<option>', {value: '', text: ''}));
            for (var i = 0; i < RS.length; i++) {

                jQuery('#dati_' + target).append(jQuery('<option>', {
                    value: RS[i][0],
                    text: RS[i][1]
                }));
            }
        }).done(function () {
            jQuery('#feed_' + target).hide();
            triggerLoadTendina();
        });
    });

    dhtmlxgrid_resize();

    jQuery('.geometry-button').on('click', function () {
        var field = jQuery(this).data('trigger');
        VF.autoload_geom = true;
        VF.geom_field = field;
        load_geojson();
    });

});

jQuery(window).on('resize', function () {
    dhtmlxgrid_resize('reload');
});


