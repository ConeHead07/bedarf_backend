<!-- div class="ui inverted labeled icon left inline vertical sidebar menu" -->
<div class="ui vertical labeled xicon inverted sidebar menu left">
    <a class="item" href="/api/admin/import/">
        <i class="upload icon left"></i> Neuer Import
    </a>
    <a class="item" href="/api/admin/import/listImports">
        <i class="list icon left"></i> Importliste
    </a>
    <?php /* <a class="item" href="/api/admin/objektbuch">
        <i class="book icon left"></i> Objektbücher
    </a> */ ?>
    <a class="item" href="/api/admin/inventuren/">
        <i class="warehouse icon left"></i> Inventuren
    </a>
    @can('list user')
    <a class="item" href="/api/admin/mandanten/">
        <i class="warehouse icon left"></i> Mandanten
    </a>
    <a class="item" href="/api/admin/user/">
        <i class="users icon left"></i> Benutzer
    </a>
    @endcan
    <a class="item" href="/api/admin/login/logout">
        <i class="sign out icon left"></i> Abmelden
    </a>
    <span class="item">
        me {{ $me }}
    </span>
    <span class="item">
        authUser {{ $authUser->name }}
    </span>
</div>
