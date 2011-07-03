<div class="main">	
	<form action="<?=url_for("admin/login")?>" method="POST">
		<fieldset>

			<legend>Please Login</legend>
			<div>
				<label for="email">Email</label> 
				<input type="text" name="email" id="email" size="30" class="field required email" />
			</div>
			<div>
				<label for="password">Password</label>
				<input type="password" name="password" id="password" size="30" class="field required" />
			</div>

		</fieldset>	
		<div class="submit"><button type="submit">Submit</button></div>		
	</form>	
</div>