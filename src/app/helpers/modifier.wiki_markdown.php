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
function smarty_modifier_wiki_markdown($text,$wiki_name = "wiki"){
	
	$markdown = new DrinkMarkdown(array(
		"table_class" => "table",
		"html_purification_enabled" => false,
		"iobjects_processing_enabled" => true,
		"urlize_text" => true,
	));

	$markdown->appendPostFilter(new WikiPageLinksCreatorFilter());
	$markdown->appendPostFilter(new NonExistentWikiPageLinksDetectorFilter($wiki_name));

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
			$name = $matches[2];
			$link = Atk14Url::BuildLink(array(
				"controller" => "wiki_pages",
				"action" => "detail",
				"name" => $name,
			));
			return "$matches[1]<a href=\"$name\">$matches[2]</a>$matches[4]";
		},$html);

		// hideSomething() is called 2x, so it's needed sometimes to call EasyReplace 2x also
		$html = EasyReplace($html,$replaces_back);
		$html = EasyReplace($html,$replaces_back);

		return $html;
	}

}

class NonExistentWikiPageLinksDetectorFilter extends DrinkMarkdownFilter {

	protected $wiki_name;

	function __construct($wiki_name, $options = []){
		$this->wiki_name = (string)$wiki_name;
		parent::__construct($options);
	}

	function filter($html,$transformator){
		$html = preg_replace_callback('/(?<link><a href="(?<name>([A-Z][a-z0-9]+){2,})">.*?<\/a>)/si',function($matches){
			$name = $matches["name"];
			$link = $matches["link"];
			if(!WikiPage::PageExists($name,$this->wiki_name)){
				$class = ' class="text-danger"';
				$title = sprintf(_("stránka %s neexistuje"),$name);
				$link = preg_replace('/^(<a href="[^"]+")>/','\1 class="text-danger" title="'.h($title).'">',$link);
			}
			return $link;
		},$html);

		//$html = EasyReplace($html,$replaces);

		return $html;
	}
}
