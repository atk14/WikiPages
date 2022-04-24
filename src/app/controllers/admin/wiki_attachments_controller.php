<?php
if(!defined("WIKI_BODY_MAX_WIDTH")){
	define("WIKI_BODY_MAX_WIDTH",defined("ARTICLE_BODY_MAX_WIDTH") ? constant("ARTICLE_BODY_MAX_WIDTH") : 859);
}

class WikiAttachmentsController extends AdminController {

	function create_new(){
		$this->page_title = _("Nahrání nové přílohy");

		$wiki_page = $this->_find("wiki_page","wiki_page_id");
		if(!$wiki_page){
			return;
		}
		$this->breadcrumbs[] = [$wiki_page->getName(),["action" => "wiki_pages/detail", "name" => $wiki_page->getName()]];

		if($this->request->post() && ($d = $this->form->validate($this->params))){
			$filename = $d["file"]->getFileName(["sanitize" => false]);
			myAssert(Translate::CheckEncoding($filename,DEFAULT_CHARSET));
			$existing = WikiAttachment::FindFirst("wiki_page_id",$wiki_page,"filename",$filename);
			if($existing && !$d["replace_existing"]){
				$this->form->set_error("file",sprintf(_("Příloha se stejným názvem (%s) u této stránky již existuje"),h($filename)));
				return;
			}
			if($existing){
				$existing->setContent($d["file"]->getContent());
				$this->flash->info(_("Příloha byla nahrazena"));
			}else{
				WikiAttachment::CreateNewRecord([
					"wiki_page_id" => $wiki_page,
					"filename" => $filename,
					"content" => $d["file"]->getContent(),
				]);
				$this->flash->info(_("Příloha byla nahrána"));
			}

			$this->_redirect_to(["action" => "wiki_pages/detail", "name" => $wiki_page->getName()]);
		}
	}

	function detail(){
		$ALLOWED_SIZES = [
			"full" => WIKI_BODY_MAX_WIDTH,
			"half" => ceil(WIKI_BODY_MAX_WIDTH/2),
			"quarter" => ceil(WIKI_BODY_MAX_WIDTH/4),
		];

		$wiki_attachment = $this->wiki_attachment;

		$format = (string)$this->params->getString("format");
		$size = (string)$this->params->getString("size");

		if(!in_array($format,[
			"",
			"thumbnail",
		])){
			return $this->_not_found();
		}

		if($size && !in_array($size,array_keys($ALLOWED_SIZES))){
			return $this->_not_found();
		}

		if($format && $size){
			return $this->_not_found();
		}

		$tmp_filename = $wiki_attachment->getTmpFilename();
		$this->render_template = false;

		if($format == "thumbnail"){
			if($wiki_attachment->isImage()){

				$this->_resize_image($tmp_filename,80,80,["background_color" => "#ffffff"]);

			}else{
				
				// https://icon-icons.com/icon/empty-file/72420
				$this->response->setHeader("Content-Type","image/svg+xml");
				$this->response->write('<?xml version="1.0" ?><!DOCTYPE svg  PUBLIC \'-//W3C//DTD SVG 1.1//EN\'  \'http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd\'><svg enable-background="new 0 0 91 91" height="91px" id="Layer_1" version="1.1" viewBox="0 0 91 91" width="91px" xml:space="preserve" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><g><path d="M25.878,74.469h35.129c3.595,0,6.518-2.924,6.518-6.52v-31.75c0-0.038-0.019-0.07-0.021-0.108   c-0.01-0.154-0.039-0.302-0.09-0.447c-0.02-0.057-0.037-0.114-0.063-0.167c-0.071-0.151-0.165-0.29-0.281-0.417   c-0.021-0.022-0.032-0.05-0.054-0.07c-0.006-0.005-0.008-0.012-0.013-0.017l-15.015-14.4c-0.141-0.134-0.302-0.232-0.473-0.311   c-0.049-0.022-0.1-0.033-0.151-0.05c-0.146-0.051-0.293-0.081-0.446-0.091c-0.037-0.002-0.068-0.021-0.106-0.021H25.878   c-0.938,0-1.699,0.761-1.699,1.7v50.97C24.179,73.708,24.939,74.469,25.878,74.469z M52.511,25.785l9.086,8.714H55.63   c-1.72,0-3.119-1.399-3.119-3.12V25.785z M27.578,23.499H49.11v7.881c0,3.595,2.924,6.52,6.52,6.52h8.495v30.05   c0,1.721-1.399,3.12-3.118,3.12H27.578V23.499z"/></g></svg>');
				return;

			}
		}

		if($size){
			if($wiki_attachment->isImage()){
				$this->_resize_image($tmp_filename,$ALLOWED_SIZES[$size]);
			}
		}

		$this->response->setHeader("Content-Type",$wiki_attachment->getMimeType());
		$this->response->setHeader(sprintf('Content-Disposition: inline; filename="%s"',$wiki_attachment->getFilename()));
		$this->response->buffer->addFile($tmp_filename);
	}

	function destroy(){
		if(!$this->request->post()){ return $this->_execute_action("error404"); }

		$wiki_page = $this->wiki_attachment->getWikiPage();

		$this->wiki_attachment->destroy();

		if(!$this->request->xhr()){
			$this->_redirect_to(["action" => "wiki_pages/detail", "name" => $wiki_page->getName()]);
		}
	}

	function _before_filter(){
		if(in_array($this->action,["detail"])){
			$this->_find("wiki_attachment");
		}
		if(in_array($this->action,["destroy"])){
			$wiki_attachment = WikiAttachment::GetInstanceByToken($this->params->getString("token"));
			if(!$wiki_attachment){ return $this->_execute_action("error404"); }
			$this->wiki_attachment = $this->tpl_data["wiki_attachment"] = $wiki_attachment;
		}
	}

	function _not_found(){
		$this->render_template = false;
		$this->response->setContentType("text/html");
		$this->response->notFound();
	}

	function _resize_image(&$tmp_filename,$width,$height = null,$options = []){
		$_tmp_filename = $tmp_filename.".x".md5(serialize(["width" => $width, "height" => $height, "options" => $options]));

		if(file_exists($_tmp_filename)){
			// the image is already resized
			$tmp_filename = $_tmp_filename;
			return true;
		}

		try {
			$scaler = new \Pupiq\ImageScaler($tmp_filename);
			if($scaler->scaleTo($width,$height,$options)){
				$scaler->saveTo($_tmp_filename);
				$tmp_filename = $_tmp_filename;
				return true;
			}
		}catch(Exception $e){
			// ...
		}

		return false;
	}
}
