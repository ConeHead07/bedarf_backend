@extends('layouts.master')
@section('title', $title ?? 'Administration')
@section('sidebar')
@endsection
@section('content')
    <link rel="stylesheet" href="/assets/jslibrary/myDataTable.css" />
<script src="/assets/jslibrary/myDataTable.js"></script>
<?php
$mid = $tplVars->mandant['mid'] ?? 0;
?>

<div id="pageMandant">

    <div class="ui top attached tabular menu">
        <div class="item active" style="padding-top:5px; padding-bottom:0" data-tab="basisdaten">
            <div style="display:block">
                Basisdaten<br>
                <i style="color:#888;margin-top:5px;font-size:9px;font-weight:normal;"><?= $tplVars->mandant['Mandant'] ?></i>
            </div>
        </div>
        <a class="item" data-tab="inventuren">Inventuren</a>
        <a class="item" data-tab="lager">Lager</a>
        <a class="item" data-tab="gebaeude">Gebäude</a>
    </div>
    <div class="ui bottom attached tab segment" data-tab="basisdaten">
        <div class="ui form" id="frmInventur">
            <h4 class="ui dividing header">Mandant</h4>
            <div class="field field-mandant">
                <label for="mid">Mandant</label>
                <input type="text" name="mandant" id="Mandant" value="<?= htmlentities($tplVars->mandant['Mandant']) ?>" placeholder="" readonly>
            </div>
        </div>
    </div>

    <div class="ui bottom attached tab segment" data-tab="inventuren" style="max-width:100vw;overflow-x: auto;">
        <table id="tblInventuren" data-type="inventuren" data-url="/api/admin/mandanten/<?= $mid ?>/inventuren" class="ui red celled sortable unstackable padded table"></table>
    </div>

    <div class="ui bottom attached tab segment" data-tab="lager" style="max-width:100vw;overflow-x: auto;">
        Lager
    </div>

    <div class="ui bottom attached tab segment" data-tab="gebaeude" style="max-width:100vw;overflow-x: auto;">
        <table id="tblGebaeude" data-type="gebaeude" data-url="/api/admin/mandanten/<?= $mid ?>/gebaeude" class="ui red celled sortable unstackable padded table"></table>
    </div>
</div>

<script>

    var inventurenConf = {
        data: [],
        key: 'jobid',
        insertable: true,
        onOpenInsert: function() {
            console.log('Mandanten.Inventuren onOpenInsert() #115 arguments', arguments);
        },
        rownumbers: true,
        colfilters: true,
        title: 'Mandanten-Inventuren',
        openDoc: true,
        onOpenDoc: function(row, rowData) {
            self.location.href = '/api/admin/inventuren/' + rowData.jobid;
        },
        fields: {
            jobid: {
                name: 'jobid'
            },
            name: {
                name: 'Angelegt von'
            },
            // mid: { name: 'mid' },
            Mandant: {
                name: 'Mandant',
                hidden: true,
            },
            Titel: {
                name: 'Inventur'
            },
            EnthaeltKunst: {
                name: 'Kunst',
                hidden: true,
                editable: false
            },
            KunstKategorien: {
                name: 'Kunst-Ktg',
                hidden: true,
                editable: false
            },
            NumArtikel: {
                hidden: false
            },
            NumInventar: {
                hidden: true
            },
            NumInventarFound: {
                hidden: false
            },
            NumRaeume: {
                hidden: true
            },
            NumRaeumeFound: {
                hidden: false
            },
            NumGebaeude: {
                hidden: true
            },
            created_at: { name: 'Angelegt am', formatter: function(val, colname, rowElm, rowData) {
                    var d = new Date(val);
                    var s = moment(d).format('DD.MM.YYYY');
                    $(this).html( s );
                } },
            x: { name: 'aktion', formatter: function(val, colname, rowElm, rowData) {
                    $(this).html(
                        '<a href="/api/admin/inventuren/' + rowData.jobid + '">Inventur</a>'
                    )
                } }
        }
    };

    var gebaeudeConf = {
        data: [],
        key: 'gid',
        rownumbers: true,
        colfilters: true,
        insertable: true,
        editable: true,
        deletable: true,
        onOpenInsert: function() {
          console.log('Mandanten.Gebaeude onOpenInsert() #115 arguments', arguments);
        },
        title: 'Mandanten-Gebäude',
        fields: {
            gid: {
                hidden: false
            },
            mid: {
                hidden: false
            },
            Gebaeude: {
                name: 'Gebäude',
                editable: true
            },
            Adresse: {
                name: 'Adresse',
                hidden: true,
                editable: true
            },
            NumInventuren: {
                hidden: false
            },
            created_at: {
                hidden: true
            },
            created_uid: {
                hidden: true
            },
            created_by: {
                hidden: true
            },
            created_jobid: {
                hidden: true
            },
            created_by_inventur: {
                hidden: true
            },
            modified_at: {
                hidden: true
            },
            modified_uid: {
                hidden: true
            },
            modified_by: {
                hidden: true
            }
        }
    };

    var tableConf = {
        inventuren: inventurenConf,
        gebaeude: gebaeudeConf
    };


    $('#pageMandant .menu .item').tab({
        onFirstLoad: function(tabPath, params, historyEvent) {
            if (1) {
                console.log('#1118 pageMandant tab onFirstLoad', { tabPath, params, historyEvent, arguments });
            }
            switch(tabPath) {
                case 'inventuren':
                case 'gebaeude':
                    var tblId = '#tbl' + tabPath.substr(0,1).toUpperCase() + tabPath.substr(1);
                    var $tbl = $(tblId);
                    if (!(tabPath in tableConf)) {
                        alert('Tabellenkonfiguration für ' + tabPath + ' wurde nicht gefunden!');
                        return;
                    }
                    if (!$tbl.length) {
                        alert('Cannot render table ' + tblId + '! Table-Id not found.');
                        return;
                    }
                    var dataUrl = $tbl.data('url');
                    var conf = tableConf[tabPath];
                    console.log('#1122 pageMandant tab raeume load export');
                    $.get(dataUrl, function(response) {
                        conf.data = response.rows;
                        conf.dataUrl = dataUrl;
                        $(tblId).myDataTable( conf );
                    });
                    break;
            }
        },
        onLoad: function(tabPath, params, historyEvent) {
            self.location.hash = tabPath;
            $('#pageMandant .menu').data('active-path', tabPath);
            console.log('#1163 called load tab without request', tabPath );
        },
        onRequest: function(tabPath) {
            console.log('request tab', tabPath );
        },
        onVisible: function(tabPath) {
            console.log('visible tab', tabPath);
        }
    });
</script>
@endsection
