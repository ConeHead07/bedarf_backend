<?php
include APP_VIEWS_PATH . '/global/partials/htmlhead.php';

?>
<style>
    th.col-filter .col-filter-control {
        width:100%;
    }
</style>

<script>
    var aUserInventuren = <?= json_encode($tplVars->aRows, JSON_PRETTY_PRINT) ?> || [];
</script>

<table id="InvUser" class="ui blue celled padded table">
</table>

<script>

    var counter = (function() {
        var count = 0;
        return { nextId: function() { return ++count; }};
    })();

    var tableConf = {
        data: aUserInventuren,
        key: 'jobid',
        rownumbers: true,
        colfilters: true,
        title: 'Benutzer-Inventuren',
        fields: {
            // Barcode: { name: 'Barcode', colspan: 4, formatter: function(val, colname, rowElm, rowData) {
            //     var bc = rowData.Barcode;
            //     var toggleHogiFont = 'libre';
            //     if (0) toggleHogiFont = counter.nextId() % 2 ? 'hogi' : 'libre';
            //     $( this )
            //         .html('')
            //         .append(
            //             $("<div/>").attr("data-code", bc).addClass(toggleHogiFont).addClass('bc')
            //                 .append( $("<span/>").addClass("bc-128").text( toggleHogiFont !== 'hogi' ? bc128b.get(bc) : bc128b.hogi(bc) ) )
            //                 .append( $("<span/>").addClass("bc-text").text(bc ) )
            //         )
            //         .append( $("<div/>").text( rowData.Etage ) )
            //         .append( $("<div/>").text( rowData.Raum ) )
            // }},
            jobid: { name: 'jobid' },
            name: { name: 'Angelegt von' },
            // mid: { name: 'mid' },
            Mandant: { name: 'Mandant' },
            Titel: { name: 'Inventur' },
            created_at: { name: 'Angelegt am', formatter: function(val, colname, rowElm, rowData) {
                var d = new Date(val);
                var s = moment(d).format('DD.MM.YYYY');
                $(this).html( s );
            } },
            x: { name: 'aktion', formatter: function(val, colname, rowElm, rowData) {
                    $(this).html(
                        '<a href="/api/admin/inventuren/' + rowData.jobid + '">Inventur</a>'
                    )
                } }
        }
    };
</script>
<script src="/assets/jslibrary/myDataTable.js"></script>
<script>
    // dataTable( '#InvUser', aUserInventuren, tableConf).orderby('jobid').render();

    $('#InvUser').myDataTable(tableConf);

</script>
<?php include APP_VIEWS_PATH . '/global/partials/htmlfoot.php' ?>
