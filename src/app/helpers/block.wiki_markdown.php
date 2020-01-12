<?php
require_once(__DIR__ . "/modifier.wiki_markdown.php");

/**
 * Block markdown helper
 *
 *	{wiki_markdown}
 *	# Hi there!
 *
 *	Welcome to our brand new website.
 *	{/wiki_markdown}
 */
function smarty_block_wiki_markdown($params,$content,$template,&$repeat){
	if($repeat){ return; }

	return smarty_modifier_markdown($content);
}
