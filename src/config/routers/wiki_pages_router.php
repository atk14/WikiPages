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

	function setUp(){
		$this->addRoute('/<lang>/wiki/',["controller" => "wiki_pages", "action" => "index"]);
		$this->addRoute('/<lang>/wiki/<name>',["controller" => "wiki_pages", "action" => "detail", "name" => '/[^\/]+/']);
	}

	function recognize($uri){
		if(preg_match('/^\/([a-z]{2})\/wiki\/([^\/]+)\/files\/([^\/]+)/',$uri,$matches)){
			$wiki_page_name = $matches[2];
			$filename = urldecode($matches[3]);
			$wiki_page = WikiPage::FindFirst("wiki_name","wiki","name",$wiki_page_name,["use_cache" => true]);
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
			if($wa->getWikiPage()->getWikiName()!="wiki"){ return; }
			$this->params->del("id");
			return sprintf('/%s/wiki/%s/files/%s',$this->lang,$wa->getWikiPage()->getName(),urlencode($wa->getFilename()));
		}
	}
}
