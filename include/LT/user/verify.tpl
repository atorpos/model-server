{tpl /user/header}
{if $error}
	<div class="alert alert-danger">
		<button class="close" data-close="alert"></button>
		<span>{$error}</span>
	</div>
{/if}
{if !$fatal}
<div class="row">
	<div class="col-xs-6">
		<input class="form-control form-control-solid placeholder-no-fix form-group" type="text" autocomplete="off" placeholder="Username" value="{$username}" readonly/>
	</div>
	<div class="col-xs-6">
		
	</div>
</div>
<div class="row">
	<div class="col-xs-6">
		<input class="form-control form-control-solid placeholder-no-fix form-group" type="password" autocomplete="off" placeholder="Password" name="password" id='password'/>
	</div>
	<div class="col-xs-6">
		<input class="form-control form-control-solid placeholder-no-fix form-group" type="password" autocomplete="off" placeholder="Re-type Password" id='retype-password' name='retype-password'/>
	</div>
</div>
<div class="row">
	<div class="col-sm-12 text-right">
		<button class="btn green" type="submit">Register</button>
	</div>
</div>
<script type="text/javascript">
	document.getElementById("password").focus();
</script>
{/if}
{tpl /user/footer}