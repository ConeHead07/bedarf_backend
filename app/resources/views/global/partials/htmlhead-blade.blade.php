<!DOCTYPE html>
<html>
<head>
    <!-- Standard Meta -->
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1">

    <link rel="image_src" type="image/jpeg" href="/images/logo.png" />

    <!-- Site Properities -->
    <title>{{ $title ?? 'Administration' }}</title>

    <meta name="description" content="merTens Inventory: Inventuren importieren, administrieren und exprotieren" />
    <meta name="keywords" content="html5, ui, library, framework, javascript" />
    <script src="/assets/jslibrary/moment.js"></script>

    @include('global.partials.barcode128-font-blade')
    @include('global.partials.hogicode128-font-blade')
    @include('global.partials.semantic-ui-blade')
    @include('global.partials.waitMe-blade')
    @include('global.partials.bootstrap-blade')

    <style>
        html,
        body {
            height: 100%;
            font-size: 14px;
        }
        a {
            color: #007bff;
            text-decoration: none;
            background-color: transparent;
        }
        .ui.menu.sidebar {
            width: 12rem;
        }
        .ui.vertical.menu .item > i.icon.left {
            width: 1.18em;
            float: left;
            margin-right: 0.5em;
            margin-left: 0;
        }
        .ui.attached.menu,
        .ui.attached.segment {
            width: 100%;
        }
    </style>
</head>
<body style="min-height:100%">
<div class="context mainsite" style="height:100%;min-height:100%">
    <div class="ui top attached menu mainmenu">
        <a id="btnPusher" class="item">
            <i class="sidebar icon"></i>
            Menu
        </a>
    </div>
    <div class="ui bottom attached segment mainwrapper" style="overflow: visible">
        <?php include __DIR__. '/menu.php' ?>

        <div class="pusher">
            <div class="full height">
                <div class="article">
                    <div class="main ui vertical container segment">

