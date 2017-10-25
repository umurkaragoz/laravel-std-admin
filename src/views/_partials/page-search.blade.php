<form method="GET" class="pull-right">
    <div class="input-group input-group-sm page-search">
        <input type="text" name="filters[q]" value="{{ Input::get('filters.q') }}" class="form-control pull-right {{ Input::get('filters.q') ? 'warning' : '' }}">

        <div class="input-group-btn">
            <button type="submit" class="btn btn-default"><i class="fa fa-search"></i></button>
        </div>
    </div>
</form>