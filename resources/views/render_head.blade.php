<?php
/**
 * @var \HollyIT\StaticLibraries\LibrariesManager $libraries
 */
?>
@foreach($libraries->getStyleSheets() as $stylesheet)
    {!! $stylesheet !!}
@endforeach

@include('static-libraries::render_module_map', ['map' => $libraries->getModuleMap(), 'shim' => $libraries->getModuleMapShim()])
@include('static-libraries::render_static_data', ['data' => $libraries->getData()])

@foreach($libraries->getScripts(true) as $script)
    {!! $script !!}
@endforeach

