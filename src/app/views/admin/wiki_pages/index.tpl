<h1>{button_create_new}{t}Vytvořit novou stránku{/t}{/button_create_new} {$page_title}</h1>

{render partial="shared/search_form"}

{if $finder->isEmpty()}

	<p>{t}Nebyla nalezena ani jedna stránka.{/t}</p>

{else}

	<table class="table table-striped">
		<thead>
			<tr>
				{sortable key=name}<th>{t}Stránka{/t}</th>{/sortable}
				<th>{t}Titulek{/t}</th>
				{sortable key=updated_at}<th>{t}Datum poslední změny{/t}</th>{/sortable}
			</tr>
		</thead>

		<tbody>
			{render partial="wiki_page_item" from=$finder->getRecords()}
		</body>
	</table>

{/if}


