{assign attachments WikiAttachment::GetInstancesFor($wiki_page)}
{capture assign="clipboard_copy_btn"}<a href="#" class="iobject-copy-code btn btn-sm btn-outline-primary float-right" role="button" data-toggle="popover" data-trigger="focus" data-content="{t}Copied!{/t}" data-placement="top" tabindex="0">{!"copy"|icon} {t}Copy{/t}</a>{/capture}

<table class="table wiki-attachments">
	<thead>
		<tr>
			<th colspan="4">{t}Obrázky a přílohy{/t}</th>
		</tr>
	</thead>
	{foreach $attachments as $attachment}
		<tbody>
			<tr>
				<td rowspan="2" width="100">{a action="wiki_attachments/detail" id=$attachment->getId()}<img src="{link_to action="wiki_attachments/detail" id=$attachment->getId() format=thumbnail}" width="80" height="80" class="img-thumbnail">{/a}</td>
				<td>
					{a action="wiki_attachments/detail" id=$attachment->getId()}<strong>{$attachment->getFilename()}</strong>{/a}<br>
					<small>{$attachment->getMimeType()}</small>
					{if $attachment->getImageWidth()}
						<br><small>{$attachment->getImageWidth()}&times;{$attachment->getImageHeight()}</small>
					{/if}
				</td>
				<td nowrap="nowrap"><small>{$attachment->getFilesize()|format_bytes}</small></td>
				<td>{a_destroy controller="wiki_attachments" action="destroy" token=$attachment->getToken() _title="{t}smazat přílohu{/t}" _class="text-danger" _confirm="{t}Opravdu chcete smazat tuto přílohu?{/t}"}{!"remove"|icon}{/a_destroy}</td>
			</tr>
			<tr>
				<td colspan="3" style="border-top: none; padding-top: 0px;">
					<small>{t created_at=$attachment->getCreatedAt()|format_date user=$attachment->getCreatedByUser()}nahráno %1 uživatelem %2{/t}{if $attachment->getUpdatedAt()},
						{t updated_at=$attachment->getUpdatedAt()|format_date user=$attachment->getUpdatedByUser()}změněno %1 uživatelem %2{/t}
					{/if}
					</small>
				</td>
			</tr>
			{if $action=="edit"}
			<tr>
				<td colspan="4" style="border-top: none; padding-top: 0px;">
					{if $attachment->isImage()}
						<div class="iobject-code-wrap">
							<span class="badge-wrap"><small><span class="badge badge-success" title="{t}malý obrázek{/t}">{!"image"|icon}</span></small></span>
							<code class="iobject-code">![{$attachment->getFilename()}]({$attachment->getRelativeUrl("quarter")})</code>
							{!$clipboard_copy_btn}
						</div>
						<div class="iobject-code-wrap">
							<span class="badge-wrap"><span class="badge badge-success" title="{t}středně velký obrázek{/t}">{!"image"|icon}</span></span>
							<code class="iobject-code">![{$attachment->getFilename()}]({$attachment->getRelativeUrl("half")})</code>
							{!$clipboard_copy_btn}
						</div>
						<div class="iobject-code-wrap">
							<span class="badge-wrap"><big><span class="badge badge-success" title="{t}velký obrázek{/t}">{!"image"|icon}</span></big></span>
							<code class="iobject-code">![{$attachment->getFilename()}]({$attachment->getRelativeUrl("full")})</code>
							{!$clipboard_copy_btn}
						</div>
					{/if}
					<div class="iobject-code-wrap">
						<span class="badge-wrap"><span class="badge badge-primary" title="{if $attachment->isImage()}{t}odkaz na originální obrázek{/t}{else}{t}odkaz na přílohu{/t}{/if}">{!"link"|icon}</span></span>
						<code class="iobject-code">[{$attachment->getFilename()}]({$attachment->getRelativeUrl()})</code>
						{!$clipboard_copy_btn}
					</div>
				</td>
			</tr>
			{/if}
		</tbody>
	{/foreach}
</table>

<p>{a action="wiki_attachments/create_new" wiki_page_id=$wiki_page}{!"plus-circle"|icon} {t}nahrát obrázek nebo přílohu{/t}{/a}</p>
