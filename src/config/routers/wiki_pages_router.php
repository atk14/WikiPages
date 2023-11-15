<?php
/**
 * Router pro Wiki Pages
 *
 * Zpusob nahrani WikiPagesRouter do pozadovaneho namespace (napr. admin):
 *
 *	<?php
 *	// file: config/routers/load.php
 *	Atk14Router::AddRouter("admin","WikiPagesRouter");
 */
class WikiPagesRouter extends Atk14Router {

	var $wiki_url_prefix = "wiki";
	var $wiki_name = "wiki";
	var $wiki_controller = "wiki_pages";

	function setUp(){
		$this->addRoute("/<lang>/$this->wiki_url_prefix/",["controller" => $this->wiki_controller, "action" => "index"]);
		$this->addRoute("/<lang>/$this->wiki_url_prefix/<name>",["controller" => $this->wiki_controller, "action" => "detail", "name" => '/[^\/]+/']);
	}

	function recognize($uri){
		if(preg_match('/^\/([a-z]{2})\/'.$this->wiki_url_prefix.'\/([^\/]+)\/files\/([^\/]+)/',$uri,$matches)){
			$wiki_page_name = $matches[2];
			$filename = urldecode($matches[3]);
			$wiki_page = WikiPage::FindFirst("wiki_name",$this->wiki_name,"name",$wiki_page_name,["use_cache" => true]);
			if(!$wiki_page){ return; }
			$wa = WikiAttachment::FindFirst("wiki_page_id",$wiki_page,"filename",$filename);
			if(!$wa){ return; }
			$this->lang = $matches[1];
			$this->controller = "wiki_attachments";
			$this->action = "detail";
			$this->params["id"] = $wa->getId();
		}
	}

	function build(){
		if($this->controller!="wiki_attachments" || $this->action!="detail"){ return; }

		if($wa = Cache::Get("WikiAttachment",$this->params->getInt("id"))){
			if($wa->getWikiPage()->getWikiName()!=$this->wiki_name){ return; }
			$this->params->del("id");
			return sprintf("/%s/$this->wiki_url_prefix/%s/files/%s",$this->lang,$wa->getWikiPage()->getName(),urlencode($wa->getFilename()));
		}
	}
}
