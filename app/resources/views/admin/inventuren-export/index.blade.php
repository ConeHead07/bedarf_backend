<?php
/**
 * Created by PhpStorm.
 * User: f.barthold
 * Date: 10.11.2020
 * Time: 13:22
 */

$tplVars = (object)$tplVars;
?>
<style>
    .segment-overflow-x {
        overflow-x: auto;
    }
    .checkbox.toggle {
        border: 1px solid #dedcdc;
        padding: 0.5em 1.5em;
        border-radius: 4px;
    }
    #btnShowAllData.checkbox.toggle label {
        color: #bdbbbb !important;
    }
    .checkbox.toggle.active {
        background-color: #c0e6ff;
    }
    #btnShowAllData.checkbox.toggle.active label {
        color: #2185d0!important;
    }

</style>
<link rel="stylesheet" href="/assets/fomantic-ui/components/calendar.css"/>
<script src="/assets/fomantic-ui/components/calendar.js"></script>

<div class="ui card w-100 mt-3">
    <div class="content">
        <div class="header">Bestandsdaten exportieren</div>
    </div>
    <div class="content">
        <p>
            Exportiere alle Daten: Bestandsdaten mit erfassten Änderungen durch die Inventur.
        </p>
        <button class="ui active button primary" data-jobid="<?= $tplVars->jobid ?>"
                data-exporthref="<?= $tplVars->exportHref ?>" id="btnBestandsExport">
            <i class="download icon"></i>
            Exportiere alle Inventar-/ Bestandsdaten
        </button>
    </div>
</div>

<div class="ui card w-100 mt-3">
    <div class="content">
        <div class="header">Inventurdaten exportieren (Scandaten)</div>
    </div>

    <div class="content">
        <p>
            Exportiere nur Inventar-Daten, die während der Inventur durch Scannen erfasst wurden.
        </p>

        <button class="ui active button primary" data-jobid="<?= $tplVars->jobid ?>"
                data-exporthref="<?= $tplVars->exportHref ?>" id="btnInventurExport">
            <i class="download icon"></i>
            Exportiere aktualisierte Daten
        </button>

        <div class="ui form mt-3">
            <div class="two fields">
                <div class="field">
                    <label>Startdatum</label>
                    <div class="ui calendar" id="rangestart">
                        <div class="ui input left icon">
                            <i class="calendar icon"></i>
                            <input type="text" name="exportstartdate" id="exportstartdate" placeholder="Startdatum">
                        </div>
                    </div>
                </div>
                <div class="field">
                    <label>Datum - bis einschließlich</label>
                    <div class="ui calendar" id="rangeend">
                        <div class="ui input left icon">
                            <i class="calendar icon"></i>
                            <input type="text" name="exportenddate" id="exportenddate" placeholder="Enddatum">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="extra content">


        <div id="btnShowAllData" class="ui toggle checkbox transition hidden">
            <input type="checkbox" readonly name="showAllData" id="showAllData">
            <label>Zeige Alle</label>
        </div>



<?= 1 ? '' : '<pre>' . print_r($tplVars, 1) . '</pre>' ?>

    <h4 class="mb-0">Vorschautabellen für aktualisierte Inventurdaten</h4>
    <div id="exportTablesContainer">
        <div id="tabularExportTables" class="ui top attached tabular menu">
            <a class="item" data-tab="exp-raeume" data-dataUrl="">Raeume</a>
            <a class="item" data-tab="exp-inventar">Inventar</a>
            <a class="item" data-tab="exp-raeume-images" data-dataUrl="">Raeume-Img</a>
            <a class="item" data-tab="exp-inventar-images">Inventar-Img</a>
            <span class="item"><button id="btnRefreshTableView" class="ui blue circular sync alternate icon button"><i class="sync alternate icon"></i></button></span>
            <span class="item"><button id="btnEditTableCols" class="ui blue circular sync alternate icon button"><i class="th icon"></i></button></span>
        </div>
        <div class="ui mini modal" id="tableColConfig">
            <div class="header">
                Tabellenfelder
                <div class="ui mini input">
                    <input type="text" name="filter" placeholder="Search...">
                </div>
            </div>
            <div class="scrolling content" style="min-height: calc(70vh - 10rem);">

            </div>
            <div class="actions">
                <div class="ui approve button">Übernehmen</div>
                <div class="ui cancel button">Abbrechen</div>
            </div>
        </div>
        <div class="ui bottom attached tab segment segment-overflow-x" id="tableRaeumeOuter" data-tab="exp-raeume">
            <div class="ui dimmer"><div class="ui loader"></div></div>
            <table id="exportRaeume" class="ui red celled unstackable padded table"></table>
        </div>
        <div class="ui bottom attached tab segment segment-overflow-x" id="tableInventarOuter" data-tab="exp-inventar">
            <div class="ui dimmer"><div class="ui loader"></div></div>
            <table id="exportInventar" class="ui red celled unstackable padded table"></table>
        </div>
        <div class="ui bottom attached tab segment segment-overflow-x" id="tableRaeumeImagesOuter" data-tab="exp-raeume-images">
            <div class="ui dimmer"><div class="ui loader"></div></div>
            <table id="exportRaeumeImages" class="ui red celled unstackable padded table"></table>
        </div>
        <div class="ui bottom attached tab segment segment-overflow-x" id="tableInventarImagesOuter" data-tab="exp-inventar-images">
            <div class="ui dimmer"><div class="ui loader"></div></div>
            <table id="exportInventarImages" class="ui red celled unstackable padded table"></table>
        </div>
    </div>
    </div>
</div>
<script>

$(function() {
    function refreshExportTables() {
        console.log('refreshExportTables() #67');
        aTabsLoaded = {};
        $("#tabularExportTables.menu .item.active").trigger('click');
        console.log('refreshExportTables() #70');
    }

    var formatDateAsIso = function(d) {
        if (d !== null && d instanceof Date) {
            return d.toISOString().substr(0, 10);
        }
        return '';
    };

    var aTabsLoaded = {};
    var aTableFields = {};

    var exportDataDefaults = {
        showAllFields: true,
        colNames: [],
        rows: [],
    };

    $('#btnBestandsExport').on('click', function() {
        console.log('#btnInventurExport.on(click) #59');
        var jobid = $(this).data('jobid');
        var href = $(this).data('exporthref');
        // var showAll = $('#showAllData').prop('checked') ? 1 : 0;
        href+= '?showAll=1';

        console.log('#btnBestandsExport #178 href=', href);
        window.top.location.href = href;
    });

    $('#btnInventurExport').on('click', function() {
        console.log('#btnInventurExport.on(click) #59');
        var href = $(this).data('exporthref');
        var jobid = $(this).data('jobid');
        var start = formatDateAsIso($("#rangestart").calendar('get date'));
        var ende = formatDateAsIso($("#rangeend").calendar('get date'));
        var fd = new FormData();
        fd.append("jobid", jobid);
        fd.append("showAll", 0);
        fd.append("showRange", 1);
        fd.append("start", start);
        fd.append("ende", ende);
        var queryString = new URLSearchParams( fd ).toString();
        // var jobid = $(this).data('jobid');
        var href = $(this).data('exporthref');
        // var showAll = $('#showAllData').prop('checked') ? 1 : 0;
        href+= '?' + queryString;

        console.log('#btnInventurExport #200 href=', href);
        window.top.location.href = href;
    });

    $('#rangestart').calendar({
        type: 'date',
        endCalendar: $('#rangeend'),
        onChange: refreshExportTables
    });
    $('#rangeend').calendar({
        type: 'date',
        startCalendar: $('#rangestart'),
        onChange: refreshExportTables
    });

    $('#tableColConfig').find('input[name=filter]').on("input", function() {
        var term = $(this).val().toString().toLowerCase();
        if (!term) {
            $("#tableColConfig .content div.checkbox").show();
            return;
        }
        $("#tableColConfig .content div.checkbox").each( function() {
            var colName = $(this).attr("data-col").toString().toLowerCase();
            $(this).toggle(colName.indexOf(term) !== -1);
        });
    });

    function editTablTableCols() {
        var activeTab = $("#exportTablesContainer .tabular.menu .item.active");
        var tabPath = activeTab.attr('data-tab');
        var activeTbl = $("#exportTablesContainer .tab[data-tab="+tabPath+"] table");
        console.log('#120 activeTbl.length:', activeTbl.length, { activeTab, tabPath, activeTbl });
        var tblFields = activeTbl.myDataTable('getFields');

        var modal = $('#tableColConfig');
        var header = modal.find('.header');
        var content = modal.find('.content').html('');

        $('#tableColConfig').find('input[name=filter]').val('');

        for(var fld in tblFields) {
            if (!tblFields.hasOwnProperty(fld)) continue;

            var visible = !('hidden' in tblFields[fld]) || !tblFields[fld].hidden;
            var name = tblFields[fld].name;
            var id = activeTbl.attr('id')+'conf'+name+'visible'
            $("<div/>")
                .addClass("ui checkbox col-" + name)
                .css({display: 'block', borderBottom: "1px solid rgba(34,36,38,0.15)", padding:"0.3rem 0"})
                .attr('data-col', name)
                .append( $("<input/>").attr({type:"checkbox", name}).data('oldValIsVisible', visible).prop('checked', visible) )
                .append( $("<label/>").text(name) )
                .appendTo( content );
        }

        modal.modal({
            closeable: true,
            onDeny    : function(){
                console.log('OK!');
                return true;
            },
            onApprove : function() {
                var aShowFields = [];
                var aHideFields = [];
                var numVisibleFields = 0;
                content.find(':checkbox').each(function() {
                    var oldValIsVisible = $(this).data('oldValIsVisible');
                    if (this.checked) {
                        numVisibleFields++;
                        if (!oldValIsVisible) aShowFields.push(this.name);
                    } else {
                        if (oldValIsVisible) aHideFields.push(this.name);
                    }
                });
                if (!numVisibleFields) {
                    alert('Änderungen werden nicht übernommen. Mindestens ein Feld muss sichtbar sein!');
                    return;
                }
                if (!activeTbl || !activeTbl.length || !activeTbl.is("table")) {
                    console.error('activeTbl is not valid!');
                    return;
                }
                console.log({activeTbl});

                if (aShowFields.length) {
                    activeTbl.myDataTable('showFields', aShowFields);
                }
                if (aHideFields.length) {
                    activeTbl.myDataTable('hideFields', aHideFields);
                }
            }
        }).modal('show');
    }

    $('#showAllData').on('change', function(e) {
        console.log('changek #showAllData #79');
        e.preventDefault();
        $('#btnShowAllData').toggleClass('active', this.checked);
        console.log('click #showAllData AND refreshExportTables #267');
        refreshExportTables();
    });

    $("#exportstartdate, #exportenddate").on("change", function(e) {
        console.log('click #changereange AND refreshExportTables #272');
        refreshExportTables();
    });
    var onTabPathLoad = true ? function(){} : function(tabPath, params, historyEvent) {
        if (1) {
            console.log('onLoad', { tabPath, params, historyEvent, arguments });
        }
        switch(tabPath) {
            case 'exp-raeume':
                $.get('/api/admin/inventuren/' + jobid + '/export/raeume', function(response) {
                    $('#exportRaeume').myDataTable({
                        showAllFields: true,
                        colIndex: 'NUM',
                        pagesize: 20,
                        colNames: response.cols || [],
                        data: response.rows
                    });
                });
                break;

            case 'exp-inventar':
                $.get('/api/admin/inventuren/' + jobid + '/export/inventar', function(response) {
                    var conf = {
                        // showAllFields: true,
                        colIndex: 'NUM',
                        pagesize: 20,
                        colNames: response.cols || [],
                        data: response.rows,
                        fields: {}
                    };
                    var aHideFields = [];
                    for (const col of response.cols) {
                        switch(col) {
                            case 'mandant':
                            case 'gebaeude':
                            case 'raum_nr':
                            case 'raum_bez':
                            case 'iv_nr':
                            case 'iv_text':
                            case 'typ':
                            case 'kategorie':
                            case 'lieferant':
                            case 'zustand':
                            case 'datum':
                            case 'memo9':
                            case 'aend_stamp':
                                conf.fields[conf] = {
                                    hidden: false
                                }
                                break;
                            default:
                                aHideFields.push(col);
                                conf.fields[conf] = {
                                    hidden: true
                                }
                        }
                    }
                    console.log('exp-inventar #360 ', { conf, aHideFields});
                    alert(aHideFields.join(' '));
                    $('#exportInventar').myDataTable(conf)
                    .myDataTable('hideFields', aHideFields );
                });
                break;

            case 'exp-raeume-images':
                $.get('/api/admin/inventuren/' + jobid + '/export/raeumeImages', function(response) {
                    $('#exportRaeumeImages').myDataTable({
                        showAllFields: true,
                        colIndex: 'NUM',
                        pagesize: 20,
                        colNames: response.cols || [],
                        data: response.rows
                    });
                });
                break;

            case 'exp-inventar-images':
                $.get('/api/admin/inventuren/' + jobid + '/export/inventarImages', function(response) {
                    $('#exportInventarImages').myDataTable({
                        showAllFields: true,
                        colIndex: 'NUM',
                        pagesize: 20,
                        colNames: response.cols.splice(1, 0, 'Bild') || [],
                        data: response.rows,
                        fields: {
                            Bild: {
                                formatter: function( val, col, row, d) {
                                    this.html(
                                        '<img src="/api/admin/image/'+d.id+'/small" style="max-width:100px;max-height:100px">'
                                    );
                                }
                            }
                        }
                    });
                });
                break;
        }
    };
    var destroyMyDataTable = function(selector) {
        var elm = $(selector);
        if (elm.length === 0) {
            return false;
        }
        var d = elm.data();
        var aDataProps = [ 'data', 'myDataTable', 'dataTableRenderer', 'dataTableRendered' ];
        for (var i = 0; i < aDataProps.length; i++) {
            var _pName = aDataProps[i];
            if (_pName in d) {
                delete d[_pName];
            }
        }
        elm.html("");
    };

    $('.menu .item').tab({
        onLoad: function(tabPath, params, historyEvent) {
            console.log('#149 load tab', tabPath );
            if (1) {
                console.log('#151 onLoad', { tabPath, params, historyEvent, arguments });
            }
            var data = {
                showAll: +document.getElementById('showAllData').checked,
                start: formatDateAsIso($("#rangestart").calendar('get date')),
                ende: formatDateAsIso($("#rangeend").calendar('get date'))
            };

            if (tabPath in aTabsLoaded && aTabsLoaded[tabPath].showAll === data.showAll) {
                return;
            }
            switch(tabPath) {
                case 'exp-raeume':
                    $("#tableRaeumeOuter").dimmer("show");
                    $.get('/api/admin/inventuren/' + jobid + '/export/raeume', data, function(response) {
                        var $tbl = $('#exportRaeume');
                        destroyMyDataTable('#exportRaeume');
                        // $tbl.myDataTable('destroy');
                        var aHideFields = [];
                        if (response.cols.indexOf('raum_nr') > -1) {
                            for (const col of response.cols) {
                                switch (col) {
                                    case 'mandant':
                                    case 'gebaeude':
                                    case 'raum_nr':
                                    case 'raum_bez':
                                    case 'etage':
                                    case 'raum_memo9':
                                    case 'aend_stamp':
                                    case 'NumInventar':
                                        break;
                                    default:
                                        aHideFields.push(col);
                                }
                            }
                        }
                        $tbl.myDataTable({
                            showAllFields: true,
                            colIndex: 'NUM',
                            pagesize: 50,
                            colNames: response.cols || [],
                            data: response.rows
                        }).myDataTable('hideFields', aHideFields);
                        aTabsLoaded[tabPath] = Object.assign(data);
                        $("#tableRaeumeOuter").dimmer("hide");
                    });
                    break;

                case 'exp-inventar':
                    $("#tableInventarOuter").dimmer("show");
                    $.get('/api/admin/inventuren/' + jobid + '/export/inventar', data, function(response) {
                        destroyMyDataTable('#exportInventar');
                        var conf = {
                            showAllFields: true,
                            colIndex: 'NUM',
                            pagesize: 50,
                            colNames: response.cols || [],
                            data: response.rows
                        };
                        var aHideFields = [];
                        if (response.cols.indexOf('iv_text') > -1) {
                            for (const col of response.cols) {
                                switch (col) {
                                    case 'mandant':
                                    case 'gebaeude':
                                    case 'raum_nr':
                                    case 'raum_bez':
                                    case 'iv_nr':
                                    case 'iv_text':
                                    case 'typ':
                                    case 'kategorie':
                                    case 'lieferant':
                                    case 'zustand':
                                    case 'datum':
                                    case 'memo9':
                                    case 'aend_stamp':
                                        break;
                                    default:
                                        aHideFields.push(col);
                                }
                            }
                        }
                        console.log('exp-inventar #360 ', { conf, aHideFields});
                        $('#exportInventar').myDataTable('destroy').myDataTable(conf).myDataTable('hideFields', aHideFields);
                        aTabsLoaded[tabPath] = Object.assign(data);
                        $("#tableInventarOuter").dimmer("hide");
                    });
                    break;

                case 'exp-raeume-images':
                    $("#tableRaeumeImagesOuter").dimmer("show");
                    $.get('/api/admin/inventuren/' + jobid + '/export/raeumeImages', data, function(response) {
                        destroyMyDataTable('#exportRaeumeImages');
                        $('#exportRaeumeImages').myDataTable({
                            showAllFields: true,
                            colIndex: 'NUM',
                            pagesize: 50,
                            colNames: response.cols || [],
                            data: response.rows,
                            fields: {
                                Bild: {
                                    formatter: function( val, col, row, d) {
                                        this.html(
                                            '<img src="/api/admin/image/'+d.id+'/small" style="max-width:100px;max-height:100px">'
                                        );
                                    }
                                }
                            }
                        });
                        aTabsLoaded[tabPath] = Object.assign(data);
                        $("#tableRaeumeImagesOuter").dimmer("hide");
                    });
                    break;

                case 'exp-inventar-images':
                    $("#tableInventarImagesOuter").dimmer("show");
                    $.get('/api/admin/inventuren/' + jobid + '/export/inventarImages', data, function(response) {
                        destroyMyDataTable('#exportInventarImages');
                        $('#exportInventarImages').myDataTable({
                            showAllFields: true,
                            colIndex: 'NUM',
                            pagesize: 50,
                            colNames: response.cols || [],
                            data: response.rows,
                            fields: {
                                Bild: {
                                    formatter: function( val, col, row, d) {
                                        this.html(
                                            '<img src="/api/admin/image/'+d.id+'/small" style="max-width:100px;max-height:100px">'
                                        );
                                    }
                                }
                            }
                        });
                        aTabsLoaded[tabPath] = Object.assign(data);
                        $("#tableInventarImagesOuter").dimmer("hide");
                    });
                    break;
            }
        },
        onFirstLoadX: function(tabPath, params, historyEvent) {
            console.log('firstload tab', tabPath );
        },
        onRequest: function(tabPath) {
            console.log('request tab', tabPath );
        },
        onVisible: function(tabPath) {
            console.log('visible tab', tabPath);
        }
    });

    $("#btnRefreshTableView").on("click", refreshExportTables);
    $("#btnEditTableCols").on("click", editTablTableCols);
});
</script>



