{$PAGE_TITLE = "Admin Users"}
{tpl /header}

<div class="portlet light">
    <div class="portlet-title">
        <div class="caption">
            <i class="icon-users"></i>
            <span class="caption-subject bold uppercase"> {{All Users}}</span>
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
                        <th class="text-center col-md-6">{{Name}}</th>
                        <th class="text-center col-md-2">{{Role}}</th>
                        <th class="text-center col-md-2">{{Last Login Time}}</th>
                            {*<th class="text-center col-md-1">{{Status}}</th>*}
                        <th class="text-center col-md-2"></th>
                    </tr>
                </thead>
                <tbody>
                    {foreach $users $_user}
                        <tr>
                            <td class="">
                                {$_user['name']|lower|ucwords}
                                {if $_user['username']}
                                    <span class="text-muted small" >&lt;<a href="mailto:{$_user['username']|lower}" >{$_user['username']|lower}</a>&gt;</span>
                                {/if}
                                {if $_user['verified_time']}
                                    <i class="glyphicon glyphicon-ok-sign font-green-jungle tooltips" 
                                       data-original-title="{{Verified:}} {$_user['verified_time']|datetime}" ></i>
                                {else}
                                    <i class="glyphicon glyphicon-question-sign font-blue tooltips"
                                       data-original-title="{{Email not yet verified}}"></i>
                                {/if}
                                <br />
                                <span class="badge badge-default text-uppercase">{{UID: }}{$_user['id']}</span>
                                {if $_user['is_developer']}
                                    <span class="badge badge-default">{{DEVELOPER}}</span>
                                {/if}
                            </td>
                            <td class="text-center">
								{$_user['role_name']}
                            </td>
                            <td class="text-center">
                                {if $_user['last_login_time']}
                                    {$_user['last_login_time']|datetime}
                                {/if}
                            </td>
                            {*<td class="text-center">
                            {if $_user['enabled']}
                            <span class="font-green-jungle">{{Enabled}}</span>
                            {else}
                            <span class=" font-red">{{Disabled}}</span>
                            {/if}
                            </td>*}
                            <td class="">
                                <a href="{url edit ['id' => $_user['id']]}" class="btn btn-primary btn-sm">
                                    <i class="fa fa-pencil"></i>
                                </a>
                                <a href="{url history ['id' => $_user['id']]}" class="btn btn-primary btn-sm">
                                    <i class="fa fa-history"></i>
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
