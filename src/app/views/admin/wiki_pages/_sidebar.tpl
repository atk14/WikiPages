
		<h2 class="h1">{$wiki_name_humanized}</h2>

		{render partial="shared/search_form" form=$search_form}

		{if $wiki_page}

			<h3>{$wiki_page->getName()}</h3>

			<table class="table">
				<thead>
					<tr>
						<th colspan="3">{t}Revize{/t}</th>
					</tr>
				</thead>
				<tbody>
					{foreach $revisions as $revision}
						<tr{if $revision->getRevision()==$wiki_page->getRevision()} style="font-weight: bold;"{/if}>
							<td>
								{if $revision->getRevision()==$latest_revision}
									<a href="{link_to action="detail" name=$revision->getName()}">{$revision->getRevision()}</a></td>
								{else}
									<a href="{link_to action="detail" name=$revision->getName() revision=$revision->getRevision()}">{$revision->getRevision()}</a></td>
								{/if}
							<td>
								{if $revision->getUpdatedByUser()}
									{$revision->getUpdatedByUser()}
								{else}
									{$revision->getCreatedByUser()|default:$mdash}
								{/if}
							</td>
							<td>
								{if $revision->getUpdatedAt()}
									{$revision->getUpdatedAt()|format_datetime}
								{else}
									{$revision->getCreatedAt()|format_datetime}
								{/if}
							</td>
						</tr>
					{/foreach}
				</tbody>
			</table>

			{render partial="attachments"}


		{/if}
