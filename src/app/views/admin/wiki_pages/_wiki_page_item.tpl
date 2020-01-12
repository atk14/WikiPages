<tr>
	<td>{a action="detail" name=$wiki_page->getName()}{$wiki_page->getName()}{/a}</td>
	<td>{$wiki_page->getTitle()}</td>
	<td>{if $wiki_page->getUpdatedAt()}{$wiki_page->getUpdatedAt()|format_datetime}{else}{$wiki_page->getCreatedAt()|format_datetime}{/if}</td>
</tr>
