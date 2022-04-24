{assign attachments WikiAttachment::GetInstancesFor($wiki_page)}

<table class="table">
	<thead>
		<tr>
			<th colspan="4">{t}Obrázky a přílohy{/t}</th>
		</tr>
	</thead>
	{foreach $attachments as $attachment}
		<tbody>
			<tr>
				<td rowspan="2">{a action="wiki_attachments/detail" id=$attachment->getId()}<img src="{link_to action="wiki_attachments/detail" id=$attachment->getId() format=thumbnail}" width="80" height="80">{/a}</td>
				<td>
					{a action="wiki_attachments/detail" id=$attachment->getId()}<strong>{$attachment->getFilename()}</strong>{/a}<br>
					<small>{$attachment->getMimeType()}</small>
					{if $attachment->getImageWidth()}
						<br><small>{$attachment->getImageWidth()}&times;{$attachment->getImageHeight()}</small>
					{/if}
				</td>
				<td>{$attachment->getFilesize()|format_bytes}</td>
				<td>{a_destroy controller="wiki_attachments" action="destroy" token=$attachment->getToken() _confirm="{t}Opravdu chcete smazat tuto přílohu?{/t}"}{!"remove"|icon}{/a_destroy}</td>
			</tr>
			<tr>
				<td colspan="3" style="border-top: none;">
					<small>{t created_at=$attachment->getCreatedAt()|format_date user=$attachment->getCreatedByUser()}nahráno %1 uživatelem %2{/t}{if $attachment->getUpdatedAt()},
						{t updated_at=$attachment->getUpdatedAt()|format_date user=$attachment->getUpdatedByUser()}změněno %1 uživatelem %2{/t}
					{/if}
					</small>
				</td>
			</tr>
			<tr>
				<td colspan="4" style="border-top: none;">
					<span class="badge badge-primary" title="{if $attachment->isImage()}{t}odkaz na originální obrázek{/t}{else}{t}odkaz na přílohu{/t}{/if}">{!"link"|icon}</span>
					<small><code>[{$attachment->getFilename()}]({$attachment->getRelativeUrl()})</code></small>
					{if $attachment->isImage()}
						<br>
						<small><span class="badge badge-success" title="{t}malý obrázek{/t}">{!"image"|icon}</span></small>
						<small><code>![{$attachment->getFilename()}]({$attachment->getRelativeUrl("quarter")})</code></small>
						<br>
						<span class="badge badge-success" title="{t}středně velký obrázek{/t}">{!"image"|icon}</span>
						<small><code>![{$attachment->getFilename()}]({$attachment->getRelativeUrl("half")})</code></small>
						<br>
						<big><span class="badge badge-success" title="{t}velký obrázek{/t}">{!"image"|icon}</span></big>
						<small><code>![{$attachment->getFilename()}]({$attachment->getRelativeUrl("full")})</code></small>
					{/if}
				</td>
			</tr>
		</tbody>
	{/foreach}
</table>

<p>{a action="wiki_attachments/create_new" wiki_page_id=$wiki_page}{!"plus-circle"|icon} {t}nahrát přílohu{/t}{/a}</p>
