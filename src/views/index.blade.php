@extends('admin._layouts.master')

@section('content')
    <div class="row">
        <div class="col-xs-12">
            <div class="box">
                <div class="box-header">
                    <h3 class="box-title" style="float:left;">{{ module()->trans('name') }}</h3>
                    <div style="float:left; margin-left:5px;">
                        @if(module('functions.create'))
                            <a href="{{ module()->route('create') }}" class="btn btn-success"> <i class="fa fa-plus"></i> </a>
                        @endif
                        @if(module('functions.delete') && module('functions.restore'))
                            <a href="{{ module()->route('trashed') }}" class="btn btn-warning"> <i class="fa fa-trash-o"></i> </a>
                        @endif
                    </div>
                    <div class="box-tools">
                        @include('std-admin::_partials.page-search')
                    </div>
                </div>
                <div class="box-body table-responsive no-padding">
                    @if(count($rows))
                        <table class="table table-hover table-striped">
                            <thead>
                            <tr>
                                @foreach($headers as $header)
                                    <th>{{ $header }}</th>
                                @endforeach
                                @if(module('functions.delete'))
                                    <th>İşlemler</th>
                                @endif
                            </tr>
                            </thead>
                            <tbody class="sortable" data-sortable-url="{{ module()->route('sorting') }}">
                            @foreach($rows as $row)
                                <tr data-id="{{ $row->id }}" data-click-url="{{ module()->route('edit', $row->id) }}">
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
                                                       data-on="{{ array_get($opts, 1, trans('std-admin/ui.toggle.on-default')) }}"
                                                       data-off="{{ array_get($opts, 2, trans('std-admin/ui.toggle.off-default')) }}"
                                                       data-onstyle="{{ array_get($opts, 3, 'success') }}"
                                                       data-offstyle="{{ array_get($opts, 4, 'default') }}"
                                                       data-size="small"
                                                       data-url="{{ module()->route('editable') }}"
                                                       data-pk="{{ $row->id }}"
                                                       data-name="{{ $column }}"
                                                >
                                            </td>
                                        @endif
                                    @endforeach
                                    @if(module('functions.delete'))
                                        <td style="width: 80px;">
                                            <div style="width:60px; float:left">
                                                <button class="btn btn-danger deletable" data-url="{{ module()->route('destroy', $row->id) }}">
                                                    <i class="fa fa-trash-o"></i>&nbsp; Sil
                                                </button>
                                            </div>
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="col-md-12">
                            <div class="callout callout-warning">
                                <p>Kayıt bulunamadı.</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection