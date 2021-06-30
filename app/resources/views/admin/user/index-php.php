<?php
include APP_VIEWS_PATH . '/global/partials/htmlhead.php';

/** @var \App\InventurenUser $inventurenListen */
$inventurenListen;
?>
<style>
    th.col-filter .col-filter-control {
        width:100%;
    }
    .ui.basic.modal.confirm-dialog {
        width: 50%;
        min-width:550px;
        border: 2px solid rgba(255, 255, 255, 0.5);
        border-radius: 10px;
    }
</style>
<script src="/assets/jslibrary/myDataTable.js"></script>
<script>
    var viewData = <?=  ($tplVars ? json_encode($tplVars) : '{}') ?>;
    var aUserInventuren = viewData.inventurenListen || [];
    var user = viewData.user || {};
</script>
<?php
/*
$bc128bGenrator = new App\Utils\Barcodes\WebFontLibreBarcode128();
$bcText = 'IN-17-8a9a98c4e3';
$bc128B = $bc128bGenrator->get( $bcText );
$bc128Seq = $bc128bGenrator->getCodeSequence($bcText, false);
$bc128SeqFull = $bc128bGenrator->getCodeSequence($bcText, true);

Code Generated By PHP
<div data-code="<?=$bcText?>" class="bc">
    <span class="bc-128"><?=$bc128B?></span>
    <span class="bc-text"><?=$bcText?></span><br>
    <span class="bc-text"><?= implode('|', $bc128Seq ) ?></span><br>
    <span class="bc-text"><?= implode('|', $bc128SeqFull ) ?></span>
</div>
*/
?>




<div id="basicDialog" class="ui basic modal confirm-dialog">
    <div class="ui header dialog-title"></div>
    <div class="content dialog-text">
    </div>
    <div class="actions">
        <div class="ui primary approve inverted button">
            <i class="checkmkark icon"></i> OK
        </div>
    </div>
</div>

<div>

    <div class="ui top attached tabular menu">
        <a class="item" data-tab="first">Bentuzerprofil</a>
        <a class="item" data-tab="second">Inventuren</a>
    </div>
    <div class="ui bottom attached tab segment" data-tab="first">
        <form id="formEditUser" class="ui form">

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
                <input type="password" name="password" placeholder="Passwort...">
            </div>

            <div class="field">
                <label>Passwort-Wiederholung</label>
                <input type="password" name="pw2" placeholder="PW-Wiederholung...">
            </div>

            <div class="field">
                <label>Ist Administrator</label>
                <div class="ui slider checkbox">
                    <input type="checkbox" name="IstAdmin" value="1">
                    <label>Admin</label>
                </div>
            </div>

            <div class="ui error message"></div>

            <div class="actions">
                <div id="btnSave" class="ui green right labeled icon button">
                    Speichern <i class="checkmark icon"></i>
                </div>
            </div>
        </form>
    </div>
    <div class="ui bottom attached tab segment" data-tab="second">
        <table id="InvUser" class="ui blue celled padded table">
    </div>
</div>
</table>

<script>

(function($){
    $('.menu .item').tab();
    var usrName = $("#formEditUser").find(":input[name=name]");
    var usrEmail = $("#formEditUser").find(":input[name=email]");
    var istAdmin = $("#formEditUser").find(":input[name=IstAdmin]");
    var usrPW1 = $("#formEditUser").find(":input[name=password]");
    var usrPW2 = $("#formEditUser").find(":input[name=pw2]");
    
    var counter = (function() {
        var count = 0;
        return { nextId: function() { return ++count; }};
    })();

    console.log('#88 user', { user });
    for(var k of Object.keys(user)) {
        if (k !== 'IstAdmin') {
            $('#formEditUser').find(':input[name='+k+']').val(user[k]);
        } else {
            istAdmin.prop('checked', user[k] == 1);
        }
    }

    var $formEditUser = $('#formEditUser')
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
                }
                ,password: {
                    identifier: 'password',
                    rules: [
                        {
                            type   : 'regExp',
                            value  : /^(|(?=.*[A-Za-z])(?=.*\d)(?=.*[$@$!%*#?&])[A-Za-z\d$@$!%*#?&]{6,})$/,
                            optional: true,
                            prompt : function(value) {
                                if(value.length < 6) {
                                    return 'Das Passwort muss mind. aus 6 Zeichen bestehen!';
                                }
                                return 'Das Passwort muss aus Groß-, Kleinbuchstaben, Zahlen und mind 1 Sonderzeichen bestehen.';
                            }
                        }
                    ]
                }
                ,pw2: {
                    rules: [
                        {
                            type   : 'match[password]',
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


    $('#formEditUser').find('.ui.checkbox').checkbox();

    $("#btnSave").click( function(e) {
        console.log('#152');
        e.preventDefault();
        console.log('#154');
        $('#formEditUser').form('validate form');
        console.log('#156');
        if (!$('#formEditUser').form('is valid')) {

            console.log('#159');
            return false;
        }

        console.log('#163');
        if( $('#formEditUser').form('is valid')) {
            // form is valid (both email and name)
            console.log('#166');
            console.log("Formular-Eingabe ist OK!");

            var data = {
                id: user.id,
                name: usrName.val(),
                email: usrEmail.val(),
                IstAdmin: istAdmin.prop('checked') ? 1 : 0
            };
            if (usrPW1.val()) {
                data.password = usrPW1.val()
            }
            console.log('post update userdata: ', { data });
            $

                .post(
                    '/api/admin/user/' + user.id + '/update',
                    data,
                    function (data) {
                        console.log('form submit resposne data', { data });
                        if ('success' in data && data.success === true) {
                            $('#basicDialog')
                                .find('.dialog-title').text('Aktion').end()
                                .find('.dialog-text').text('Benutzer wurde aktualisiert!').end()
                                .modal({
                                    onApprove: function() {
                                        console.log("Dialog wurde bestätigt");
                                    }
                                })
                                .modal('show');
                            return;
                        }
                        console.error("Es sind Fehler aufgetreten!\ndata: ", data);
                        if ('errorFields' in data && Object.values(data.errorFields).length > 0) {
                            var errors = {};
                            for (var f in Object.keys(data.errorFields)) {
                                var k = f;
                                if (['name', 'email', 'password'].indexOf(k) !== -1) {
                                    errors[f] = data.errorFields[k];
                                    $('#formEditUser').form('add prompt', k);
                                }
                            }
                            $('#formEditUser').form('add errors', data.errorFields);
                            return;
                        }
                        if ('error' in data && data.error != '') {
                            $formEditUser.form('add errors', { 'server': data.error });
                            return;
                        }
                        $('#formEditUser').form('add errors', { server: 'Interner Server-Fehler beim Speichern!'});
                    })
                .fail(function() {
                    console.error('form submit failed', { arguments });
                });
            return false;
        } else {
            console.error("Formular-Eingabe ist ungültig");
        }
        return false;
    });

    var tableConf = {
        key: 'jobid',
        rownumbers: true,
        colfilters: true,
        title: 'Benutzer-Inventuren',
        fields: {
            jobid: { name: 'jobid' },
            uid: { name: 'uid' },
            mid: { name: 'mid' },
            Mandant: { name: 'Mandant' },
            Titel: { name: 'Inventur' },
            AbgeschlossenAm: { name: 'Abgeschlossen am' },
            x: { name: 'aktion', formatter: function(val, colname, rowElm, rowData) {
                    $(this).html(
                        '<a href="/api/admin/inventuren/' + rowData.jobid + '">Inventur laden</a>'
                    )
                } }
        }
    };


    dataTable( '#InvUser', aUserInventuren, tableConf).orderby('jobid').render();
}(jQuery))
</script>
<?php include APP_VIEWS_PATH . '/global/partials/htmlfoot.php' ?>
