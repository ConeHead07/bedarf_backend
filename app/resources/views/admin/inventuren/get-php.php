<?php include APP_VIEWS_PATH . '/global/partials/htmlhead.php' ?>
<?php
$jobid = $tplVars->jobid ?? 0;
?>
<script>
    var jobid = <?= json_encode($jobid) ?>;
    var initialHash = window.location.hash;

    if (initialHash.substr(0,1) === '#') {
        initialHash = initialHash.substr(1);
    }

    if (initialHash.substr(0,1) === '/') {
        initialHash = initialHash.substr(1);
    }
</script>
<link rel="stylesheet" href="/assets/app/css/uploadControl.css" />
<link rel="stylesheet" href="/assets/jslibrary/myDataTable.css" />
<script src="/assets/jslibrary/myDataTable.js"></script>
<script>

    var confListRaumBilder = {
        params: {
            rid: 0
        },
        key: 'id',
        pagesize: 20,
        title: 'Bild',
        editable: true,
        insertable: true,
        deletable: true,
        fields: {
            id: { name: 'id', formatter: function(val, col, row, data) {
                    console.log('formatter for img_id. Params ', { val, col, row, data });
                    if (val) {
                        var src = '/api/admin/image/' + val;
                        $(this).html('');
                        $("<img/>")
                            .attr('src', src)
                            .css({
                                maxWidth: '100px',
                                maxHeight: '100px'
                            })
                            .on('click', function(e) {
                                getImageModal(src, {
                                    title: data.Bezeichnung, defList: {

                                    } }).modal('show');
                            })
                            .appendTo(this);
                    }
                }},
            name: { name: 'name', editable: true },
            desc: { name: 'desc', editable: true }
        },
        onLoad: null, // callback()
        onEdit: null, // callback(row, rowData)
        onSave: function callbackOnSave(inputData, rowData, inputControls, row) {
            return new Promise(function(resolve, reject) {
                var tbl = $( row ).closest('table');
                var rid = tbl.data('rid');
                var url = '/api/admin/raeume/' + rid + '/imageedit/' + rowData.id;
                var formData = $.fn.myDataTable.lib.getInputAsFormDataBySelector( row );
                var jqXhr = null;
                var getJqXhr = function(jx) {
                    jqXhr = jx;
                };

                var uploadProgressCallback = function(e) {
                    console.log('get.php confListRaumBilder.onSave uploadProgressCallback #60', e);
                };

                var prom = new Promise( function(resolve, reject) {
                    var rand = Math.random();
                    setTimeout(function() { rand > 0.5 ? resolve(rand) : reject(rand + ' is too small'); }, 1000 * rand);
                });
                prom.then().catch();

                $.fn.myDataTable.lib
                    .postFormData(url, formData, getJqXhr, uploadProgressCallback )
                    .then(function(d) {
                        resolve(d);
                    })
                    .catch(function(e) {
                        reject();
                    });
            });
        },
        onInsert: function callbackOnInsert(rowData) {
            return new Promise(function(resolve, reject) {
                resolve({});
            });
        },
        onDelete: function callbackOnInsert(rowData, row) {
            console.log("<?= __FILE__ ?> #<?= __LINE__ ?> confListRaumBilder onDelete", { row, rowData });
            return new Promise(function(resolve, reject) {
                var tbl = $(row).closest('table');
                var rid = tbl.data('rid');
                var url = '/api/admin/raeume/' + rid + '/imageremove/' + rowData.id;
                $.ajax(url, {
                    success: function (data) {
                        resolve(data);
                    },
                    error: function (e) {
                        reject();
                    },
                });
            });
        }
    };

    var confListRaumPlaene = $.extend({}, confListRaumBilder);

    function getImageModal(src, opts) {
        console.log('function getImageModal'); // , {  opts, o });
        var o = typeof opts === 'object' ? opts : {};
        var modal = $("#modalTemplates .my-modal-with-image").clone();
        var target = o.target || 'body';
        var title = o.title || 'Bild';
        var text = o.text || '';
        var defList = o.defList || null;
        if (defList && Object.values(defList).length > 0) {
            var tbody = modal.find("table.my-modal-deflist-table");
            for(var k in defList) {
                var v = defList[k];
                if (!defList.hasOwnProperty(k) || v === null || v === '') continue;

                $("<tr/>").append( $("<td/>").text(k) ).append( $("<td/>").text(v) ).appendTo( tbody );
            }
        } else {
            modal.find("table.my-modal-deflist-table").hide();
        }
        modal.find('.my-modal-title').text( title);

        var imgReg = new Image();
        imgReg.src = src;
        imgReg.onload = function() {
            modal.find("img.my-modal-image").css({
                maxWidth: '100%',
                maxHeight: '90vh',
                border: '2px solid grey'
            }).attr('src', imgReg.src);
        };

        return modal.appendTo( target ).modal({ allowMultiple: true, onHidden: function() { modal.remove() }});
    }

    var raumAttachementsModal = function(rid, opts) {
        var modal = $("#modalTemplates .my-modal-raum").clone();
        var o = typeof opts === 'object' ? opts : {};
        var target = o.target || 'body';
        var title = o.title || 'Raum';
        var text = o.text || '';
        var defList = o.defList || null;

        if (defList && Object.values(defList).length > 0) {
            var tbody = modal.find("table.my-modal-deflist-table");
            for(var k in defList) {
                var v = defList[k];
                if (!defList.hasOwnProperty(k) || v === null || v === '') continue;

                $("<tr/>").append( $("<td/>").text(k) ).append( $("<td/>").text(v) ).appendTo( tbody );
            }
        }

        modal.find('label.lblUpload').each(function() {
            var id = $( this ).attr('for');
            modal.find('#' + id).attr('id', id + '1');
            $(this).attr( 'for', id + '1');
        });

        modal.find('input[type=file][name^=fileRaum]').on( 'change', function() {
            var input = this;
            var name = input.name;
            var readers = [];
            var target = null;
            var category = '';
            var postDataURL = '/api/admin/raeume/' + rid + '/importimage'
            switch(name) {
                case 'fileRaumplaene':
                    target = modal.find('.my-modal-raum-plaene');
                    category = 'Plan';
                    break;

                case 'fileRaumbilder':
                    target = modal.find('.my-modal-raum-bilder');
                    category = 'Bild';
                    break;

                default:
                    return false;
            }

            if (input.files && input.files.length) {
                for (var i = 0; i < input.files.length; i++) {

                    (function(file, url){
                        var row =processRaumFileTable(file, target);
                        var progressElm = $("<div/>")
                            .addClass("ui indicating progress")
                            .append( $("<div/>").addClass("bar").append( $("<div/>").addClass("progress") ) )
                            .append( $("<div/>").addClass("label").text("warte auf Upload") )
                            .appendTo( $( row ).find("td.actions") )
                            .progress({ total: file.size });

                        var formData = new FormData();
                        formData.append('category', category);
                        formData.append('image', file, file.name);

                        $.fn.myDataTable.lib.postFormData(url, formData, null, function uploadProgress(e){
                            var pct = e.loaded * 100 / e.total;
                            progressElm.progress('set progress', e.loaded);
                            progressElm.progress('set label', 'uploading');
                            console.log('get.php #103 uploadProgress, update progress(' + e.loaded + ')', { pct, e });
                        })
                            .then(function(d) {
                                var data = ('data' in d) ? d.data : d;
                                console.log('get.php #390', { data });
                                progressElm.progress('set success');
                                progressElm.progress('set label', 'finished');

                                row.closest('table')
                                    .myDataTable('addRow', data)
                                    .myDataTable('addRenderRow', data);

                                row.remove();
                            })
                            .catch(function() {
                                console.log('get.php #394', { url, formData, arguments });
                                progressElm.progress('set error');
                                progressElm.progress('set label', 'Fehler!');
                            });
                    })(input.files[i], postDataURL);
                }
            }
        });

        modal.find('.my-modal-title').text( title);

        $.get('/api/admin/raeume/' + rid + '/listgroupedimages', function(d) {
            console.log('#203 callback /api/admin/raeume/' + rid + '/listgroupedimages', { d });

            if (d.success) {

                confListRaumPlaene.data = d.data.plaene;
                modal.find('table.my-modal-table-plaene')
                    .myDataTable(confListRaumPlaene)
                    .data('rid', rid);

                confListRaumBilder.data = d.data.bilder;
                modal.find('table.my-modal-table-bilder')
                    .myDataTable(confListRaumBilder)
                    .data('rid', rid);
            }
        });



        return modal.appendTo( target ).modal({onHidden: function() { modal.remove() }});
    };

    var processRaumFileList = function(file, target) {
        var reader = new FileReader();
        var filename = file.name;
        var filesize = file.size;
        reader.addEventListener('load', function() {

            /*
            <div class="ui grid">
                <div class="four wide column"></div>
                <div class="four wide column"></div>
                <div class="four wide column"></div>
                <div class="four wide column"></div>
                </div>
             */
            var itemGroup = $("<div/>").addClass('ui grid').appendTo( target );

            var itemBoxDesc = $("<div/>").addClass('three wide column ui form').appendTo( itemGroup );
            var itemBoxPrev = $("<div/>").addClass('three wide column ui form').appendTo( itemGroup );
            var itemBoxBtns = $("<div/>").addClass('three wide column ui form').appendTo( itemGroup );

            var inputFileDesc = $("<input/>").val(filename).appendTo( itemBoxDesc );
            var inputPreview = $("<img/>")
                .attr('src', reader.result )
                .css({
                    maxWidth: "120px",
                    maxHeight: "120px"
                })
                .appendTo( itemBoxPrev );

        });
        reader.readAsDataURL( file );
    };

    var processRaumFileTable = function(file, target) {
        var reader = new FileReader();
        var filename = file.name;
        var filesize = file.size;

        var tbody = $( target ).find('tbody');
        var tr = $("<tr/>").appendTo( tbody );
        var th = $("<th/>").addClass('actions').appendTo( tr );
        var td1 = $("<td/>").addClass('actions').appendTo( tr );
        var td2 = $("<td/>").addClass('image').appendTo( tr );
        var td3 = $("<td/>").addClass('input desc').appendTo( tr );

        var inputFileDesc = $("<input/>").attr({ name: "desc"}).val(filename).appendTo( td3);

        reader.addEventListener('load', function() {

            $("<img/>")
                .attr('src', reader.result )
                .css({
                    maxWidth: "120px",
                    maxHeight: "120px"
                })
                .appendTo( td2 );

        });
        reader.readAsDataURL( file );
        return tr;
    };

    var showItemDetailsModal = function(opts) {
        var modal = $("#modalTemplates .my-modal-details").clone();
        var o = typeof opts === 'object' ? opts : {};
        var target = o.target || 'body';
        var title = o.title || 'Raum';
        var text = o.text || '';
        var defList = o.defList || null;

        if (defList && Object.values(defList).length > 0) {
            var tbody = modal.find("table.my-modal-deflist-table");
            for(var k in defList) {
                var v = defList[k];
                if (!defList.hasOwnProperty(k) || v === null || v === '') continue;

                $("<tr/>").append( $("<td/>").text(k) ).append( $("<td/>").text(v) ).appendTo( tbody );
            }
        }

        if (opts.image && (typeof opts.image === 'string') && opts.image.length > 0) {
            var imgReg = new Image();
            imgReg.src = opts.image;
            imgReg.onload = function() {
                modal.find("img.my-modal-image").css({
                    maxWidth: '100%',
                    maxHeight: '90vh',
                    border: '2px solid grey'
                }).attr('src', imgReg.src);
            };
        }

        modal.find('.my-modal-title').text( title);

        if (text) {
            modal.find('.my-modal-text:first').prepend($("<h5/>").text(text));
        }

        modal.find('label.lblUpload').each(function() {
            var id = $( this ).attr('for');
            modal.find('#' + id).attr('id', id + '1');
            $(this).attr( 'for', id + '1');
        });

        return modal.appendTo( target ).modal({onHidden: function() { modal.remove() }}).modal('show');
    };

    var aGebList = [];
    var confTblRaeume = {
        key: 'rid',
        pagesize: 20,
        title: 'Räume',
        editable: true,
        insertable: true,
        deletable: true,
        tableSelector: "#tblRaeume",
        fields: {
            Gebaeude: { name: 'Gebaeude', editable: true,
                editor: function(val, name, rowData) {
                    var inputWrapper = this;

                    var hidden = $("<input/>")
                        .attr({
                            type: 'hidden',
                            name: name,
                        })
                        .addClass("myDataTable-td-edit-input")
                        .val( val )
                        .appendTo( inputWrapper );

                    var input = $("<select/>").appendTo( inputWrapper )
                        .attr({ name: 'gid' })
                        .addClass("myDataTable-td-edit-input")
                        .on('change', function() {
                            var i = this.options.selectedIndex;
                            var text = this.options[i].text;
                            hidden.val( text );
                        });

                    aGebList.forEach( function(v, i) {
                        var opt = $("<option>").val(v.gid).text(v.Gebaeude).appendTo(input);
                        if (val == v.gid) {
                            opt.prop('selected', true);
                        }
                    });
                }
            },
            Etage: {name: 'Etage', editable: true, edittype: 'text'},
            Raum: {name: 'Raum', editable: true },
            Raumbezeichnung: {name: 'Bezeichnung', editable: true,
                formatter: function(val, col, row, data) {
                    var raum = (data.Raum || '').toLowerCase();
                    var rbzg = (data.Raumbezeichnung || '').toLowerCase();
                    var zeigeRaum = raum.length > 0 && raum.indexOf(rbzg) > -1;
                    var zeigeRBzg = rbzg.length > 0 && rbzg.indexOf(raum) > -1;
                    var title = zeigeRaum || !zeigeRBzg ? data.Raum : data.Raumbezeichnung;
                    $(this).
                        css({
                            textDecoration: 'underline'
                        })
                        .text( val )
                        .off('click')
                        .on(
                            'click',
                        function(e) {
                            raumAttachementsModal(data.rid, {
                                title: 'Raum ' + title,
                                text: 'Bzg: ' + data.Raumbezeichnung,
                                defList: data
                            }).modal('show');
                        }
                    )
                }
            },
            numInventar: {name: 'numInventar'},
            created_at: {name: 'Erstellt', formatter: function(val, col, data) {
                val = $.fn.myDataTable.formatters['date-dmy'](val);
                $(this).text( val );
            }},
            created_uid: {name: 'Erstellt von'},
            modified_at: {name: 'Aktualisiert', formatter: function(val, col, data) {
                val = $.fn.myDataTable.formatters['date-dmy'](val);
                $(this).text( val );
            }},
            modified_uid: {name: 'Aktualisiert von'}
        },
        onLoad: function() {
            var self = this;
            var d = $(this).myDataTable('getData');
            if (!Array.isArray(d)) {
                return;
            }
            var lastGID = null;
            aGebList = [];
            d.sort(function(a, b) {
                return (a.Gebaeude || '') < (b.Gebaeude || '') ? -1 : 1;
            }).forEach(function(v, i) {
                if (lastGID !== v.gid ) {
                    aGebList.push({gid: v.gid, Gebaeude: v.Gebaeude });
                    lastGID = v.gid;
                }
            });
        },
        onSave: function(inputData, rowData, inputControls, row) {
            console.log('#410 get.php confTblInventar onSave', { inputData, rowData, inputControls, row });

            var formData = new FormData();
            formData.append('id', rowData['rid']);
            formData.append('rid', rowData['rid']);
            formData.append('jobid', jobid);

            for(var k in inputData) {
                if (!inputData.hasOwnProperty(k)) continue;
                if (!(k in rowData) || inputData[k] !== rowData[k]) {
                    formData.append(k, inputData[k]);
                }
            }

            return new Promise(function(resolve, reject) {

                var url = '/api/admin/raeume/update';
                console.log('#426 get.php', { url, formData });
                $.fn.myDataTable.lib
                    // .postFormData(url, formData, function getJqXhr(jqXhr){
                    //     console.log('get.php #107 getJqXhr', { url, formData, jqXhr });
                    // })
                    .postFormData(url, formData)
                    .then(function(d) {
                        console.log('#423 get.php');
                        resolve(d);
                    })
                    .catch(function() {
                        console.log('#437 get.php', { url, formData, arguments });
                        reject('#428 Server-Fehler!');
                    });
            })
        },
        onOpenInsert: function() {
            var self = this;
            return new Promise(function(tblResolve, tblReject) {

                raum.openInsertDialog({jobid}, {
                    title: 'Neuen Raum anlegen',
                    onSubmit: function(form, namedData, formData) {
                        console.log('#448 onOpenInsert onSubmit ', { form, namedData, formData });
                        return new Promise(function(resolve, reject) {
                            console.log('#450 raum.insert resolve namedData', { namedData });
                            resolve(namedData);
                        });
                    },
                    onSuccess: function(data) {
                        return new Promise(function(resolve, reject)
                        {
                            resolve(data);
                            tblResolve(data);
                        });
                    },
                    onError: function(msg) {
                    },
                    onCancel: function() {
                        console.log('Cancel Insert Raum-data', { arguments });
                    }
                });
            });
        }
    };
    var confTblInventar = {
        key: 'ivid',
        pagesize: 20,
        title: 'Inventar',
        colIndex: 'NUM',
        editable: true,
        deletable: true,
        insertable: true,
        fields: {
            Raum: {
                name: 'Raum',
                formatter: function(val, col, row, data) {
                    var rid = data.rid;
                    $(this).html('').append(
                        $("<span/>")
                            .addClass('link')
                            .css({
                                textDecoration: 'underline',
                                cursor: 'pointer'
                            })
                            .text( val )
                            .on(
                                'click',
                                function(e) {
                                    $.get('/api/admin/raeume/' + rid)
                                        .then(function(response) {
                                            if (!response.success || !response.data) {
                                                alert('Fehler. Raumdaten konnten nicht abegrufen werden!');
                                                return;
                                            }
                                            var rspData = response.data;
                                            var raum = (rspData.Raum || '').toLowerCase();
                                            var rbzg = (rspData.Raumbezeichnung || '').toLowerCase();
                                            var zeigeRaum = raum.length > 0 && rbzg.indexOf(raum) === -1;
                                            var zeigeRBzg = rbzg.length > 0 && raum.indexOf(rbzg) === -1;
                                            var title = (zeigeRaum ? rspData.Raum + ' ' : '') +
                                                (zeigeRBzg ? rspData.Raumbezeichnung : '');

                                            raumAttachementsModal(rspData.rid, {
                                                title: 'Raum ' + title,
                                                text: 'Bzg: ' + rspData.Raumbezeichnung,
                                                defList: rspData
                                            }).modal('show');
                                        });
                                }
                            )
                    )
                }
            },
            Bezeichnung: {
                name: 'Artikel',
                formatter: function(val, col, row, data) {
                    var mcid = data.mcid;
                    $(this).html('').append(
                        $("<span/>")
                            .addClass('link')
                            .css({
                                textDecoration: 'underline',
                                cursor: 'pointer'
                            })
                            .text( val )
                            .on(
                                'click',
                                function(e) {
                                    $.get('/api/admin/artikel/' + mcid)
                                        .then(function(response) {
                                            if (!response.success || !response.data) {
                                                alert('Fehler. Artikeldaten konnten nicht abegrufen werden!');
                                                return;
                                            }
                                            var rspData = response.data;

                                            var opts = {
                                                title: val,
                                                defList: rspData,
                                                image: rspData.img_id
                                                ? '/api/admin/image/' + rspData.img_id
                                                    : ''
                                            }
                                            showItemDetailsModal(opts);
                                        });
                                }
                            ));

                }
            },
            Typ: {name: 'Typ'},
            Gruppe: {name: 'Gruppe', editable: true },
            Farbe: {name: 'Farbe', editable: true },
            Groesse: {name: 'Groesse'},
            created_at: {name: 'Erstellt', formatter: function(val, col, data) {
                    val = $.fn.myDataTable.formatters['date-dmy'](val);
                    $(this).text( val );
                }},
            created_uid: {name: 'Erstellt von'},
            modified_at: {name: 'Aktualisiert', formatter: function(val, col, data) {
                    val = $.fn.myDataTable.formatters['date-dmy'](val);
                    $(this).text( val );
                }},
            modified_uid: {name: 'Aktualisiert von'}
        },
        onSave: function(inputData, rowData, inputControls, row) {
            console.log('get.php #149 confTblInventar onSave', { inputData, rowData, inputControls, row });

            var formData = new FormData();
            formData.append('id', rowData['ivid']);
            formData.append('ivid', rowData['ivid']);

            for(var k in inputData) {
                if (!inputData.hasOwnProperty(k)) continue;
                if (!(k in rowData) || inputData[k] !== rowData[k]) {
                    changeData[k] = inputData[k];
                    formData.append(k, inputData[k]);
                }
            }

            return new Promise(function(resolve, reject) {


                var url = '/api/admin/inventar/update';
                console.log('get.php #167 ', { url, formData });
                $.fn.myDataTable.lib
                    .postFormData(url, formData, function getJqXhr(jqXhr){
                        console.log('get.php #170', { url, formData, jqXhr });
                    })
                    .then(function(d) {
                        console.log('get.php #173');
                        resolve(d);
                    })
                    .catch(function() {
                        console.log('get.php #177', { url, formData });
                        reject('#142 Server-Fehler!');
                    });
                console.log('get.php #180');
            })
        }
    };

    var confTblArtikel = {
        key: 'mcid',
        pagesize: 20,
        title: 'Artikel',
        editable: true,
        insertable: true,
        deletable: true,
        fields: {
            Bezeichnung: {name: 'Bezeichnung', editable: true},
            Typ: {name: 'Typ', editable: true},
            Gruppe: {name: 'Gruppe', editable: true },
            Farbe: {name: 'Farbe', editable: true },
            Groesse: {name: 'Groesse', editable: true },
            Hersteller: { name: 'Hersteller', editable: true},
            NumInventar: { name: 'Num'},
            img_id: { name: 'Bild', editable: true,
                formatter: function(val, col, row, data) {
                    console.log('formatter for img_id. Params ', { val, col, row, data });
                    // return;
                    if (val) {
                        var src = '/api/admin/image/' + val + '/small?' + Date.now();
                        var srcRegular = '/api/admin/image/' + val;
                        $(this).html('');
                        $("<img/>")
                            .css({
                                maxHeight:'120px',
                                maxWidth:'120px',
                                height: 'auto'
                            })
                            .attr('src', src)
                            .on('load', function(e) {
                                console.log('Image with id ' + val + ' loaded!')
                            })
                            .on('click', function(e) {
                                getImageModal(srcRegular, {title: data.Bezeichnung, defList: {
                                        Hersteller: data.Hersteller,
                                        Typ: data.Typ,
                                        Farbe: data.Farbe,
                                        Groesse: data.Groesse,
                                        Grupper: data.Gruppe,
                                        Kategorie: data.Kategorie,
                                        Anzahl: data.NumInventar,
                                        Inventarisiert: data.NumAssignedInventar,
                                        img_ord: data.img_ord
                                    } }).modal('show');
                            })
                            .appendTo(this);
                        $(this).css('min-height','100px');
                    }
                },
                editor: function(val, name, rowData) {
                    var inputWrapper = this;
                    var srcOrig = $(this).closest('td').find('img').attr('src');
                    var valOrig = val;
                    var img = $("<img/>")
                        .css({ maxHeight:'120px', maxWidth:'120px',height:'auto', width:'auto' })
                        .addClass('img-edit-preview')
                        .attr('src', srcOrig)
                        .appendTo( inputWrapper );


                    var input = $("<input/>")
                        .attr({ hidden: "hidden", name: name })
                        .addClass("myDataTable-td-edit-input")
                        .val(val)
                        .appendTo( inputWrapper );

                    var action = $("<input/>")
                        .attr({ hidden: "hidden", name: name + "_action" })
                        .addClass("myDataTable-td-edit-input")
                        .val('nothing')
                        .appendTo( inputWrapper );

                    console.log('Bild-Editor #1 ', { val, name, inputWrapper, html: inputWrapper.html() });
                    var btnBar = $("<div/>").addClass("ui buttons").appendTo( inputWrapper );

                    var btnReset = $("<button/>")
                        .addClass('mini ui circular button icon grey')
                        .append( $("<i/>").addClass("minus circle icon") )
                        .on("click", function(e) {
                            if (srcOrig) {
                                img.attr("src", srcOrig);
                            }
                            file.val('');
                            input.val(valOrig);
                            action.val('nothing');
                        })
                        .appendTo( btnBar );

                    var lbl = $("<label/>")
                        .attr('for', "inputFile")
                        .addClass("mini ui circular button icon green upload")
                        .append( $("<i/>").addClass("upload icon") )
                        .appendTo( btnBar );

                    var file = $("<input/>")
                        .attr({type: "file", id: "inputFile", name: name + '_file', accept: ".jpg,.jpeg"})
                        .addClass("myDataTable-td-edit-input")
                        .appendTo( btnBar )
                        .on('change', function(e) {
                            if (this.files.length > 0) {
                                var reader = new FileReader();
                                reader.onload = function(e) {
                                    img.attr('src', e.target.result);
                                }
                                reader.readAsDataURL(this.files[0]); // convert to base64 string
                                action.val('new');
                            } else {
                                img.attr("src", "");
                                action.val('remove');
                            }
                            input.val('');
                        });

                    var btnDelete = $("<button/>")
                        .addClass('mini ui circular button icon red')
                        .append( $("<i/>").addClass("trash circle icon") )
                        .on("click", function(e) {
                            img.attr("src", "");
                            file.val('');
                            input.val(val);
                            action.val('remove');
                        })
                        .appendTo( btnBar );

                    console.log('Bild-Editor #2 ', { val, name, inputWrapper, html: inputWrapper.html() });

                }
            },
            created_at: {name: 'Erstellt', formatter: function(val, col, data) {
                    val = $.fn.myDataTable.formatters['date-dmy'](val);
                    $(this).text( val );
                }},
            created_uid: {name: 'Erstellt von'},
            modified_at: {name: 'Aktualisiert', formatter: function(val, col, data) {
                    val = $.fn.myDataTable.formatters['date-dmy'](val);
                    $(this).text( val );
                }},
            modified_uid: {name: 'Aktualisiert von'}
        },
        onSave: function(inputData, rowData, inputControls, row) {
            console.log('get.php #323');
            return new Promise(function(resolve, reject) {

                console.log('get.php #326 onSave in Promise ', { inputData, rowData, inputControls, row });
                var imgAction = $(inputControls['img_id_action']).val();
                var imgInput = inputControls['img_id_file'];
                if (imgInput instanceof jQuery) {
                    console.log('get.php #330 imgInput is instanceof jQuery!');
                    imgInput = imgInput[0];
                }

                var imgFile = imgInput.files && imgInput.files.length ? imgInput.files[0] : null;
                var updatedData = {};
                var changeData = {};

                var formData = new FormData();
                formData.append('id', rowData['mcid']);
                formData.append('mcid', rowData['mcid']);

                if (imgAction === 'new' && !imgFile) {
                    imgAction = 'nothing';
                }

                if (imgAction === 'remove' && !inputData.img_id) {
                    imgAction = 'nothing';
                }

                for(var k in inputData) {
                    if (!inputData.hasOwnProperty(k)) continue;
                    if (inputData[k] !== rowData[k]) {
                        changeData[k] = inputData[k];
                    }
                }

                console.log('get.php #283');
                switch(imgAction) {
                    case 'nothing':
                        if ('img_id' in changeData) {
                            delete changeData.img_id;
                        }
                        break;

                    case 'remove':
                        changeData.img_id = inputData.img_id;
                        break;

                    case 'new':
                        formData.append('img_id', inputData.img_id);
                        formData.append('image', imgFile, imgFile.name);
                        break;
                }

                if (Object.keys(changeData).length === 0) {
                    console.log('get.php #371');
                    resolve({});
                }

                for (var ch in changeData) {
                    if (!changeData.hasOwnProperty(ch)) continue;
                    formData.append(ch, changeData[ch]);
                }

                for (var pair of formData.entries()) {
                    console.log('formData ' + pair[0]+ ': '+ pair[1]);
                }

                var url = '/api/admin/artikel/update';
                console.log('get.php #752 ', { url, formData });
                $.fn.myDataTable.lib.postFormData(url, formData, function getJqXhr(jqXhr){
                        console.log('get.php #754 xhr for progress', { url, formData, jqXhr });
                    })
                    .then(function(d) {
                        if (d && ('img_id' in d)) {
                            console.log('get.php #758 resolve(d) img_id', { img_id: d.img_id });
                            // row.find('td[data-field] :input.myDataTable-td-edit-input[name=img_id]').val(d.img_id);
                        }
                        console.log('get.php #761 resolve(d)', { d });
                        resolve(d);
                    })
                    .catch(function() {
                        console.log('get.php #761 refect', { url, formData });
                        reject('#395 Server-Fehler!');
                    });
                console.log('get.php #397');
            });
        },
        onOpenInsert: function() {
            var self = this;
            return new Promise(function(tblResolve, tblReject) {

                artikel.openInsertDialog({jobid}, {
                    title: 'Neuen Artikel anlegen',
                    onSubmit: function(form, namedData, formData) {
                        console.log('#759 onOpenInsert onSubmit ', { form, namedData, formData });
                        return new Promise(function(resolve, reject) {
                            console.log('#71 raum.insert resolve namedData', { namedData });
                            resolve(namedData);
                        });
                    },
                    onSuccess: function(data) {
                        console.log('#766 onOpenInsert onSuccess ', { data });
                        return new Promise(function(resolve, reject)
                        {
                            resolve(data);
                            tblResolve(data);
                        });
                    },
                    onError: function(msg) {
                        console.log('#774 onOpenInsert onError ', { arguments });
                    },
                    onCancel: function() {
                        console.log('#777 onOpenInsert onCancel ', { arguments });
                    }
                });
            });
        }
    };
</script>
<style>
    th.col-filter .col-filter-control {
        width:100%;
    }

    input:checked + label .unchecked {
        display: none;
    }

    input:not(:checked) + label .checked {
        display: none;
    }
</style>

<div id="pageInventur">

    <div class="ui top attached tabular menu">
        <a class="item" data-tab="basisdaten">Basisdaten</a>
        <a class="item" data-tab="mitarbeiter">Mitarbeiter</a>
        <a class="item" data-tab="raeume" data-dataUrl="">Raeume</a>
        <a class="item" data-tab="artikel">Artikel</a>
        <a class="item" data-tab="inventar">Inventar</a>
        <a class="item" href="/api/admin/objektbuch/<?= (int)$jobid ?>">Objektbuch</a>
        <a class="item" data-tab="export">Export</a>
    </div>
    <div class="ui bottom attached tab segment" data-tab="basisdaten">
        <div class="ui form" id="frmInventur">
            <h4 class="ui dividing header">Inventur</h4>
            <div class="field field-mandant">
                <label for="mid">Mandant</label>
                <div class="ui fluid search selection dropdown" style="display: none;">
                    <input type="hidden" name="mid" id="mid">
                    <i class="dropdown icon"></i>
                    <div class="default text">Mandant auswählen</div>
                    <div class="menu options"></div>
                </div>
                <input type="text" name="mandant" id="Mandant" placeholder="" readonly>
            </div>
            <div class="field">
                <label for="mid">Titel</label>
                <input type="text" name="titel" id="Titel" placeholder="" readonly>
            </div>
            <div class="field transition hidden">
                <label for="aktiviert">Aktiv</label>
                <div class="inline field">
                    <div class="ui toggle checkbox aktiviert">
                        <input type="checkbox" name="aktiviert" id="aktiviert" tabindex="0" class="hidden">
                        <label for="aktiviert"><span class="checked">Ja</span><span class="unchecked">Nein</span></label>
                    </div>
                </div>
            </div>
            <div class="field transition hidden">
                <label for="Start">Startdatum</label>
                <input type="date" name="Start" id="Start" placeholder="">
            </div>
            <div class="field transition hidden">
                <label for="AbgeschlossenAm">Abgeschlossen</label>
                <input type="text" name="AbgeschlossenAm" id="AbgeschlossenAm" readonly placeholder="">
            </div>
            <div class="field">
                <label for="created_at">Erstellt am</label>
                <input type="text" name="created_at" id="created_at" readonly placeholder="">
            </div>
            <div class="field">
                <label for="create_user">Erstellt von</label>
                <input type="text" name="created_user" id="create_user" readonly placeholder="">
            </div>
        </div>
    </div>

    <div class="ui bottom attached tab segment" data-tab="mitarbeiter">
        <select id="SelectMitarbeiter" class="ui fluid search dropdown" name="mitarbeiter" multiple="multiple">

        </select>
        <div id="btnSaveMA" class="ui primary button">Speichern</div>
    </div>

    <div class="ui bottom attached tab segment" data-tab="raeume">
        <table id="tblRaeume" class="ui red celled unstackable padded table"></table>
    </div>

    <div class="ui bottom attached tab segment" data-tab="artikel">
        <table id="tblArtikel" class="ui red celled unstackable padded table"></table>
    </div>

    <div class="ui bottom attached tab segment" data-tab="inventar">
        <table id="tblInventar" class="ui red celled unstackable padded table"></table>
    </div>

    <div class="ui bottom attached tab segment" data-tab="export">
        <div style="height:200px;"></div>
    </div>
</div>
<div id="modalTemplates">
    <div class="ui modal my-modal-with-image">
        <i class="close icon"></i>
        <div class="header my-modal-title"></div>
        <div class="ximage content">
            <div class="ui xmedium ximage my-modal-image-box"></div>
            <div class="description my-modal-text" style="height:auto; overflow:hidden;">
                <img class="my-modal-image" style="float:left; margin-right:5rem;max-width:75vw;" onclick="window.open(this.src);"/>
                <table class="ui definition table my-modal-deflist-table">
                    <thead>
                        <tr>
                            <th>Eigenschaft</th>
                            <th>Wert</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody></table>
            </div>
        </div>
        <div class="actions">
            <div class="ui black deny button my-modal-btn-no">No</div>
            <div class="ui positive right labeled icon button my-modal-btn-yes">
                Yes <i class="checkmark icon"></i>
            </div>
        </div>
    </div>
    <div class="ui modal my-modal-raum">
        <i class="close icon"></i>
        <div class="header my-modal-title"></div>
        <div class="content">
            <div class="ui xmedium my-modal-image-box"></div>
            <div class="description my-modal-text">

                <h5>Pläne</h5>
                <div class="my-modal-raum-plaene">
                    <table class="ui table celled padded my-modal-table-plaene"></table>
                </div>
                <div>
                    <label for="fileRaumplaene" class="mini ui button icon green upload lblUpload">add</label>
                    <input id="fileRaumplaene" name="fileRaumplaene" multiple="" type="file" style="display: none;" accept="image/*">
                </div>
                <h5>Bilder</h5>
                <div class="my-modal-raum-bilder">
                    <table class="ui table celled padded my-modal-table-bilder""></table>
                </div>
                <div>
                    <label for="fileRaumbilder" class="mini ui button icon green upload lblUpload">add</label>
                    <input id="fileRaumbilder" name="fileRaumbilder" multiple="" type="file" style="display: none;" accept="image/*">
                </div>

                <h5>Raumdaten</h5>
                <table class="ui definition table my-modal-deflist-table">
                    <thead>
                    <tr>
                        <th>Eigenschaft</th>
                        <th>Wert</th>
                    </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="actions">
            <div class="ui black deny button my-modal-btn-no">No</div>
            <div class="ui positive right labeled icon button my-modal-btn-yes">
                Yes <i class="checkmark icon"></i>
            </div>
        </div>
    </div>
    <div class="ui modal my-modal-details">
        <i class="close icon"></i>
        <div class="header my-modal-title"></div>
        <div class="content">
            <div class="ui xmedium my-modal-image-box"></div>
            <div class="description my-modal-text">

                <img class="my-modal-image" style="float:left; margin-right:5rem;max-width:75vw;" onclick="window.open(this.src);"/>

                <table class="ui definition table my-modal-deflist-table">
                    <thead>
                    <tr>
                        <th>Eigenschaft</th>
                        <th>Wert</th>
                    </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>

    </div>
</div>

<script>

    $(function(){
        var aTabStatus = {};

        var loadInventurBaseData = function(viewData) {
            var viewData = <?= json_encode($tplVars) ?>;
            var inventurDaten = viewData.inventur;
            var inventurCreatedBy = viewData.inventurCreatedBy;
            var mandanten = viewData.aMandanten;
            var users = viewData.aUsers;
            var selectedUsers = viewData.aInvUsers;
            var jobid = viewData.inventur.jobid;
            console.log({viewData});

            var fieldMandant = $('.field-mandant');
            var mandantSelectOptions = fieldMandant.find('.options');
            var mandantDropdown = fieldMandant.find('.ui.dropdown');
            var activeMandantName = '';

            mandanten.forEach(function(m) {
                var option = $("<div/>").addClass("item").attr('data-value', m.mid).data('value', m.mid).text(m.Mandant);
                if ('mid' in inventurDaten && inventurDaten.mid == m.mid) {
                    // option.addClass('active');
                    activeMandantName = m.Mandant;
                }
                mandantSelectOptions.append( option );
            });

            var inventurForm = $('#frmInventur');
            for(var k in inventurDaten) {
                if (!inventurDaten.hasOwnProperty(k)) {
                    continue;
                }
                inventurForm.find(':input#' + k).val( inventurDaten[k] );
                inventurForm.find('.readable.text-' + k).text( inventurDaten[k] );
                inventurForm.find('.readable.html-' + k).html( inventurDaten[k] );
            }
            inventurForm.find(':input#Mandant').val( activeMandantName );
            inventurForm.find(':input#create_user').val( inventurCreatedBy.name || '' );
            inventurForm.find('.readable.text-mandant').text( activeMandantName );

            mandantDropdown.dropdown();
            $('#frmInventur').find('.ui.checkbox').checkbox();
        };

        var loadMitarbeiter = function(viewData) {
            var users = viewData.aUsers;
            var selectedUsers = viewData.aInvUsers;

            var userSelectBox = $("select#SelectMitarbeiter");
            users.forEach(function(u) {
                var selected = selectedUsers.find( function(s) { return s.uid === u.id; });
                var opt = $('<option/>').attr('value', u.id).text(u.name).prop('selected', !!selected);
                userSelectBox.append( opt );
            });

            // $('#frmInventur').find('.ui.dropdown').dropdown();
            $("select#SelectMitarbeiter").dropdown();

            $('#btnSaveMA').off('click').on('click', function(e) {
                // var url = '/api/admin/inventuren/' + jobid + '/setUsers';
                var url = '/api/admin/inventuren/setJobUsers';
                var $self = this;
                $(this).prop('disabled', true);
                $.post(
                    url,
                    {
                        jobid,
                        mitarbeiter: $('#SelectMitarbeiter').val()
                    },
                    function(data) {
                        console.log('response from setUsers', data);
                    },
                    'json'
                ).done(function() {
                    $($self).prop('disabled', false);
                });
            });
        };

        $('#pageInventur .menu .item').tab({
            onFirstLoad: function(tabPath, params, historyEvent) {
                if (1) {
                    console.log('#1118 pageInventur tab onFirstLoad', { tabPath, params, historyEvent, arguments });
                }
                if (tabPath in aTabStatus && aTabStatus[tabPath].isLoading) {
                    console.log('#1121 pageInventur tab ' + tabPath + ' is already loading');
                    return true;
                }
                switch(tabPath) {
                    case 'raeume':
                        aTabStatus[tabPath] = { isLoading: true };
                        console.log('#1122 pageInventur tab raeume load export');
                        $.get('/api/admin/inventuren/' + jobid + '/raeume', function(response) {
                            console.log('#1124 pageInventur tab raeume LOADED', { target, response });
                            confTblRaeume.data = response.rows;
                            $('#tblRaeume').myDataTable( confTblRaeume );
                            aTabStatus[tabPath].isLoading = false;
                        });
                        break;

                    case 'artikel':
                        aTabStatus[tabPath] = { isLoading: true };
                        console.log('#1131 pageInventur tab artikel load');
                        $.get('/api/admin/inventuren/' + jobid + '/artikel', function(response) {
                            console.log('#1133 pageInventur tab artikel LOADED  ', { target, response });
                            confTblArtikel.data = response.rows;
                            $('#tblArtikel').myDataTable( confTblArtikel);
                            aTabStatus[tabPath].isLoading = false;
                        });
                        break;

                    case 'inventar':
                        aTabStatus[tabPath] = { isLoading: true };
                        console.log('#1140 pageInventur tab inventar load');
                        $.get('/api/admin/inventuren/' + jobid + '/inventar', function(response) {
                            console.log('#1143 pageInventur tab inventar LOADED  ', { target, response });
                            confTblInventar.data = response.rows;
                            confTblInventar.colNames = response.cols || [];
                            $('#tblInventar').myDataTable( confTblInventar );
                            aTabStatus[tabPath].isLoading = false;
                        });
                        break;

                    case 'export':
                        aTabStatus[tabPath] = { isLoading: true };
                        console.log('#1150 pageInventur tab export load ');
                        var target = $('.tab.segment[data-tab=export]');
                        aTabStatus[tabPath].isLoading = true;
                        $.get('/api/admin/inventuren/' + jobid + '/export', function(response) {
                            console.log('#1153 pageInventur tab export LOADED  ', { target, response });
                            if (typeof response === 'string') {
                                target.html( response );
                            }
                            aTabStatus[tabPath].isLoading = false;
                        });
                }
            },
            onLoad: function(tabPath, params, historyEvent) {
                self.location.hash = tabPath;
                $('#pageInventur .menu').data('active-path', tabPath);
                console.log('#1163 called load tab without request', tabPath );
            },
            onRequest: function(tabPath) {
                console.log('request tab', tabPath );
            },
            onVisible: function(tabPath) {
                console.log('visible tab', tabPath);
            }
        });

        var viewData = <?= json_encode($tplVars) ?>;
        var jobid = viewData.inventur.jobid;


        loadInventurBaseData(viewData);
        loadMitarbeiter(viewData);

        var changeTabByHash = function() {
            if (self.location.hash.length < 2 ) {
                console.log('changeTabByHash #1 cancel no hash', self.location.hash);
                return;
            }

            var hash = self.location.hash.substr(1);
            console.log('changeTabByHash #2', { hash });

            if (hash.substr(0,1) === '/') {
                hash = hash.substr(1);
                console.log('changeTabByHash #3', { hash });
            }

            if (!hash) {
                console.log('changeTabByHash #4 cancel no hash', { hash });
                return;
            }

            var menu = $('#pageInventur .menu');
            var tab = $('#pageInventur .menu .item');
            var activePath = menu.data('active-path');
            console.log('changeTabByHash #5', { hash, activePath });

            if (hash === activePath) {
                console.log('changeTabByHash #6 cancel');
                return;
            }


            var isTab = tab.tab('is tab', hash);

            if (isTab) {
                console.log('changeTabByHash change tab #7', { hash });
                tab.tab('change tab', hash);
            } else {
                console.log('changeTabByHash #8 no changes', { hash });
            }
        };

        $(window).on('hashchange', changeTabByHash);

        if (initialHash) {
            window.location.hash = initialHash;
            $(window).trigger('hashchange');
        }

    });
</script>

<?php include APP_VIEWS_PATH . '/admin/inventuren/_raeume_insert.php' ?>
<?php include APP_VIEWS_PATH . '/admin/inventuren/_inventar_insert.php' ?>
<?php include APP_VIEWS_PATH . '/admin/inventuren/_artikel_insert.php' ?>
<?php include APP_VIEWS_PATH . '/global/partials/htmlfoot.php' ?>
