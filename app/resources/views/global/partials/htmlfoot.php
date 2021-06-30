
                    </div><!-- CLOSE main.ui.container -->

                    <?php include __DIR__ . '/foot.php' ?>
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
