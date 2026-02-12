<h1>{$page_title}</h1>

{render partial="shared/search_form"}

{if $finder->isEmpty()}

	<p>{t}Nebyla nalezena ani jedna stránka.{/t}</p>

{else}

	<table class="table">
		<thead>
			<tr>
				{sortable key=name}<th>{t}Stránka{/t}</th>{/sortable}
				<th>{t}Titulek{/t}</th>
				{sortable key=updated_at}<th>{t}Datum poslední změny{/t}</th>{/sortable}
				<th>{t}Autor poslední změny{/t}</th>
			</tr>
		</thead>

		<tbody>
			{render partial="wiki_page_item" from=$finder->getRecords()}
		</body>
	</table>

{/if}


