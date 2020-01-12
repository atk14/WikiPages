<?php
class WikiPagesController extends AdminController {

	var $wiki_name = "wiki"; // "wiki", "instructions_and_manuals"... 
	var $wiki_name_humanized = "Wiki"; // "Wiki", "Instructions and Manuals"

	function index(){
		if($this->params->isEmpty()){
			$this->_redirect_to_index();
			return;
		}

		$this->page_title = $this->wiki_name_humanized;

		($d = $this->form->validate($this->params)) || ($d = $this->form->get_initial());

		$conditions = $bind_ar = [];

		$conditions[] = "wiki_name=:wiki_name";
		$bind_ar[":wiki_name"] = $this->wiki_name;
		$conditions[] = "NOT deleted";

		$conditions[] = "revision=(SELECT MAX(revision) FROM wiki_pages wp WHERE wp.name=wiki_pages.name AND wp.wiki_name=wiki_pages.wiki_name)";

		if($d["search"]){
			$q_up = Translate::Upper($d["search"]);

			$_fields = array();
			$_fields[] = "name";
			$_fields[] = "id";
			$_fields[] = "content";

			$ft_cond = FullTextSearchQueryLike::GetQuery("UPPER(".join("||' '||",$_fields).")",$q_up);
			if($ft_cond){
				$conditions[] = $ft_cond;
				$bind_ar[":search"] = $q_up;
			}
		}
		
		$this->sorting->add("updated_at","COALESCE(updated_at,created_at) DESC","COALESCE(updated_at,created_at) ASC");
		$this->sorting->add("name");

		$this->tpl_data["finder"] = WikiPage::Finder([
			"conditions" => $conditions,
			"bind_ar" => $bind_ar,

			"order_by" => $this->sorting,
			"offset" => null,
			"limit" => null,
		]);
	}

	function detail(){
		$name = $this->params->getString("name");
		if(!strlen($name)){ return $this->_redirect_to_index(); }

		if(!$wiki_page = $this->_find_wiki_page($name)){
			$this->tpl_data["name"] = $name;
			$this->template_name = "page_not_found";
			$this->page_title = sprintf(_("Stránka %s nenalezena"),$name);
			$this->response->setStatusCode(404);
			return;
		}

		if($wiki_page->isDeleted()){
			return $this->_execute_action("error404");
		}

		$latest_revision = $wiki_page->getRevision();

		$revisions = WikiPage::GetAllRevisionsByName($name,$this->wiki_name);

		$revision = $this->params->defined("revision") ? $this->params->getInt("revision") : $wiki_page->getRevision();
		if(!isset($revisions[$revision])){
			return $this->_execute_action("error404");
		}
		$wiki_page = $revisions[$revision];

		$this->page_title = $wiki_page->getName();

		if($revision!=$latest_revision){
			$this->breadcrumbs[] = [$wiki_page->getName(),$this->_link_to(["action" => "detail", "name" => $wiki_page->getName()])];
			$this->page_title = sprintf(_("%s (rev %s)"),$wiki_page->getName(),$revision);
		}

		$content = $wiki_page->getContent();
		Atk14Require::Helper("modifier.wiki_markdown");
		$content = smarty_modifier_wiki_markdown($content);
		$pattern = '/^<h1(|\s[^>]*)>(?<page_title>.+?)<\/h1>/';
		$content = preg_replace($pattern,'',$content); // odstraneni nadpisu <h1>

		$this->tpl_data["content"] = $content;
		$this->tpl_data["wiki_page"] = $wiki_page;
		$this->tpl_data["revisions"] = $revisions;
		$this->tpl_data["revision"] = $revision;
		$this->tpl_data["latest_revision"] = $latest_revision;
	}

	function create_new(){
		$this->page_title = _("Vytvořit novou stránku");

		$title = $this->params->getString("name") ? $this->params->getString("name") : _("Nadpis");
		$this->form->set_initial([
			"name" => $this->params->getString("name"),
			"content" => "# $title\n\n"
		]);

		if($this->request->post() && ($d = $this->form->validate($this->params))){
			if($this->_find_wiki_page($d["name"])){
				$this->form->set_error("name",_("Stránka s tímto jménem již existuje"));
				return;
			}
			$d["wiki_name"] = $this->wiki_name;
			$wiki_page = WikiPage::CreateNewRecord($d);
			$this->_redirect_to(["action" => "detail", "name" => $wiki_page->getName()]);
		}
	}

	function edit(){
		$this->page_title = _("Editace stránky");
		$this->breadcrumbs[] = [$this->wiki_page->getName(),$this->_link_to(["action" => "detail", "name" => $this->wiki_page->getName()])];

		$this->form->set_initial($this->wiki_page);
		$this->form->set_initial("content",$this->wiki_page->getContent());

		if($this->request->post() && ($d = $this->form->validate($this->params))){
			if($this->wiki_page->getName()!=$d["name"] && $this->_find_wiki_page($d["name"])){
				$this->form->set_error("name",_("Stránka s tímto jménem již existuje"));
				return;
			}
			$this->wiki_page->updateContent($d["content"],$this->logged_user);
			$this->wiki_page->updateName($d["name"]);

			$this->_redirect_to(["action" => "detail", "name" => $this->wiki_page->getName()]);
		}
	}

	function destroy(){
		if(!$this->wiki_page->isDeletable()){
			return $this->_execute_action("error404");
		}
		if(!$this->wiki_page->isDeletableBy($this->logged_user)){
			return $this->_execute_action("error403");
		}

		$this->breadcrumbs[] = [$this->wiki_page->getName(),$this->_link_to(["action" => "detail", "name" => $this->wiki_page->getName()])];

		$this->page_title = sprintf(_("Deleting page %s"),$this->wiki_page->getName());

		if($this->request->post() && ($d = $this->form->validate($this->params))){
			WikiPage::DestroyAllRevisionsByName($this->wiki_page->getName(),$this->wiki_page->getWikiName(),false);
			$this->flash->success("The page was deleted");
			$this->_redirect_to_index();
		}
	}

	function _redirect_to_index(){
		$this->_redirect_to([
			"action" => "detail",
			"name" => "Index",
		]);
	}

	function _find_wiki_page($name,$revision = 0){
		return WikiPage::FindFirst("name",$name,"wiki_name",$this->wiki_name);
	}

	function _before_filter(){
		if(in_array($this->action,["edit","destroy"])){
			$this->_find("wiki_page");
		}
	}

	function _before_render(){
		parent::_before_render();

		$this->tpl_data["wiki_name"] = $this->wiki_name;
		$this->tpl_data["wiki_name_humanized"] = $this->wiki_name_humanized;
		$this->tpl_data["search_form"] = $this->_get_form("index");
	}
}
