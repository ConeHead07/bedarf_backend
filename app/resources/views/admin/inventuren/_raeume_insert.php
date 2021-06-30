<?php
/**
 * Created by PhpStorm.
 * User: f.barthold
 * Date: 20.11.2020
 * Time: 14:47
 */

$data = (isset($data) && is_array($data)) ? $data : [];
?>
<style>
    .html-templates {
        display: none;
    }
</style>
<div class="html-templates" id="raumHtmlTemplates">
    <div class="ui modal modal-raum-insert">
        <i class="close icon"></i>
        <div class="header">
            Dialog
        </div>
        <div class="content" id="modalRaumInsertContent">
        </div>
        <div class="actions">
            <div class="ui black deny button">
                Abbrechen
            </div>
            <div class="ui positive right labeled icon button">
                Speichern
                <i class="checkmark icon"></i>
            </div>
        </div>
    </div>
    <form class="ui form form-raum-insert">
        <h4 class="ui dividing header">Neue Raumdaten</h4>
        <div class="field">
            <label>Gebaeude</label>
            <select name="gid" class="ui fluid dropdown"></select>
        </div>
        <div class="field">
            <label>Etage</label>
            <input type="text" name="Etage" list="datalistEtagen" placeholder="Etage">
            <datalist id="datalistEtagen"></datalist>
        </div>
        <div class="field">
            <label>Raum</label>
            <input type="text" name="Raum" placeholder="Raum">
        </div>
        <div class="field">
            <label>Raumbezeichnung</label>
            <input type="text" name="Raumbezeichnung" placeholder="Raumbezeichnung">
        </div>
        <input type="hidden" name="jobid" value="<?= $jobid ?? 0 ?>">

        <div class="ui error message"></div>
    </form>
</div>
<script>

    var raum = raum || {};
    raum.formRaumConfig = {
        fields: {
            Gebaeude: {
                identifier: 'gid',
                rules: [
                    {
                        type   : 'minCount[1]',
                        prompt : 'Bitte gebe ein Gebäude an'
                    }
                ]
            },
            Etage: {
                identifier: 'Etage',
                rules: [
                    {
                        type   : 'empty', // 'minCount[2]',
                        prompt : 'Bitte gebe die Etage an'
                    }
                ]
            },
            Raum: {
                identifier: 'Raum',
                rules: [
                    {
                        type   : 'minLength[1]',
                        prompt : 'Bitte gebe einen Raum an'
                    }
                ]
            },
            Raumbezeichnung: {
                identifier: 'Raumbezeichnung',
                rules: [
                    {
                        type   : 'minLength[0]',
                        prompt : 'Bitte gebe eine Raumbezeichnung an'
                    }
                ]
            }
        }
    };
    raum.insert = function(data, opt) {
        console.log('#105 raum.insert', { data, opt });
        var args = arguments;
        return new Promise(function(resolve, reject)
        {
            console.log('#109 raum.insert new Promise');
            $('#formRaumInsert').form('validate form');
            if (!$('#formRaumInsert').form('is valid')) {
                console.log('#159');
                return false;
            }
            console.log('Save new Raum-data', {args});
            $.post(
                '/api/admin/raeume/insert',
                data,
                function(response) {
                    if ('success' in response && response.success) {
                        resolve(response.row);
                    } else {
                        reject( response.message || '', response);
                    }
                }
            ).fail(function() {
                console.log('#127 raum.insert $.post.fail');
                reject('Unbekannter Fehler!', {});
            });
        })
    },
    raum.openInsertDialog = function(data, opts) {

        $("#modalRaumInsert.modal-raum-insert").remove();
        $("#formRaumInsert.form-raum-insert").remove();

        var modal = $("#raumHtmlTemplates .modal-raum-insert").clone();
        var form = $("#raumHtmlTemplates .form-raum-insert").clone();
        modal.attr("id", "modalRaumInsert");
        form.attr("id", "formRaumInsert").form( this.formRaumConfig );
        form.find("select").dropdown();

        var jobid = ('jobid' in data)
            ? data.jobid
            : form.find(':input[name=jobid]').val();

        var gebaeudeList = [];
        var etagenList = [];

        var o = typeof opts === 'object' ? opts : {};
        var target = o.target || 'body';
        var title = o.title || 'Bild';
        var onSubmit = o.onSubmit || function(){};
        var onSuccess = o.onSuccess || function(){};
        var onError  = o.onError || function(){};
        var onCancel = o.onCancel || function(){};

        for(var sName in data) {
            var d = data[sName];
            var values = Array.isArray(d) ? d : [ d ];
            for(var di = 0; di < values.length; di++) {
                var val = values[di];

                form.find(':input[name=' + sName + '],:input[name=' + sName + $.escapeSelector('[]') + ']').each(function() {
                    if (['checkbox', 'radio'].indexOf(this.type) > -1) {
                        $(this).prop('checked', $(this).val() === val);
                    }
                    else if (['select', 'select-multiple', 'select-one'].indexOf(this.type) > -1) {
                        $(this).find('option:selected').prop('selected', false);
                        $(this).find('option').each(function() {
                           if ( this.value === val) $(this).prop('selected', true);
                        });
                    }
                    else {
                        $(this).val( val );
                    }
                });
            }
        }

        modal.find('.header').html(title);
        modal.find('#modalRaumInsertContent').append( form );

        var inputGeb = form.find('select[name=gid]');
        var inputEtg = form.find('input[name=Etage]');
        var datalistEtg = $('datalist#' + inputEtg.attr('list'));

        var fitEtagenAutocomplete = function() {
            console.log('#118 ', { inputGeb: inputGeb.html() });
            if (!datalistEtg.length) {
                return false;
            }

            var gid = inputGeb.val();
            var etagen = (gid in etagenList) ? etagenList[gid] : [];
            // datalistEtg.find('option').remove();

            for(var i = 0; i < etagen.length; i++) {
                var _opt = $('<option/>').val(etagen[i]);
                console.log('#136 ', { optVal: _opt.val(), i });
                datalistEtg.append( _opt );
            }

            console.log('#137 fitEtagenAutocomplete', {
                datalistEtg,
                datalistEtgHtml: datalistEtg.html(),
                gid,
                etagen
            });
        };

        $.get('/api/admin/inventuren/' + jobid + '/gebaeude', function(data) {
            gebaeudeList = data.rows;
            console.log('#150 ', { gebaeudeList });
            for(var i = 0; i < gebaeudeList.length; i++) {
                var itm = gebaeudeList[i];
                var opt = $('<option/>')
                    .val( itm.gid )
                    .text( itm.Gebaeude );

                inputGeb.append( opt );

                console.log('#159 ', { i, itm, opt, gebaeudeList });
            }
            console.log('#161 ', { inputGeb: inputGeb.html() });
            inputGeb
                .on('change', function() {
                    fitEtagenAutocomplete();
                });
            console.log('#166 ', { inputGeb: inputGeb.html() });
        });

        $.get('/api/admin/inventuren/' + jobid + '/etagenGroupedByGid', function(data) {
            console.log('#171 ', { inputGeb: inputGeb.html() });
            etagenList = data.rows;
            fitEtagenAutocomplete();
        });

        return modal.appendTo( target )
            .modal({
            allowMultiple: true,
            onApprove: function() {
                console.log('#34 raum.insert onApprove');
                var fd = new FormData( form.get(0) );
                var formData = [];
                var namedData = {};
                var entries = fd.entries();
                for(var e of entries) {
                    var name = e[0], value = e[1];
                    formData.push({ name, value });
                    namedData[ name ] = value;
                }

                console.log({ fd });
                var checkData = onSubmit(fd, namedData, formData);

                if (checkData instanceof Promise) {
                    checkData
                        .then( function() {
                            raum.insert( namedData, {})
                                .then( function(data) {
                                    modal.modal('hide');
                                    onSuccess(data);
                                })
                                .catch( function() {
                                    console.log('#259 raum.insert catch');
                                    $('#formRaumInsert').form('add errors', {
                                        server: 'Interner Server-Fehler beim Speichern!'
                                    });
                                    onError('Raum konnte nicht angelegt werden!');
                                })
                        })
                        .catch(function() {
                            console.log('#268 raum.insert checkData callback then');
                        });
                } else {
                    console.log('#266 raum.insert');
                    raum.insert(namedData, {})
                        .then( function(data) {
                            console.log('#267 raum.insert then close modal');
                            modal.modal('hide');
                            onSuccess(data);
                        })
                        .catch( function() {
                            console.log('#272 raum.insert catch');
                            $('#formRaumInsert').form('add errors', {
                                server: 'Interner Server-Fehler beim Speichern!'
                            });
                            onError('Raum konnte nicht angelegt werden!');
                        });
                }
                console.log('#281 raum.insert return false');
                return false;
            },
            onDeny: function() {
                var fd = new FormData( form.get(0) );
                onCancel( fd );
            },
            onHidden: function() { modal.remove() }})
            .modal('show');
    }
</script>
