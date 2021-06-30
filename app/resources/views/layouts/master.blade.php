<!DOCTYPE html>
    <html>
    <!-- Stored in resources/views/layouts/master.blade.php -->
    <head>
        @include('global.partials.head-blade')
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
            .ui.top.attached.tabular.menu {
                flex-wrap: wrap;
            }
            .ui.attached.tab.segment {
                overflow:auto;
            }

            .full-width,
            ui.full-width,
            ui.container.full-width {
                width: 100%;
                min-width: initial;
                max-width: none;
            }
            @media only screen and (min-width: 1200px) {
                .ui.ui.ui.container.full-width:not(.fluid) {
                    width: 100%;
                }
                .ui.ui.ui.container.full-width {
                    width: 100%;
                    min-width: initial;
                    max-width: none;
                }

                .full-width,
                ui.full-width,
                ui.container.full-width {
                    width: 100%;
                    min-width: initial;
                    max-width: none;
                }
            }
            @media only screen and (min-width: 900px) {
                .ui.ui.ui.container.full-width:not(.fluid) {
                    width: 100%;
                }
                .ui.ui.ui.container.full-width {
                    width: 100%;
                    min-width: initial;
                    max-width: none;
                }

                .full-width,
                ui.full-width,
                ui.container.full-width {
                    width: 100%;
                    min-width: initial;
                    max-width: none;
                }
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
        @include( 'global.partials.menu-blade')
        <div class="pusher">
            <div class="full height">
                <div class="article">
                    <div class="main ui vertical container segment full-width">

<div class="container full-width">
    @yield('content')
</div>

                    </div><!-- CLOSE main.ui.container -->

                    @include('global.partials.foot-blade')
                </div><!-- CLOSE article -->
            </div><!-- CLOSE full.height -->
        </div><!-- CLOSE div.pusher -->
    </div><!-- CLOSE .ui.bottom.attached.segment.mainwrapper -->
</div><!-- CLOSE .context.mainsite -->
</body>
    <script>
        if (0) $('.context.mainsite .ui.sidebar')
            .sidebar({
                context: $('.context.mainsite .bottom.segment')
            })
            .sidebar('attach events', '.context.mainsite .menu .item')
        ;

        if (1) $('.mainsite .ui.sidebar')
            .sidebar({
                context: $('.mainsite .mainwrapper')
            })
            .sidebar('attach events', '.mainsite .menu #btnPusher')
        ;
    </script>
</html>
