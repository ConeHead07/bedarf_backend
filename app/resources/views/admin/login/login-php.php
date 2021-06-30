
<!DOCTYPE html>
<html>
<head>
    <!-- Standard Meta -->
    <?php include APP_VIEWS_PATH . '/global/partials/head.php' ?>

    <style type="text/css">
        body {
            background-color: #DADADA;
        }
        body > .grid {
            height: 100%;
        }
        .image {
            margin-top: -100px;
        }
        .column {
            max-width: 450px;
        }
    </style>
    <script>
        $(document)
            .ready(function() {
                var redirectUrl = '/api/admin/inventuren/';
                <?php if (!empty($tplVars['redirectUrl'])): ?>
                redirectUrl = <?= json_encode($tplVars['redirectUrl']) . ";\n" ?>
                <?php endif; ?>
                console.log("#24 document.ready");
                $('.ui.form')
                    .form({
                        fields: {
                            email: {
                                identifier  : 'email',
                                rules: [
                                    {
                                        type   : 'empty',
                                        prompt : 'Please enter your e-mail'
                                    },
                                    {
                                        type   : 'email',
                                        prompt : 'Please enter a valid e-mail'
                                    }
                                ]
                            },
                            password: {
                                identifier  : 'password',
                                rules: [
                                    {
                                        type   : 'empty',
                                        prompt : 'Please enter your password'
                                    },
                                    {
                                        type   : 'length[5]',
                                        prompt : 'Your password must be at least 6 characters'
                                    }
                                ]
                            }
                        }
                    })
                ;

                console.log("#58");
                function getCookiesAsJson() {
                    var cookies = {};
                    document.cookie.split(';').forEach(function(val) {
                        var cKeyVal = val.split(':');
                        cookies[ cKeyVal.slice(0, 1).trim() ] = cKeyVal.slice(1).join(';').trim();
                    });
                    return cookies;
                }

                console.log("#68");
                $('form#login');
                console.log("#70 $(#submit).length: " + $('#submit').length);
                $('#submit').click( function(e) {
                    e.preventDefault();

                    console.log('clicked button for submit')
                    var data = {
                        email: $("#email").val(),
                        password: $("#pw").val(),
                        returnSecureToken: true,
                        clientDeviceId: -1,
                        redirectUrl
                    };

                    $.ajax({
                        type: "POST",
                        url: '/api/admin/login/auth',
                        data: JSON.stringify(data),
                        contentType: 'json',
                        success: function(data) {
                            console.log("auth - response: ", { data });
                            // alert('process login response!');
                            // var cookies = getCookiesAsJson();
                            console.log(document.cookie);

                            if (data.access_token && data.token_type && data.token_type === 'bearer') {
                                var expDate = new Date();
                                expDate.setSeconds( expDate.getSeconds() + data.expires_in);
                                var authCookie = 'Authorization=Bearer ' + data.access_token + '; ' +
                                'expires=' + expDate.toUTCString() + '; ' +
                                'path=/api/admin/';
                                console.log( {authCookie});
                                document.cookie = authCookie;
                                if ('gotoUrl' in data) {
                                    document.location.href = data.gotoUrl;
                                } else {
                                    document.location.href = redirectUrl;
                                }
                                // alert('process login: successful!');
                            } else {
                                console.error('cannot set auth-cookie', { data });
                                // alert('process login: error!');
                            }

                        },
                        failure: function(errMsg) {
                            console.error(errMsg);
                            alert('login invalid!');
                        },
                        dataType: 'json'
                    });
                });

            })
        ;
    </script>
</head>
<body>

<div class="ui middle aligned center aligned grid">
    <div class="column">
        <h2 class="ui teal image header">
            <!-- <img src="assets/images/logo.png" class="image"> -->
            <div class="content">
                Log-in to your account
            </div>
        </h2>
        <form id="login" class="ui large form">
            <div class="ui stacked segment">
                <div class="field">
                    <div class="ui left icon input">
                        <i class="user icon"></i>
                        <input type="text" id="email" name="email" placeholder="E-mail address">
                    </div>
                </div>
                <div class="field">
                    <div class="ui left icon input">
                        <i class="lock icon"></i>
                        <input type="password" id="pw" name="password" placeholder="Password">
                    </div>
                </div>
                <div id="submit" class="ui fluid large teal xsubmit button">Login</div>
            </div>

            <div class="ui error message"></div>

        </form>

        <!-- <div class="ui message">
            New to us? <a href="#">Sign Up</a>
        </div> -->
    </div>
</div>

</body>

</html>
