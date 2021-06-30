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

@include('global.partials.myDataTables')
