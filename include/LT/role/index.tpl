{$PAGE_TITLE = "User Roles"}
{tpl /header}

<div class="portlet light">
    <div class="portlet-title">
        <div class="caption">
            <i class="fa fa-group"></i>
            <span class="caption-subject bold uppercase"> {{All Roles}}</span>
            <span class="caption-helper"></span>
        </div>
        <div class="actions">
            <a href="{url edit}" class="btn btn-circle btn-default">
                <i class="fa fa-plus"></i> {{Add}} </a>
            <a class="btn btn-circle btn-icon-only btn-default fullscreen" href="javascript:;" data-original-title="" title=""> </a>
        </div>
    </div>
    <div class="portlet-body">
        <div class="table-responsive">
            <table class="table table-condensed table-striped table-bordered table-hover">
                <thead>
                    <tr>
                        <th class="text-center col-md-4">{{Name}}</th>
                        <th class="text-center col-md-6">{{Description}}</th>
                        <th class="text-center col-md-2"></th>
                    </tr>
                </thead>
                <tbody>
                    {foreach $roles $_role}
                        <tr>
                            <td>
                                {$_role['name']}<br />
								<small class="text-muted">#{$_role['id']}</small>
                            </td>
                            <td>
                                {$_role['description']}
                            </td>
                            <td>
                                <a href="{url edit ['id' => $_role['id']]}" class="btn btn-primary">
                                    <i class="fa fa-pencil"></i>
                                </a>
                                <a href="{url edit ['id' => $_role['id']]}" class="btn btn-danger">
                                    <i class="fa fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    {/foreach}
                </tbody>
            </table>
        </div>
    </div>
</div>

{tpl /footer}
