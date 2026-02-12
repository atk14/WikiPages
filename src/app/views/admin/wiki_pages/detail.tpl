{if $current_revision!=$latest_revision}
	<meta name="robots" content="noindex,noarchive">
{/if}

<div class="row">

	<div class="col-sm-8">

		<div class="wiki">
		{dropdown_menu clearfix=false}
			{if $wiki_page->isEditableBy($logged_user)}
				{a action="edit" id=$wiki_page}{!"edit"|icon} {t}Edit{/t}{/a}
			{/if}
			{if $wiki_page->isDeletableBy($logged_user)}
				{a action="destroy" id=$wiki_page}{!"remove"|icon} {t}Delete{/t}{/a}
			{/if}
		{/dropdown_menu}
	
		{highlight_search_query}
		<h1>{$wiki_page->getTitle()}</h1>

		{!$content}
		{/highlight_search_query}
		</div>

	</div>

	<div class="col-sm-4">

		{render partial="sidebar"}

	</div>

</div>
