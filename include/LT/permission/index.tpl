{$PAGE_TITLE = "Permissions"}
{tpl /header}

<div class="portlet light">
    <div class="portlet-title">
        <div class="caption">
            <i class="fa fa-cubes"></i>
            <span class="caption-subject bold uppercase"> {{All Permissions}}</span>
            <span class="caption-helper"></span>
        </div>
        <div class="actions">
			{if allow 223.197.135.143}
				<a href="{url edit}" class="btn btn-circle btn-default">
					<i class="fa fa-plus"></i> {{Add}} 
				</a>
			{else}
				{{IP Restricted Area}}
			{/if}
			{php}
echo 'Hello'
			{/php}
            <a class="btn btn-circle btn-icon-only btn-default fullscreen" href="javascript:;" data-original-title="" title=""> </a>
        </div>
    </div>
    <div class="portlet-body">
        <div class="table-responsive">
            <table class="table table-condensed table-striped table-bordered table-hover">
                <thead>
                    <tr>
                        <th class="text-center col-md-3">{{Permission Tree}}</th>
                        <th class="text-center col-md-3">{{Permission Key}}</th>
                        <th class="text-center col-md-4">{{Description}}</th>
                        <th class="text-center col-md-2"></th>
                    </tr>
                </thead>
                <tbody>
                    {foreach $permissions $_permission}
                        <tr>
                            <td>
                                {$_permission['name']|html} <small class="text-muted">(#{$_permission['id']})</small>
                            </td>
                            <td>

								<small class="text-muted">{$_permission['key']}</small>
                            </td>
                            <td>
								<strong>{$_permission['label']}</strong><br />
                                {$_permission['description']}
                            </td>
                            <td>
                                <a href="{url edit ['id' => $_permission['id']]}" class="btn btn-primary">
                                    <i class="fa fa-pencil"></i>
                                </a>
                            </td>
                        </tr>
                    {/foreach}
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="portlet light">
    <div class="portlet-title">
        <div class="caption">
            <i class="fa fa-cubes"></i>
            <span class="caption-subject bold uppercase"> {{Permission Class}}</span>
            <span class="caption-helper"></span>
        </div>
        <div class="actions">

        </div>
    </div>
    <div class="portlet-body">
        <pre><code class="language-php">
class Permission { 

				{foreach $permissions $_permission}

    const {$_permission['key']} =&gt; '{$_permission['key']}';
				{/foreach}
}
		</code></pre>
    </div>
</div>


{tpl /footer}
