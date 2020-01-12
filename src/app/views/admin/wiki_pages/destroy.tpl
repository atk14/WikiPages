<h1>{$page_title}</h1>

<p>
	{t name=$wiki_page->getName()|h escape=no}Do you really want to delete page <em>%1</em>?{/t}
</p>

{render partial="shared/form"}
