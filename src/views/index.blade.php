@extends('std-admin::index-container')

@section('table')
    <table class="table table-hover table-striped">
        <thead>
        <tr>
            @foreach($headers as $header)
                <th>{{ $header }}</th>
            @endforeach
            @if(module('functions.delete'))
                <th></th>
            @endif
        </tr>
        </thead>
        <tbody class="sortable" data-sortable-url="{{ mRoute('sorting') }}">
        @foreach($rows as $row)
            <tr data-id="{{ $row->id }}" data-click-url="{{ mRoute('edit', $row->id) }}">
                @foreach($columns as $column => $type)
                    @if($type == 'sorting')
                        <td class="handle">
                            <span> <i class="fa fa-ellipsis-v"></i> <i class="fa fa-ellipsis-v"></i></span>
                            {!! $row->$column !!}
                        </td>
                    @elseif($type == 'string')
                        <td>{!! $row->$column !!}</td>
                    @elseif(starts_with($type, 'toggle'))
                        <?php
                        // bootstrap toggle has 4 parameters which can be passed along the column type. Thse and sytax is like following;
                        // toggle,On text, Off text,on-style,off-style
                        // and defaults are;
                        // toggle, trans:ui.on-default, trans:ui.off-default, success, default
                        $opts = explode(',', $type);
                        ?>
                        <td>
                            <input type="checkbox" class="update-on-change"
                                   {{ $row->$column ? 'checked' : '' }}
                                   data-toggle="toggle"
                                   data-on="{{ array_get($opts, 1, trans('std-admin::ui.toggle.on-default')) }}"
                                   data-off="{{ array_get($opts, 2, trans('std-admin::ui.toggle.off-default')) }}"
                                   data-onstyle="{{ array_get($opts, 3, 'success') }}"
                                   data-offstyle="{{ array_get($opts, 4, 'default') }}"
                                   data-size="small"
                                   data-url="{{ mRoute('editable') }}"
                                   data-pk="{{ $row->id }}"
                                   data-name="{{ $column }}"
                            >
                        </td>
                    @endif
                @endforeach
                @if(module('functions.delete'))
                    <td style="width: 33px;">
                        <button class="btn btn-danger deletable" data-url="{{ mRoute('destroy', $row->id) }}">
                            <i class="fa fa-times"></i>
                        </button>
                    </td>
                @endif
            </tr>
        @endforeach
        </tbody>
    </table>
@endsection