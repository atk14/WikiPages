<div class="row">

	<div class="col-sm-8">


		<h1 class="text-muted">{$name}</h1>

		<p>
			{t name=$name|h escape=no}Stránka <em>%1</em> neexistuje.{/t}
		</p>

		<p>
			{a action="create_new" name=$name _class="btn btn-default"}{t}Vytvořit stránku?{/t}{/a}
		</p>

	</div>

	<div class="col-sm-4">

		{render partial="sidebar"}

	</div>

</div>
