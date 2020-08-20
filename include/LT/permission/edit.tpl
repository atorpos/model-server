{$PAGE_TITLE = 'Permission Object'}
{tpl /header}

<div class="alert alert-warning">
	<strong>Warning!</strong> Make sure you know what you are doing.
</div>
<div class="portlet light">
    <div class="portlet-title">
        <div class="caption">
            <i class="fa fa-cube font-red-sunglo"></i>
            <span class="caption-subject font-red-sunglo bold uppercase">{{General}}</span>
            <span class="caption-helper"></span>
        </div>
        <div class="tools"></div>
    </div>
    <div class="portlet-body form">
        <!-- BEGIN FORM-->
        <form action="{url}" method="get" class="form-horizontal ajax" role="form">
            <div class="form-body">
                {foreach $form->elements $element}
                    {$element|html}
                {/foreach}
            </div>
            <div class="form-actions">
                <div class="row">
                    <div class="col-md-6 col-md-offset-2">
                        <button type="submit" class="btn btn-primary"> {{Save}} </button>
                        <a href="{url index}" class="btn btn-default"> {{Cancel}} </a>
                    </div>
                </div>
            </div>
        </form>
        <!-- END FORM-->
    </div>
</div>


<div class="portlet light">
    <div class="portlet-title">
        <div class="caption">
            <i class="fa fa-cube font-red-sunglo"></i>
            <span class="caption-subject font-red-sunglo bold uppercase">{{Delete this permission object}}</span>
            <span class="caption-helper"></span>
        </div>
        <div class="tools"></div>
    </div>	

    <div class="portlet-body">
		<a href="javascript:alert('todo');" class="btn btn-danger">
			<i class="fa fa-trash"></i> {{Delete}}
		</a>
	</div>
</div>
{tpl /footer}