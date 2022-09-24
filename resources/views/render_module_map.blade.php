@if(!empty($map))
        {!! $shim !!}
        <script type="importmap">
            @json(['imports'=> $map])

        </script>
@endif
