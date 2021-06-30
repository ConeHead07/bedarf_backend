@extends('layouts.master')
@section('title', $title ?? 'Administration')
@section('sidebar')
@endsection
@section('content')
<style>
    th.col-filter .col-filter-control {
        width:100%;
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

<div id="formBoxCreateUser" class="ui small modal scrolling">
    <div class="header">
        Benutzer anlegen
    </div>
    <div class="scrolling content">
        <div class="description">
            <div class="ui header">Benutzerprofil</div>
                <form id="formCreateUser" class="ui form">

                    <div class="field">
                        <label>Benutzername</label>
                        <input type="text" name="name" placeholder="Name...">
                    </div>

                    <div class="field">
                        <label>Email</label>
                        <input type="email" name="email" placeholder="Email...">
                    </div>

                    <div class="field">
                        <label>Passwort</label>
                        <input type="text" name="pw" placeholder="Passwort...">
                    </div>

                    <div class="field">
                        <label>Passwort-Wiederholung</label>
                        <input type="text" name="pw2" placeholder="PW-Wiederholung...">
                    </div>

                    <div class="field">
                        <label>Ist Administrator</label>
                        <div class="ui slider checkbox">
                            <input type="checkbox" name="IstAdmin" value="1">
                            <label>Admin</label>
                        </div>
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

<table id="tableList" class="ui blue celled padded table">
</table>
<script src="/assets/jslibrary/myDataTable.js"></script>
<script>
    $(function() {
        var usrName = $("#formBoxCreateUser").find(":input[name=name]");
        var usrEmail = $("#formBoxCreateUser").find(":input[name=email]");
        var istAdmin = $("#formBoxCreateUser").find(":input[name=IstAdmin]");
        var usrPW1 = $("#formBoxCreateUser").find(":input[name=pw]");
        var usrPW2 = $("#formBoxCreateUser").find(":input[name=pw2]");
        var formIsValid = false;

        var $modalCreateUsr = $('#formBoxCreateUser')
            .css({ zIndex: 1050 })
            .appendTo('body').modal({
            centered: true
        })
            .modal('hide')
            .modal({
                onShow: function() {
                    $("#formCreateUser").form('clear');
                }
            });

        var rgxEmail = /^[a-zA-Z0-9.!#$%&’*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/;

        var checkEmailFormat = function(email) {
            return null !== email.match(rgxEmail);
        };

        var checkUserNameEmail = function(target) {
            var email = usrEmail.val();

            if (target === usrEmail.get(0) && !checkEmailFormat(email)) {
                console.error('Invalid email!');
                return;
            }
            $.post('/api/admin/user/checkUser', {
                    name: usrName.val(),
                    email: usrEmail.val(),
                    IstAdmin: istAdmin.prop('checked') ? 1 : 0
                },
                function(data) {
                    console.log('checkUerNameEmail response-data:', { data });
                });
        };

        var bwIsBad = function(p) {
            var onlyNumber = p.match(/^\d{,6}$/);
            if (null !== onlyNumber) {
                return true;
            }
            var onlyLower = p.match(/^[a-z]{,6}$/);
            if (null !== onlyLower) {
                return true;
            }
            var onlyUpper = p.match(/^[A-Z]{,6}$/);
            if (null !== onlyUpper) {
                return true;
            }
        };

        // Minimum 6 characters at least 1 Alphabet and 1 Number
        var pwRuleLight = /^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{6,}$/;

        // Check at least 1 Alpha, 1 Nmbr, 1 SpecialChar
        var pwRuleOk = /^(?=.*[A-Za-z])(?=.*\d)(?=.*[$@$!%*#?&])[A-Za-z\d$@$!%*#?&]{6,}$/;

        // Check at least 1 Upper, 1 Lower and 1 Number
        var pwRuleGood = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]{6,}$/

        // Check at least 1 Uppercase Alphabet, 1 Lowercase Alphabet, 1 Number
        var pwRuleStrong = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[$@$!%*?&])[A-Za-z\d$@$!%*?&]{6,}/;

        // Minimum 6 and Maximum 10 characters
        // AND at least 1 Uppercase Alphabet, 1 Lowercase Alphabet, 1 Number and 1 Special Character:
        var pwRuleStrongMinMax = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[$@$!%*?&])[A-Za-z\d$@$!%*?&]{6,10}/;

        // Check at least 1 Upper, 1 Lower and 1 Number
        var pwIsGood = function(p) {
            return null !== p.match(/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]{6,}$/);
        };




        var counter = (function() {
            var count = 0;
            return { nextId: function() { return ++count; }};
        })();

        function fileSizeReadable(fsize) {
            if (fsize < 1024) {
                return fsize + ' Bytes';
            } else if (fsize < (1024 *1024) ) {
                return (fsize / 1024).toFixed(1).toString().replace('.', ',') + ' KB';
            }
            return (fsize / (1024 * 1024)).toFixed(1).replace('.', ',') + ' MB';
        }

        var listConf = {
            key: 'id',
            rownumbers: true,
            colfilters: true,
            title: 'Benutzer',
            showFooter: true,
            insertable: true,
            onOpenInsert: function() {
                var self = this;
                return new Promise(function(tblResolve, tblReject) {
                    var $formCreateUser = $('#formCreateUser')
                        .form({
                            fields: {
                                name: {
                                    identifier: 'name',
                                    rules: [
                                        {
                                            type   : 'minLength[1]'
                                        }
                                    ]
                                },
                                email: {
                                    identifier: 'email',
                                    rules: [
                                        {
                                            type   : 'email',
                                            prompt : 'Email-Syntax ist ungültig'
                                        }
                                    ]
                                },
                                pw: {
                                    identifier: 'pw',
                                    rules: [
                                        {
                                            type   : 'regExp',
                                            value  : /^(?=.*[A-Za-z])(?=.*\d)(?=.*[$@$!%*#?&])[A-Za-z\d$@$!%*#?&]{6,}$/,
                                            prompt : function(value) {
                                                if(value.length < 6) {
                                                    return 'Das Passwort muss mind. aus 6 Zeichen bestehen!';
                                                }
                                                return 'Das Passwort muss aus Groß-, Kleinbuchstaben, Zahlen und mind 1 Sonderzeichen bestehen.';
                                            }
                                        }
                                    ]
                                },
                                pw2: {
                                    rules: [
                                        {
                                            type   : 'match[pw]',
                                            prompt : 'Passwort-Wiederholung stimmt nicht mit Passwort überein.'
                                        }
                                    ]
                                }
                            }
                        })
                        .submit(function(e) {
                            console.log('on submit form');
                        })
                    ;

                    $("#formBoxCreateUser")
                        .modal({
                            onDeny: function() {
                                $formCreateUser.form('reset');
                                $formCreateUser.form('clear errors');
                                tblReject();
                            },
                            onApprove: function() {
                                $formCreateUser.form('validate form');
                                if (!$formCreateUser.form('is valid')) {
                                    return false;
                                }

                                if( $formCreateUser.form('is valid')) {
                                    // form is valid (both email and name)
                                    console.log("Formular-Eingabe ist OK!");

                                    $
                                        .post(
                                            '/api/admin/user/create',
                                            {
                                                name: usrName.val(),
                                                email: usrEmail.val(),
                                                pw: usrPW1.val(),
                                                IstAdmin: istAdmin.prop('checked') ? 1 : 0,
                                            },
                                            function (data) {
                                                console.log('form submit resposne data', { data });
                                                if ('success' in data && data.success === true) {
                                                    $modalCreateUsr.modal('hide');
                                                    tblResolve(data);
                                                    $('#basicDialog')
                                                        .find('.dialog-title').text('').end()
                                                        .find('.dialog-text').text('Benutzer wurde angelegt!').end()
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
                                                            $('#formCreateUser').form('add prompt', k);
                                                        }
                                                    }
                                                    $('#formCreateUser').form('add errors', data.errorFields);
                                                    return;
                                                }
                                                if ('error' in data && data.error != '') {
                                                    $formCreateUser.form('add errors', { 'server': data.error });
                                                    return;
                                                }
                                                $('#formCreateUser').form('add errors', { server: 'Interner Server-Fehler beim Speichern!'});
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

                    $formCreateUser.find(':input').each( function() {
                        $(this).on('change', function() {
                            $formCreateUser.form('validate field', $(this).attr('name') );
                        });
                    });

                });
            },
            fields: {
                id: { name: 'UID' },
                name: { name: 'name' },
                email: { name: 'email' },
                created_at: { name: 'Erstellt am' },
                x: { name: 'aktion', formatter: function(val, colname, rowElm, rowData) {
                        $(this).append(
                            $('<a href="/api/admin/user/' + rowData.id + '">Benutzer-Inventuren laden</a>')
                        );
                        if (rowData.id != 1) {
                            $(this).append(
                                $('<span/>')
                                    .addClass("ui button negative btn-user-delete right")
                                    .data('id', rowData.id)
                                    .append( $("<i/>").addClass("icon trash alternate") )
                            );
                        }
                    }}
            },
            onRendered: function() {
                $('#tableList').find('.btn-user-delete').off('click').on('click', function(e) {
                    e.preventDefault();
                    var id = $(this).data('id');
                    var tr = $(this).closest('tr');
                    var rowData = tr.data('row');
                    var userName = (rowData && 'name' in rowData) ? rowData.name + ' ' : '';
                    if (!confirm('Möchten Sie den Benutzer ' + userName + 'wirklich löschen?' )) {
                        return false;
                    }
                    var url = "/api/admin/user/" + id + "/delete";
                    $.getJSON(url, function(data) {
                        console.log('response delete user', { url, data });
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
        $('#tableList').myDataTable( listConf ).myDataTable('orderby', 'created_at', 'desc');
    });
</script>
@endsection
