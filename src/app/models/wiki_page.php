<?php
class WikiPage extends ApplicationModel {

	static function GetInstanceByName($name,$wiki_name = "wiki") {
		return WikiPage::FindFirst("wiki_name",$wiki_name,"name",$name,["order_by" => "revision DESC"]);
	}

	/**
	 *
	 * foreach(WikiPage::GetAllRevisionsByName("ImportArticles") as $revision => $wp){
	 *	// ..
	 * }
	 */
	static function GetAllRevisionsByName($name,$wiki_name = "wiki") {
		$pages = [];
		foreach(WikiPage::FindAll("wiki_name",$wiki_name,"name",$name,["order_by" => "revision DESC"]) as $wp){
			$pages[$wp->getRevision()] = $wp;
		}
		return $pages;
	}

	static function DestroyAllRevisionsByName($name,$wiki_name = "wiki",$destroy_for_real = false){
		foreach(self::GetAllRevisionsByName($name,$wiki_name) as $page){
			$page->destroy($destroy_for_real);
		}
	}

	/**
	 * Extracts title from the content
	 *
	 * It searches the title within <h1> element.
	 */
	function getTitle(){
		Atk14Require::Helper("modifier.wiki_markdown");

		$content = smarty_modifier_wiki_markdown($this->getContent());
		$pattern = '/^<(h1|h2|h3)(|\s[^>]*)>(?<page_title>.+?)<\/\1>/';
		if(preg_match($pattern,$content,$matches)){
			return strip_tags($matches["page_title"]);
		}

		return $this->getName();
	}

	function getCreatedByUser(){
		return Cache::Get("User",$this->getCreatedByUserId());
	}

	function getUpdatedByUser(){
		return Cache::Get("User",$this->getUpdatedByUserId());
	}

	function isDeleted(){ return $this->g("deleted"); }

	/**
	 *
	 * $page->updateContent("Welcome!",$logged_user);
	 */
	function updateContent($content,$user = null){
		$user_id = is_object($user) ? $user->getId() : $user;
		$current_content = $this->getContent();
		$current_revision = $this->getRevision();
		$current_mtime = strtotime($this->g("updated_at") ? $this->g("updated_at") : $this->g("created_at"));
		$current_muser_id = !is_null($this->g("updated_by_user_id")) ? $this->g("updated_by_user_id") : $this->g("created_by_user_id");

		if($current_content==$content){
			return;
		}

		$values = [
			"content" => $content,
		];

		$new_revision_required =
			(time()-$current_mtime > 60 * 30) || 
			(!is_null($user_id) && $user_id!==$current_muser_id) ||
			(is_null($user_id) && !is_null($this->g("updated_by_user_id")));

		if($new_revision_required){
			$values += [
				"revision" => $current_revision + 1,
				"created_by_user_id" => $user_id,
				"updated_by_user_id" => null,
				"created_at" => now(),
				"updated_at" => null,
			];
			$data = $this->toArray();
			unset($data["id"]);
		}else{
			$values += [
				"updated_by_user_id" => $user_id,
				"updated_at" => now(),
			];
		}

		$this->s($values);

		if($new_revision_required){
			WikiPage::CreateNewRecord($data);
		}
	}

	/**
	 *
	 * $page = WikiPage::GetInstanceByName("ImportOfArticles");
	 * $page->updateName("ArticlesImport");
	 */
	function updateName($name){
		$name = (string)$name;
		if($this->g("name")===$name){
			return;
		}
		
		$table_name = $this->getTableName();
		$this->dbmole->doQuery("UPDATE $table_name SET name=:new_name WHERE wiki_name=:wiki_name AND name=:current_name",[
			":current_name" => $this->g("name"),
			":new_name" => $name,
			":wiki_name" => $this->g("wiki_name"),
		]);

		$this->_readValues();
	}

	function getCurrentPage(){
		return WikiPage::FindFirst("wiki_name",$this->getWikiName(),"name",$this->getName(),["order_by" => "revision DESC", "use_cache" => true]);
	}

	function getCurrentRevision(){
		return $this->getCurrentPage()->getRevision();
	}

	function isCurrentRevision(){
		return $this->getRevision()===$this->getCurrentRevision();
	}

	function isEditableBy($user){
		return !$this->isDeleted() && $this->isCurrentRevision();
	}

	function isDeletable(){
		return !$this->isDeleted() && $this->isCurrentRevision();
	}

	function isDeletableBy($user){
		return $this->isDeletable();
	}

	function destroy($destroy_for_real = false){
		if(!$destroy_for_real){
			$this->s([
				"name" => $this->getName()."~deleted-".$this->getId(),
				"deleted" => true,

				"updated_at" => $this->g("updated_at"),
				"updated_from_addr" => $this->g("updated_from_addr"),
				"updated_from_hostname" => $this->g("updated_from_hostname"),
			]);
			return;
		}

		return parent::destroy($destroy_for_real);
	}
}
