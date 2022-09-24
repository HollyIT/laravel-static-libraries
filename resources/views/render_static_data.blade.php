<?php
/**
 * @var array $data
 */
?>
@if(!empty($data))
    <script>
    @foreach($data as $key=>$value)
        var {!! $key !!}=@json($value);
    @endforeach
    </script>
@endif
