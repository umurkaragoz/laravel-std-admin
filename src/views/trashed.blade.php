@extends('admin._layouts.master')

@section('content')
    <div class="row std-admin">
        <div class="col-xs-12">
            <div class="box">
                <div class="box-header">
                    <h3 class="box-title" style="float:left;">Çöp Kutusu</h3>
                    <div class="box-tools trashed">
                        <div class="pull-left">
                            <a href="{{ module()->route('index') }}" class="back-btn btn btn-primary"><i class="fa fa-arrow-left"></i>&nbsp; Geri Dön</a>
                        </div>
                        @include('std-admin::_partials.page-search')
                    </div>
                </div>
                <!-- /.box-header -->
                <div class="box-body table-responsive no-padding">
                    @if(count($rows))
                        <table class="table table-hover">
                            <thead>
                            <tr>
                                @foreach($headers as $header)
                                    <th>{{ $header }}</th>
                                @endforeach
                                <th>İşlem</th>
                            </tr>
                            </thead>
                            <tbody>

                            @foreach($rows as $row)
                                <tr>
                                    @foreach($row as $column)
                                        <td>{!! $column !!}</td>
                                    @endforeach
                                    <td>
                                        <button class="btn btn-warning deletable" data-url="{{ module()->route('restore', $row['id']) }}" data-method="GET">
                                            <i class="fa fa-recycle"></i>&nbsp; Geri Yükle
                                        </button>
                                    </td>
                                </tr>
                            @endforeach

                            </tbody>
                        </table>
                    @else
                        <div class="col-md-12">
                            <div class="callout callout-warning">
                                @if(Input::get('q'))
                                    <h5>Aradığınız kritere uygun sonuç bulunamadı.</h5>
                                @else
                                    <h5>Çöp kutusu boş.</h5>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection