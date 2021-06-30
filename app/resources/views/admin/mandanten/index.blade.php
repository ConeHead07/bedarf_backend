@extends('layouts.master')
@section('title', $title ?? 'Mandanten')
@section('sidebar')
@endsection
@section('content')
    <style>
        th.col-filter .col-filter-control {
            width:100%;
        }
        .force-show {
            display: block !important;
        }
    </style>

    <script>
        var aRows = @json($tplVars->aRows, JSON_PRETTY_PRINT) || [];
    </script>

    <div id="basicDialog" class="ui mini modal">
        <div class="ui header dialog-title"></div>
        <div class="context dialog-text">
        </div>
        <div class="actions">
            <div class="ui primary approve inverted button">
                <i class="checkmkark icon"></i> OK
            </div>
        </div>
    </div>

    <div id="formBoxCreateMandant" class="ui small modal scrolling">
        <div class="header">
            Mandant anlegen
        </div>
        <div class="scrolling content">
            <div class="description">
                <div class="ui header">Mandant</div>
                <form id="formCreateMandant" class="ui form">

                    <div class="field">
                        <label>Name</label>
                        <input type="text" name="Mandant" placeholder="Name...">
                    </div>

                    <div class="ui error message"></div>
                </form>
            </div>

        </div>
        <div class="actions">

            <div id="btnCancel" class="ui negative right labeled icon button cancel">
                Abbrechen
                <i class="minus circle icon"></i>
            </div>
            <div id="btnSave" class="ui green right labeled icon button ok approve">
                Speichern <i class="checkmark icon"></i>
            </div>
        </div>
    </div>

    <div style="max-width:100vw;overflow-x: scroll;">
    <table id="tableList" class="ui blue celled padded table">
    </table>
    </div>
    <script src="/assets/jslibrary/myDataTable.js"></script>
    <script>
        $(function() {
            var usrName = $("#formBoxCreateMandant").find(":input[name=Mandant]");
            var formIsValid = false;

            var usrNameInputTimer = null;
            usrName.on('input, change', function(e) {
                if (usrNameInputTimer !== null) {
                    clearTimeout(usrNameInputTimer);
                }
                switch(e.type) {
                    case 'change':
                        checkMandant();
                        break;

                    case 'input':
                    default:
                        usrNameInputTimer = setTimeout(checkMandant, 200);
                }
            });

            var $modalCreateUsr = $('#formBoxCreateMandant')
                .css({ zIndex: 1050 })
                .appendTo('body').modal({
                    centered: true
                })
                .modal('hide')
                .modal({
                    onShow: function() {
                        $("#formCreateMandant").form('clear');
                    }
                });

            var checkMandant = function(target) {
                var frmErrBox = $('#formBoxCreateMandant').find('.ui.error.message');
                var frmButton = $('#formBoxCreateMandant').find('#btnSave');
                frmButton.waitMe({ effect: 'ios'}).addClass('disabled').prop('disabled', true);

                $.post('/api/admin/mandanten/checkMandant', {
                        Mandant: usrName.val()
                    },
                    function(data) {
                        console.log('checkMandant Result data', data);
                        frmButton.waitMe('hide');
                        if (data.success === true) {
                            frmButton.removeClass('disabled').prop('disabled', false);
                            frmErrBox.html('').removeClass('force-show');
                        }
                        if (data.message) {
                            frmErrBox.html(data.message.split("\n").join("<br>")).addClass('force-show');
                        }
                    });
            };

            var listConf = {
                key: 'id',
                rownumbers: true,
                colfilters: true,
                title: 'Mandanten',
                showFooter: true,
                insertable: true,
                editable: true,
                deletable: true,
                onOpenInsert: function() {
                    var self = this;
                    return new Promise(function(tblResolve, tblReject) {
                        var $formCreateMandant = $('#formCreateMandant');
                        $formCreateMandant
                            .form({
                                fields: {
                                    Mandant: {
                                        identifier: 'Mandant',
                                        rules: [
                                            {
                                                type   : 'minLength[1]'
                                            }
                                        ]
                                    }
                                }
                            })
                            .submit(function(e) {
                                console.log('on submit form');
                            })
                        ;

                        $("#formBoxCreateMandant")
                            .modal({
                                onDeny: function() {
                                    $formCreateMandant.form('reset');
                                    $formCreateMandant.form('clear errors');
                                    tblReject();
                                },
                                onApprove: function() {
                                    $formCreateMandant.form('validate form');
                                    if (!$formCreateMandant.form('is valid')) {
                                        return false;
                                    }

                                    if( $formCreateMandant.form('is valid')) {
                                        // form is valid (both email and name)
                                        console.log("Formular-Eingabe ist OK!");

                                        $
                                            .post(
                                                '/api/admin/mandanten/create',
                                                {
                                                    Mandant: usrName.val()
                                                },
                                                function (data) {
                                                    console.log('form submit resposne data', { data });
                                                    if ('success' in data && data.success === true) {
                                                        $modalCreateUsr.modal('hide');
                                                        tblResolve(data);
                                                        $('#basicDialog')
                                                            .find('.dialog-title').text('').end()
                                                            .find('.dialog-text').text('Mandant wurde angelegt!').end()
                                                        // .modal({
                                                        //     onApprove: function() {
                                                        //         console.log("Dialog wurde bestätigt");
                                                        //         window.document.location.reload();
                                                        //     },
                                                        //     onHidden: function() {
                                                        //         console.log("Dialog wurde geschlossen");
                                                        //         window.document.location.reload();
                                                        //     }
                                                        // })
                                                            .modal('show');
                                                        return;
                                                    }
                                                    console.error("Es sind Fehler aufgetreten!\ndata: ", data);
                                                    if ('errorFields' in data && Object.values(data.errorFields).length > 0) {
                                                        var errors = {};
                                                        for (var f in Object.keys(data.errorFields)) {
                                                            var k = (f !== 'password') ? f : 'pw';
                                                            if (['name', 'email', 'pw'].indexOf(k) !== -1) {
                                                                errors[f] = data.errorFields[k];
                                                                $('#formCreateMandant').form('add prompt', k);
                                                            }
                                                        }
                                                        $('#formCreateMandant').form('add errors', data.errorFields);
                                                        return;
                                                    }
                                                    if ('error' in data && data.error != '') {
                                                        $formCreateMandant.form('add errors', { 'server': data.error });
                                                        return;
                                                    }
                                                    $('#formCreateMandant').form('add errors', { server: 'Interner Server-Fehler beim Speichern!'});
                                                })
                                            .fail(function() {
                                                console.error('form submit failed', { arguments });
                                            });
                                        return false;
                                    } else {
                                        console.error("Formular-Eingabe ist ungültig");
                                    }
                                    return false;
                                }
                            })
                            .modal('show');

                        $formCreateMandant.find(':input').each( function() {
                            console.log(':input elm #221', this);
                            $(this).on('change', function() {
                                var $frm = $('#formCreateMandant');

                                $frm.form(
                                    'validate field',
                                    $(this).attr('name')
                                );
                            });
                        });

                    });
                },
                fields: {
                    mid: { name: 'MID' },
                    Mandant: { name: 'Name', editable: true },
                    numInventuren: { name: 'Inventuren' },
                    numGebaeude: { name: 'Gebaeude' },
                    created_at: { name: 'Erstellt am' },
                    created_uid: { hidden: true },
                    created_by: { hidden: true },
                    modified_at: { hidden: true },
                    modified_uid: { hidden: true },
                    modified_by: { hidden: true }
                },
                openDoc: true,
                onOpenDoc: function(row, rowData) {
                    self.location.href = '/api/admin/mandanten/' + rowData.mid;
                },
                onSave: function(inputData, rowData, inputControls, row) {
                    console.log('onSave #251', { inputData, rowData, inputControls, row });
                    var id = rowData.mid;
                    var formData = {
                        Mandant: inputData.Mandant
                    };

                    console.log('onSave #258 return new Promise()');
                    return new Promise(function(resolve, reject) {

                        var tbl = $(row).closest('table');
                        var url = '/api/admin/mandanten/' + id + '/update';
                        console.log('onSave #262 ', { id, url, formData });
                        tbl.waitMe({ effect: 'ios'});
                        $.post(url, formData)
                            .done(function(data) {
                                console.log('onSave response #264 ', { data });
                                tbl.waitMe('hide');
                                if (data && data.success) {

                                    return resolve(data.data);
                                }
                                if (data && data.message) {
                                    alert(data.message);
                                } else {
                                    alert('Mandant konnte nicht gelöscht werden!');
                                }
                                reject();
                            }).fail(function() {
                                console.error(arguments);
                                reject();
                            });
                    });
                },
                onDelete: function(data, tr) {
                    console.log('onDelete #221', { arguments });
                    return new Promise(function(resolve, reject) {
                        var id = data.mid;
                        var tbl = $(tr).closest('table');
                        tbl.waitMe({ effect: 'ios'});
                        $.post('/api/admin/mandanten/' + id + '/delete', function(data) {
                            tbl.waitMe('hide');
                            if (data && data.success) {

                                return resolve();
                            }
                            if (data && data.message) {
                                alert(data.message);
                            } else {
                                alert('Mandant konnte nicht gelöscht werden!');
                            }
                            reject();
                        });
                    });
                },
                onRendered: function() {
                    $('#tableList').find('.btn-user-delete').off('click').on('click', function(e) {
                        e.preventDefault();
                        var id = $(this).data('id');
                        var tr = $(this).closest('tr');
                        var rowData = tr.data('row');
                        var userName = (rowData && 'name' in rowData) ? rowData.Mandant + ' ' : '';
                        if (!confirm('Möchten Sie den Mandant ' + userName + 'wirklich löschen?' )) {
                            return false;
                        }
                        var url = "/api/admin/mandanten/" + id + "/delete";
                        $.getJSON(url, function(data) {
                            console.log('response delete mandant', { url, data });
                            if ('success' in data && data.success) {
                                console.log('remove row from table');
                                tr.remove();
                                usrTbl.deleteById(id);
                            } else {
                                console.error('ERROR, delete-Request answered without success-response');
                            }
                        });
                        return false;
                    });
                }
            };

            // var usrTbl = dataTable( '#tableList', aRows, listConf);
            // usrTbl.orderby('created_at', 'DESC');
            listConf.data = aRows;
            $('#tableList').myDataTable( listConf ).myDataTable('orderby', 'Mandant', 'asc');
        });
    </script>
@endsection
