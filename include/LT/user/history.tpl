{$PAGE_TITLE = $user['name'] . ' - User History'}
{tpl /header}

<div class="row">
	<div class="col-md-12">
		<div class="portlet light bordered">
			<div class="portlet-title">
				<div class="caption">
					<i class="fa fa-history font-red-sunglo"></i>
					<span class="caption-subject font-red-sunglo bold uppercase">Authenticate History ({$user.username})</span>
					<span class="caption-helper">recent records</span>
				</div>
				<div class="tools">
				</div>
			</div>
			<div class="portlet-body">
				<div class="table-responsive">
					<table class="table table-condensed table-striped table-bordered table-hover">
						<thead>
							<tr>
								<th class="center col-md-1">Time</th>
								<th class="center col-md-1">IP</th>
								<th class="center col-md-4">User agent</th>
								<th class="center col-md-4">URL</th>
								<th class="center col-md-1">Result</th>
								<th class="center col-md-1">Message</th>
							</tr>
						</thead>
						<tbody>
							{foreach $history $record}
								<tr>
									<td class="center">{$record["updated_time"]|datetime}</td>
									<td class="center">{$record.ip}</td>
									<td class="">{$record.user_agent}</td>
									<td class="">{$record.url}</td>
									<td class="center">{if $record["result"]==1}Success{else}Fail{/if}</td>
									<td class="center">{$record.message}</td>
								</tr>
							{/foreach}
						</tbody>
					</table>
				</div>
			</div>

		</div>
	</div>
</div>

{tpl /footer}