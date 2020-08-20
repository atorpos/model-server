{$PAGE_TITLE = 'Role'}
{tpl /header}

<div class="portlet light">
    <div class="portlet-title">
        <div class="caption">
            <i class="fa fa-group font-red-sunglo"></i>
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

                {foreach $permissionsForm->elements $element}
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
{tpl /footer}