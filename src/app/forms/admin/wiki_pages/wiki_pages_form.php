<?php
class WikiPagesForm extends AdminForm {

	function set_up(){
		$this->add_field("content", new MarkdownField([
			"label" => _("Obsah"),
			"base_href" => Atk14Url::BuildLink(["controller" => "wiki_pages", "action" => "index"]),
		]));

		$this->add_field("name",new RegexField('/^([A-Z][a-z0-9]*)+$/',[
			"label" => _("Označení stránky ve formátu Wiki"),
			"hints" => [
				"Napoveda",
				"ImportDat",
				"PrechodNaRok2000"
			],
			"max_length" => 255,
		]));
	}
}
