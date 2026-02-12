<tr>
	<td>{highlight_search_query}{a action="detail" name=$wiki_page->getName() search=$params->getString("search")}{$wiki_page->getName()}{/a}{/highlight_search_query}</td>
	<td>{highlight_search_query}{$wiki_page->getTitle()}{/highlight_search_query}</td>
	<td>{if $wiki_page->getUpdatedAt()}{$wiki_page->getUpdatedAt()|format_datetime}{else}{$wiki_page->getCreatedAt()|format_datetime}{/if}</td>
	<td>{$wiki_page->getUpdatedByUser()|default:$wiki_page->getCreatedByUser()|default:$mdash}</td>
</tr>
<tr>
	<td colspan="4" style="border-top: none;">
		{highlight_search_query}
		<small>{$wiki_page->getContent()|wiki_markdown|strip_html|truncate:500}</small>
		{/highlight_search_query}
	</td>
</tr>
