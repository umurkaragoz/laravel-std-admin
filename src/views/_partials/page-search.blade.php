<form method="GET" class="pull-right">
    <div class="input-group input-group-sm page-search">
        <input type="text" name="q" value="{{ Input::get('q') }}" class="form-control pull-right {{ Input::get('q') ? 'warning' : '' }}">

        <div class="input-group-btn">
            <button type="submit" class="btn btn-default"><i class="fa fa-search"></i></button>
        </div>
    </div>
</form>