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
    var aUserInventuren = <?= json_encode($tplVars->aRows, JSON_PRETTY_PRINT) ?> || [];
</script>

<table id="InvUser" class="ui blue celled padded table"></table>

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
            jobid: {
                name: 'jobid'
            },
            name: {
                name: 'Angelegt von'
            },
            // mid: { name: 'mid' },
            Mandant: {
                name: 'Mandant'
            },
            Titel: {
                name: 'Inventur'
            },
            EnthaeltKunst: {
                name: 'Kunst',
                hidden: true,
                editable: false
            },
            KunstKategorien: {
                name: 'Kunst-Ktg',
                hidden: true,
                editable: false
            },
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
@endsection
