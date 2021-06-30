@extends('layouts.master')
@section('title', 'Inventar Barcodes')
@section('sidebar')
@endsection
@section('content')
    <style>
        th.col-filter .col-filter-control {
            width:100%;
        }

        .ui.sticky.fixed.quicklinks {
            left: 0;
        }
        .sticky.quicklinks {
            position: fixed;
            top: 100px;
            left:0px;
            width: 35px;
            height: 2.5rem;
            padding-right:.5rem;
            white-space: nowrap;
            overflow: hidden;
            -webkit-transition: 0.3s width ease, 0.5s transform ease;
            -moz-transition: 0.3s width ease, 0.5s transform ease;
            -o-transition: 0.3s width ease, 0.5s transform ease;
            -ms-transition: 0.3s width ease, 0.5s transform ease;
            transition: 0.3s width ease, 0.5s transform ease;
        }

        .sticky.quicklinks:hover {
            width: 120px;
            height: auto;
            transition: 0.3s width ease, 0.5s transform ease;
        }
        .sticky.quicklinks:hover .content.icon {
            display: none;
        }
        .sticky.quicklinks .text,
        .sticky.quicklinks .quicklinks-nav {
            display: none;
        }
        .sticky.quicklinks .quicklinks-nav ul {
            list-style-type: none;
            margin: 0.5rem 0;
            padding: 0.5rem 0;
        }
        .sticky.quicklinks .quicklinks-nav ul li {
            display: block;
        }
        .sticky.quicklinks .quicklinks-nav ul li + li {
            margin-top:.5rem;
        }
        .sticky.quicklinks:hover .text {
            display:inline;
        }
        .sticky.quicklinks:hover .quicklinks-nav {
            display: block;
            font-size: .8em;
            font-weight:normal;
            color: silver;
            padding: .5rem 0;
        }
        .sticky.quicklinks .quicklinks-nav a + a {
            margin-top:.5rem;
        }
        .sticky.quicklinks:hover .quicklinks-nav a {
            display: block;
            color: silver;
        }
        .sticky.quicklinks:hover .quicklinks-nav a:hover,
        .sticky.quicklinks:hover .quicklinks-nav a:active,
        .sticky.quicklinks:hover .quicklinks-nav a:focus {
            color: white;
        }
        .row-bc-box {
            float: Left;
            padding-right: 1rem;
        }
        .row-text-box {
            display: inline;
        }
        .row-img {
            float: right;
            max-width: 25%;
            max-height: 4.5rem;
            margin-left: 1rem;
        }
    </style>
    <style media="print">
        thead .col-filters,
        thead th.col-nr,
        thead th.col-numInventar,
        tbody th[scope=row],
        tbody td.col-numInventar,
        .not-printable {
            display:none;
        }
    </style>
    <style>
        .fit-print thead .col-filters,
        .fit-print thead th.col-nr,
        .fit-print thead th.col-numInventar,
        .fit-print tbody th[scope=row],
        .fit-print tbody td.col-numInventar {
            display:none;
        }
    </style>
    <script src="/assets/jslibrary/myBC128.js"></script>
    <script src="/assets/lindellBarcodes/JsBarcode.code128.min.js"></script>

    <a name="Inventar" class="not-printable transition hidden"></a>
    <table id="InventarCodes" class="objectbook-table ui green celled padded table not-printable"></table>

    <a name="BCGenerator"></a>
    <div id="BarcodeGeneratorBox" class="not-printable transition hidden">
        <button id="btnNewInvBC">Barcode generieren</button>
        <input type="text" id="txtNewInvBc"><br>
        <input id="anzahl" type="number" value="20"> Anzahl
        <div id="generatedNewInvBC">
            <div data-code="" class="libre bc bc-box" style="margin-top:3rem; margin-right:6rem">
                <span class="bc-128"></span>
                <span class="bc-text"></span>
            </div>
        </div>
    </div>

    <div id="quicklinksBox" class="ui black xbig right attached sticky fixed button quicklinks not-printable" data-context="body">
        <i class="content icon"></i>
        <span class="text">Tabellen</span>
        <div class="quicklinks-nav">
            <a href="#Inventar">Inventar</a>
            <a href="#BCGenerator">Neue Barcodes</a>
        </div>
    </div>
    <script>

        var lindellBC128Config = {
            "format":"CODE128",
            "background":"#FFFFFF",
            "lineColor":"#000000",
            "fontSize":14,
            "height":35,
            "width":"2",
            "margin":0,
            "textMargin":0,
            "displayValue":true,
            "font":"monospace",
            "fontOptions":"", // bold
            "textAlign":"center",
            "valid":function(valid){
                if(!valid){
                    alert('Invalid Barcode! arguments.length: ' + argument.length);
                }
                else{
                    // To Anything
                }
            }
        };
        var barcodeRenderer = 'lindell'; // 'libre' /* google-font */, 'hogi' /* font */, 'lindell' /* svg */

        var maxInvBC = 0;
        var bcTplCloned = null;

        $('#btnNewInvBC').off('click').on('click', function(e) {
            var inp = $("#txtNewInvBc").val();
            if (!inp) {
                if (!maxInvBC) {
                    var idx = invConf.colNames.indexOf('inventarBarcode');
                    if (idx === -1) {
                        return;
                    }
                    for(var i = 0; i < invConf.data.length; i++) {
                        if (+invConf.data[i][idx] > maxInvBC ) {
                            maxInvBC = +invConf.data[i][idx];
                        }
                    }
                }
                maxInvBC = +maxInvBC + 1;
                inp = maxInvBC.toString(10);
            }

            var fill = function(inp, stellen) {
                if (inp.length < stellen) {
                    do {
                        inp = '0' + inp;
                    } while (inp.length < stellen)
                }
                return inp;
            };

            var bc = fill(inp, 9);
            var anzahl = !isNaN(bc) ? parseInt($('#anzahl').val(), 10) : 1;

            if (!bcTplCloned) {
                bcTplCloned = $('#generatedNewInvBC .bc-box:first').clone();
            }
            $('#generatedNewInvBC').html('');

            for (var i = 0; i < anzahl; i++ ) {
                bc = fill(bc, 9);
                switch(barcodeRenderer) {
                    case 'lindell':
                        var LBCId = 'NEUIV' + i;
                        $('#generatedNewInvBC').append(
                            $("<div/>").addClass('bc bc-box').css({marginTop:'3rem', marginRight:'6rem'}).data('code', bc).append(
                                $('<svg id="' + LBCId + '"></svg>')
                            )
                        );
                        $('#generatedNewInvBC').find("#" + LBCId).JsBarcode(bc, lindellBC128Config);
                        console.log({i, bc, LBCId, lindellBC128Config});
                        break;

                    default:
                        var bc128 = bc128b.get(bc);
                        var bcBox = bcTplCloned.clone();
                        $('#generatedNewInvBC').append( bcBox );
                        bcBox.attr('data-code', bc).data('code', bc);
                        bcBox.find('.bc-128').text( bc128 );
                        bcBox.find('.bc-text').text( bc );
                }

                bc = (parseInt(bc, 10) + 1).toString(10);
            }
            if (0 && barcodeRenderer === 'lindell') {
                $('#generatedNewInvBC').find('.bc-box').each( function() {
                    var svgId = $(this).find('svg').attr('id');
                    var code = $(this).data('code');
                    $('#' + svgId).JsBarcode(code, lindellBC128Config);
                    console.log({svgId, code, lindellBC128Config});
                });
            }

            /*
                    $('#generatedNewInvBC .bc-box').attr('data-code', bc).data('code', bc);
                    $('#generatedNewInvBC .bc-128').text( bc128 );
                    $('#generatedNewInvBC .bc-text').text( bc );
                    */
        });

        var counter = (function() {
            var count = 0;
            return { nextId: function() { return ++count; }};
        })();

        var invConf = {
            key: 'ivid',
            rownumbers: true,
            colfilters: true,
            fields: {
                RaumText: {
                    name: 'Raum',
                    colspan: 2, // colspan: 4,
                    formatter: function(val, colname, rowElm, rowData) {
                        var bc = rowData.raumBarcode;
                        var text = rowData.RaumText;
                        $( this ).html(bc + "<br>" + text);
                    }
                },
                raumBarcode: {
                    name: 'RaumCode',
                    colspan: 0
                },
                ArtikelText: {
                    name: 'Artikel',
                    colspan: 2,
                    formatter: function(val, colname, rowElm, rowData) {
                        var bc = rowData.artikelBarcode;
                        var text = rowData.ArtikelText;
                        $(this).html(bc + "<br>" + text);
                    }
                },
                artikelBarcode: {
                    name: 'Artikel-Code',
                    colspan: 0
                },
                inventarBarcode: {
                    name: 'Inventar-Code',
                    colspan: 2,
                    formatter: function(val, colname, rowElm, rowData) {
                        var bc = rowData.inventarBarcode;
                        var minBcLength = 9;
                        if (bc && bc.length < minBcLength) {
                            do {
                                bc = '0' + bc;
                            }
                            while(bc.length < minBcLength);
                        }
                        var LBCId = 'LBCi' + rowData.ivid;

                        switch(barcodeRenderer) {
                            case 'lindell':
                                $( this )
                                    .html('')
                                    .append( $("<div/>").append( $('<svg id="' + LBCId + '"></svg>') ) );
                                $('#' + LBCId).JsBarcode(bc, lindellBC128Config );
                                break;

                            case 'hogi':
                            case 'libre':
                            default:
                                $( this )
                                    .html('')
                                    .append(
                                        $("<div/>").attr("data-code", bc).addClass('libre bc')
                                            .append( $("<span/>").addClass("bc-128").text( bc128b.get(bc) ) )
                                            .append( $("<span/>").addClass("bc-text").text(bc) )
                                    )
                                ;
                                break;
                        }
                    }
                },
                ivid: { colspan: 0 }
                /*
                Raum: {
                    name: 'Raum',
                    colspan: 2, // colspan: 4,
                    formatter: function(val, colname, rowElm, rowData) {
                        var bc = rowData.raumBarcode;
                        var text = rowData.Gebaeude;

                        if ( rowData.Etage) {
                            text += ', Etage: ' + rowData.Etage;
                        }
                        if ( rowData.Raum) {
                            text += ', ' + rowData.Raum;
                        }
                        if ( rowData.Raumbezeichnung ) {
                            text += ', ' + rowData.Raumbezeichnung;
                        }

                        $( this )
                            .html('')
                            .append(
                                $("<div/>").attr("data-code", bc).addClass('libre bc')
                                    .append( $("<span/>").addClass("bc-128").text( bc128b.get(bc) ) )
                                    .append( $("<span/>").addClass("bc-text").text(bc) )
                            )
                            .append( $("<div/>").text( text ).css({ marginTop: '1rem'} ));
                    }
                },
                Gebaeude: { colspan: 0 },
                Etage: { colspan: 0 },
                Raumbezeichnung: { colspan: 0 },

                Bezeichnung: {
                    name: 'Bezeichnung',
                    colspan: 6,
                    formatter: function(val, colname, rowElm, rowData) {
                        var bc = rowData.artikelBarcode;
                        var text = rowData.Bezeichnung;
                        if ( rowData.Typ ) {
                            text += ', T: ' + rowData.Typ;
                        }
                        if ( rowData.Kategorie) {
                            text += ', K: ' + rowData.Kategorie;
                        }
                        if ( rowData.Gruppe) {
                            text += ', G: ' + rowData.Gruppe;
                        }
                        if ( rowData.Farbe ) {
                            text += ', F: ' + rowData.Farbe;
                        }
                        if ( rowData.Groesse ) {
                            text += ', ' + rowData.Groesse;
                        }

                        $( this )
                            .html('')
                            .append(
                                $("<div/>").attr("data-code", bc).addClass('libre bc')
                                    .append( $("<span/>").addClass("bc-128").text( bc128b.get(bc) ) )
                                    .append( $("<span/>").addClass("bc-text").text(bc) )
                            )
                            .append( $("<div/>").text( text ).css({ marginTop: '1rem'} ));
                    }
                },
                Typ: { colspan: 0 },
                Kategorie: { colspan: 0 },
                Gruppe: { colspan: 0 },
                Farbe: { colspan: 0 },
                Groesse: { colspan: 0 },

                inventarBarcode: {
                    name: 'inventarBarcode',
                    colspan: 2,
                    formatter: function(val, colname, rowElm, rowData) {
                        var bc = rowData.inventarBarcode;
                        $( this )
                            .html('')
                            .append(
                                $("<div/>").attr("data-code", bc).addClass('libre bc')
                                    .append( $("<span/>").addClass("bc-128").text( bc128b.get(bc) ) )
                                    .append( $("<span/>").addClass("bc-text").text(bc) )
                            )
                            ;
                    }
                },
                ivid: { colspan: 0 }
                */
            }
        };
        // okm.mcid, okg.gcid, CONCAT_WS("-", "IN", okm.mcid, okg.code) Barcode, okg.code, '
        //                . ' okg.Bezeichnung, okg.Typ, okg.Kategorie, okg.Farbe, okg.Groesse, okg.Bild, count(1) numInventar'
    </script>
    <!-- script src="/assets/jslibrary/myDataTable.booksIndex.js"></script -->
    <script xsrc="/assets/jslibrary/myDataTable.nojquery.js"></script>
    <script src="/assets/jslibrary/myDataTable.js"></script>
    <script>
    function showInventar() {
        $("#InventarCodes").removeClass('transition hidden').waitMe();
        $("#InventarLink").removeClass('transition').removeClass('hidden');
        $.get('/api/admin/objektbuch/<?= $jobid ?>/listInventarBarcodes', function(data) {
            invConf.colIndex = 'NUM';
            invConf.colNames = data.cols;
            invConf.data = data.rows;
            invConf.title = 'Inventar';
            invConf.pagesize = 50;

            $("#InventarCodes").myDataTable(invConf);
            $("#InventarCodes").waitMe('hide');
        });
    }

    function showBarcodeGenerator() {
        $(function() {
            $("#BarcodeGeneratorBox").removeClass('transition').removeClass('hidden').appendTo("body");
        });
    }

    $(function() {

        $("#quicklinksBox").appendTo("body");

        showInventar();
        showBarcodeGenerator();
    });

    </script>
@endsection
