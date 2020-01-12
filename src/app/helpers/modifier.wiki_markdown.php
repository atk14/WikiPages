<?php
/**
 * Markdown modifier
 *
 *	{$source_text|wiki_markdown}
 *
 * In an ATK14 application, you may use nofilter or the exclamation mark
 *
 *	{$source_text|wiki_markdown nofilter}
 *	{!$source_text|wiki_markdown}
 */
function smarty_modifier_wiki_markdown($text){
	
	$markdown = new DrinkMarkdown(array(
		"table_class" => "table",
		"html_purification_enabled" => false,
		"iobjects_processing_enabled" => true,
		"urlize_text" => true,
	));

	$markdown->appendPostFilter(new WikiPageLinksCreatorFilter());

	$text = $markdown->transform($text);

	return $text;
}

class WikiPageLinksCreatorFilter extends DrinkMarkdownFilter {

	function filter($html,$transformator){
		$replaces_back = array();

		$this->hideSomething('/<code\b[^>]*?'.'>.*?<\/code>/si',$html,$replaces_back);
		$this->hideSomething('/<a\b[^>]*?'.'>.*?<\/a>/si',$html,$replaces_back);

		// WebDesign -> <a href="...">WebDesign</a>
		$html = preg_replace_callback('/([>\s,.])(([A-Z][a-z0-9]+){2,})([\s,.<])/s',function($matches){
			$href = Atk14Url::BuildLink(array(
				"controller" => "wiki_pages",
				"action" => "detail",
				"name" => $matches[2]
			));
			return "$matches[1]<a href=\"$href\">$matches[2]</a>$matches[4]";
		},$html);

		// hideSomething() is called 2x, so it's needed sometimes to call EasyReplace 2x also
		$html = EasyReplace($html,$replaces_back);
		$html = EasyReplace($html,$replaces_back);

		return $html;
	}

}
