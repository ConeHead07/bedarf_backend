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
<div class="html-templates" id="artikelHtmlTemplates">
    <div class="ui modal modal-artikel-insert">
        <i class="close icon"></i>
        <div class="header">
            Dialog
        </div>
        <div class="content" id="modalArtikelInsertContent">
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
    <form class="ui form form-artikel-insert">
        <h4 class="ui dividing header">Neue Artikeldaten</h4>

        <div class="field">
            <label>Hersteller</label>
            <input type="text" name="Hersteller" list="datalistInputHersteller" xclass="ui fluid dropdown" placeholder="Hersteller">
            <datalist id="datalistInputHersteller"></datalist>
        </div>
        <div class="field">
            <label>Bezeichnung</label>
            <input type="text" name="Bezeichnung" list="datalistBezeichnung" class="ui fluid dropdown" placeholder="Bezeichnung">
            <datalist id="datalistBezeichnung"></datalist>
        </div>
        <div class="field">
            <label>Typ</label>
            <input type="text" name="Typ" list="datalistTyp" placeholder="Typ">
            <datalist id="datalistTyp"></datalist>
        </div>
        <div class="field">
            <label>Gruppe</label>
            <input type="text" name="Gruppe" list="datalistGruppe" placeholder="Gruppe">
            <datalist id="datalistGruppe"></datalist>
        </div>
        <div class="field">
            <label>Kategorie</label>
            <input type="text" name="Kategorie" list="datalistKategorie" placeholder="Kategorie">
            <datalist id="datalistKategorie"></datalist>
        </div>
        <div class="field">
            <label>Farbe</label>
            <input type="text" name="Farbe" list="datalistFarbe" placeholder="Farbe">
            <datalist id="datalistFarbe"></datalist>
        </div>
        <div class="field">
            <label>Groesse</label>
            <input type="text" name="Groesse" list="datalistGroesse" placeholder="Groesse">
            <datalist id="datalistGroesse"></datalist>
        </div>

        <input type="hidden" name="jobid" value="<?= $jobid ?? 0 ?>">

        <div class="ui error message"></div>
    </form>
</div>
<script>

    var artikel = artikel || {};
    artikel = {};
    artikel.jobid = <?= $jobid ?? 0 ?>;
    artikel.modal = null;
    artikel.form = null;

    artikel.formArtikelConfig = {
        fields: {
            Bezeichnung: {
                identifier: 'Bezeichnung',
                rules: [
                    {
                        type   : 'minCount[1]',
                        prompt : 'Bitte gebe ein Gebäude an'
                    }
                ]
            },
            Typ: {
                identifier: 'Typ',
                rules: [
                    {
                        type   : 'empty', // 'minCount[2]',
                        prompt : 'Bitte gebe die Etage an'
                    }
                ]
            },
            Gruppe: {
                identifier: 'Gruppe',
                rules: [
                    {
                        type   : 'minLength[1]',
                        prompt : 'Bitte gebe einen Artikel an'
                    }
                ]
            },
            Farbe: {
                identifier: 'Farbe',
                rules: [
                    {
                        type   : 'empty',
                        prompt : 'Bitte gebe eine Artikelbezeichnung an'
                    }
                ]
            },
            Groesse: {
                identifier: 'Groesse',
                rules: [
                    {
                        type   : 'empty',
                        prompt : 'Bitte gebe eine Artikelbezeichnung an'
                    }
                ]
            },
            Hersteller: {
                identifier: 'Hersteller',
                rules: [
                    {
                        type   : 'empty',
                        prompt : 'Bitte gebe eine Artikelbezeichnung an'
                    }
                ]
            },
            Kategorie: {
                identifier: 'Kategorie',
                rules: [
                    {
                        type   : 'empty',
                        prompt : 'Bitte gebe eine Artikelbezeichnung an'
                    }
                ]
            },
        }
    };

    artikel.formFields = {
        fields: [ 'Hersteller', 'Bezeichnung', 'Typ', 'Gruppe', 'Kategorie', 'Farbe', 'Groesse' ],
        input: {}
    };

    artikel.autocompleteConf = {
        fields: [ 'Bezeichnung', 'Typ', 'Gruppe', 'Kategorie', 'Farbe', 'Groesse' ],
        input: {}
    };

    artikel.insert = function(data, opt) {
        console.log('#105 artikel.insert', { data, opt });
        var args = arguments;
        return new Promise(function(resolve, reject)
        {
            console.log('#109 artikel.insert new Promise');
            $('#formArtikelInsert').form('validate form');
            if (!$('#formArtikelInsert').form('is valid')) {
                console.log('#159');
                return false;
            }
            console.log('Save new Artikel-data', {args});
            $.post(
                '/api/admin/artikel/insert',
                data,
                function(response) {
                    if ('success' in response && response.success) {
                        resolve(response.row);
                    } else {
                        reject( response.message || '', response);
                    }
                }
            ).fail(function() {
                console.log('#127 artikel.insert $.post.fail');
                reject('Unbekannter Fehler!', {});
            });
        });
    };

    artikel.loadData = function(data) {
        var form = this.form;
        if (this.jobid && !data.jobid) {
            data.jobid = this.jobid;
        }

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
        return this;
    };

    artikel.herstellerList = [];
    artikel.loadHersteller = function() {
        var self = this;
        return $.get('/api/admin/inventuren/' + this.jobid + '/hersteller')
            .then(function(data) {
                if (data.success && 'rows' in data) {
                    self.herstellerList = data.rows
                }
            });
    };

    artikel.herstellerArtikelList = [];
    artikel.loadHerstellerArtikel = function(hst) {
        var self = this;
        return $.get('/api/admin/inventuren/' + this.jobid + '/hersteller/' + hst + '/baseartikel')
            .then(function(data) {
                console.log('#239 artikel.loadHerstellerArtikel then(data)', { data });
                if (data.success && 'rows' in data) {
                    self.herstellerArtikelList = data.rows;
                    console.log('#242 artikel.loadHerstellerArtikel loaded', { rows: data.rows, herstellerArtikelList: self.herstellerArtikelList });
                }
            });
    };

    artikel.getSortedUniqueListByCol = function(rows, colname) {
        if (!Array.isArray(rows)) {
            console.error('#250 getSortedUniqueListByCol() Expected first param `rows` as Array, but getting ', typeof rows, { rows, colname});
        }
        return rows.sort().reduce(function(carry, currRow, idx) {
            var val = currRow[colname] || '';
            if (val && carry.indexOf(val) === -1) {
                carry.push(val);
            }
            return carry;
        }, []);
    };

    artikel.getHerstellerArtikelMatchList = function() {

        var inputFields = Object.values( this.autocompleteConf.input ).map( function(item, idx) {
            return item.input;
        });

        var fieldQuery = {};
        $(inputFields).each(function() {
            var val = $.trim($(this).val());
            if ( val != '') {
                var fld = $(this).attr('name');
                fieldQuery[ fld ] = val.toString().toLowerCase();
            }
        });

        var matchList = [];
        if (Object.keys(fieldQuery).length === 0) {
            matchList = this.herstellerArtikelList;
        } else {
            for (var i = 0; i < baseartikelList.length; i++) {
                var itm = this.herstellerArtikelList[i];
                var isMatch = true;
                for (var f in fieldQuery) {
                    if (!(f in itm)
                        || itm[f].toString().toLowerCase() !== fieldQuery[ f ]
                    ) {
                        isMatch = false;
                        break;
                    }
                }

                if (isMatch) {
                    matchList.push( itm );
                }
            }
        }
        return matchList;
    };

    artikel.setDatalist = function(datalistElement, listValues) {
        console.log('#302 artikel.setDatalist ', datalistElement, listValues);

        datalistElement.find('option').remove();
        console.log('#305 artikel.setDatalist ');
        for(var i = 0; i < listValues.length; i++) {
            datalistElement.append( $("<option/>").text( listValues[i] ).val(listValues[ i ]) );
        }
        console.log('#309 artikel.setDatalist ', { datalistElement });

        return this;
    };

    artikel.loadDatalists = function() {
        var self = this;
        console.log('#309');

        this.autocompleteConf.fields.forEach(function(fld, idx) {
            var input = self.formFields.input[ fld ];
            var uniqList = self.getSortedUniqueListByCol(self.herstellerArtikelList, input.col);
            console.log('#317 ', { fld, idx, input, herstellerArtikelList: self.herstellerArtikelList, uniqList });

            self.setDatalist(input.datalist, uniqList);
        });

        return self;
    };

    artikel.openInsertDialog = function(data, opts) {
        var self = this;
        $("#modalArtikelInsert.modal-artikel-insert").remove();
        $("#formArtikelInsert.form-artikel-insert").remove();

        var modal = $("#artikelHtmlTemplates .modal-artikel-insert").clone();
        var form = $("#artikelHtmlTemplates .form-artikel-insert").clone();
        var jobid = ('jobid' in data)
            ? data.jobid
            : form.find(':input[name=jobid]').val();

        this.modal = modal;
        this.form = form;
        this.jobid = jobid;

        modal.attr("id", "modalArtikelInsert");
        form.attr("id", "formArtikelInsert").form( this.formArtikelConfig );
        form.find("select").dropdown();

        var o = typeof opts === 'object' ? opts : {};
        var target = o.target || 'body';
        var title = o.title || 'Bild';
        var onSubmit = o.onSubmit || function(){};
        var onSuccess = o.onSuccess || function(){};
        var onError  = o.onError || function(){};
        var onCancel = o.onCancel || function(){};
        var suffix = '_' + Math.random().toString(10).substr(2)

        modal.find('.header').html(title);
        modal.find('#modalArtikelInsertContent').append( form );

        var aFldNames = Object.values(this.formFields.fields);
        for(var inFld of aFldNames) {
            this.formFields.input[ inFld ] = this.formFields.input[ inFld ] || {};
            var obj = this.formFields.input[ inFld ];
            obj.field = obj.field || inFld;
            obj.col = obj.col || inFld;
            obj.selector = obj.selector || ':input[name=' + inFld + ']';
            obj.input = form.find(obj.selector);

            var tempDatalistId = obj.input.attr('list');
            obj.datalist = form.find( 'datalist[id=' + tempDatalistId + ']');

            var newDatalistId = tempDatalistId + suffix;
            obj.input.attr('list', newDatalistId);
            obj.datalist.attr('id', newDatalistId);

            if (inFld === 'Hersteller') {
                console.log('#372 ',
                    {
                        aFldNames,
                        inFld,
                        tempDatalistId,
                        newDatalistId,
                        'obj.input.attr(list)': obj.input.attr('list'),
                        'obj.datalist.attr(id)': obj.datalist.attr('id')
                    }
                );
            }

            if (self.autocompleteConf.fields.indexOf( inFld ) !== -1) {
                self.autocompleteConf.input[ inFld ] = self.autocompleteConf.input[ inFld ];
            }
        }

        this.loadData(data);


        var inputHst = self.formFields.input['Hersteller'].input;
        var datalistElement = self.formFields.input['Hersteller'].datalist;

        if (self.herstellerList && self.herstellerList.length > 0) {
            var list = self.getSortedUniqueListByCol(self.herstellerList, 'Hersteller');
            self.setDatalist(datalistElement, list);
        }

        inputHst.off('change').on('change', function() {
            console.log('_artikel_insert.php #384 this.loadHersteller().then inputHst.on(change,..)');
            var hst = inputHst.val();
            console.log('_artikel_insert.php #386 this.loadHersteller().then inputHst.val', hst);
            self.loadHerstellerArtikel(hst).then(function() {
                console.log('_artikel_insert.php #388 this.loadHersteller().then loaded herstellerArtikel');
                self.loadDatalists();
            });
        });

        this.loadHersteller()
            .then(function() {
                if (!self.herstellerList || !Array.isArray(self.herstellerList)) {
                    console.error('#378 self.herstellerList is not a array!', { self, herstellerList: self.herstellerList });
                }
                var list = self.getSortedUniqueListByCol(self.herstellerList, 'Hersteller');
                console.log('_artikel_insert.php #379 this.loadHersteller().then', { datalistElement, list });
                self.setDatalist(datalistElement, list);

                console.log('_artikel_insert.php #382 this.loadHersteller().then');
                // inputHst.off('change').on('change', function() {
                //     console.log('_artikel_insert.php #384 this.loadHersteller().then inputHst.on(change,..)');
                //     var hst = inputHst.val();
                //     console.log('_artikel_insert.php #386 this.loadHersteller().then inputHst.val', hst);
                //     self.loadHerstellerArtikel(hst).then(function() {
                //         console.log('_artikel_insert.php #388 this.loadHersteller().then loaded herstellerArtikel');
                //         self.loadDatalists();
                //     });
                // });
            });

        return modal.appendTo( target )
            .modal({
                allowMultiple: true,
                onApprove: function() {
                    console.log('#34 artikel.insert onApprove');
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
                                artikel.insert( namedData, {})
                                    .then( function(data) {
                                        modal.modal('hide');
                                        onSuccess(data);
                                    })
                                    .catch( function() {
                                        console.log('#259 artikel.insert catch');
                                        $('#formArtikelInsert').form('add errors', {
                                            server: 'Interner Server-Fehler beim Speichern!'
                                        });
                                        onError('Artikel konnte nicht angelegt werden!');
                                    })
                            })
                            .catch(function() {
                                console.log('#268 artikel.insert checkData callback then');
                            });
                    } else {
                        console.log('#266 artikel.insert');
                        artikel.insert(namedData, {})
                            .then( function(data) {
                                console.log('#267 artikel.insert then close modal');
                                modal.modal('hide');
                                onSuccess(data);
                            })
                            .catch( function() {
                                console.log('#272 artikel.insert catch');
                                $('#formArtikelInsert').form('add errors', {
                                    server: 'Interner Server-Fehler beim Speichern!'
                                });
                                onError('Artikel konnte nicht angelegt werden!');
                            });
                    }
                    console.log('#281 artikel.insert return false');
                    return false;
                },
                onDeny: function() {
                    var fd = new FormData( form.get(0) );
                    onCancel( fd );
                },
                onHidden: function() { modal.remove() }
            })
            .modal('show');
        };

    artikel.loadHersteller();
    // artikel.openInsertDialog([], {});
</script>
