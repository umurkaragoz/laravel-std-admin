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
                        @yield('table')
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