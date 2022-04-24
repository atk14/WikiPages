<?php
class WikiAttachment extends ApplicationModel {

	function __construct(){
		parent::__construct("wiki_attachments",[
			"do_not_read_values" => ["content"],
		]);
	}

	static function CreateNewRecord($values,$optiosn = []){
		$values += [
			"content" => null,
			"filename" => null,
		];

		self::_FulfillValuesByContent($values["content"],$values);

		return parent::CreateNewRecord($values);
	}

	/**
	 * Replaces existing content
	 *
	 * Also replaces other related fields.
	 */
	function setContent($content){
		$values = [
			"id" => $this->getId(),
			"filename" => $this->getFilename(),
		];

		self::_FulfillValuesByContent($content,$values);

		$this->s($values);
	}

	static protected function _FulfillValuesByContent($content,&$values){
		$values += [
			"filename" => null,
		];

		$filename = Files::WriteToTemp($content,$err);
		myAssert(!$err);

		if(!isset($values["id"])){
			$values["id"] = self::GetNextId();
		}

		if(!isset($values["checksum"])){
			$values["checksum"] = md5(md5($content).md5($content."WikiAttachment"));
		}

		if(!isset($values["mime_type"])){
			$values["mime_type"] = Files::DetermineFileType($filename,["original_filename" => $values["filename"]]);
		}

		$values["content"] = chunk_split(base64_encode($content));
		$values["filesize"] = filesize($filename);

		$image_width = $image_height = null;
		if($ary = getimagesize($filename)){
			$image_width = $ary[0];
			$image_height = $ary[1];
		}
		$values += [
			"image_width" => $image_width,
			"image_height" => $image_height,
		];

		Files::Unlink($filename);
	}

	static function GetInstancesFor($wiki_page){
		$current_page = $wiki_page->getCurrentPage();
		return self::FindAll("wiki_page_id",$current_page,["order_by" => "UPPER(filename),filename"]);
	}

	function isImage(){
		return !!preg_match('/^image\//',$this->getMimeType());
	}

	function getTmpFilename(){
		$tmp_file = TEMP."/wiki_attachments/".$this->getId()."_".$this->getChecksum();
		if(!file_exists($tmp_file)){
			$src = Files::WriteToTemp($this->getContent(),$err);
			myAssert(!$err);

			Files::MkdirForFile($tmp_file);
			Files::MoveFile($src,$tmp_file,$err);
			myAssert(!$err);
		}
		return $tmp_file;
	}

	function getContent(){
		return base64_decode($this->g("content"));
	}

	function getWikiPage(){
		return Cache::Get("WikiPage",$this->getWikiPageId());
	}

	function getCreatedByUser(){
		return Cache::Get("User",$this->getCreatedByUserId());
	}

	function getUpdatedByUser(){
		return Cache::Get("User",$this->getUpdatedByUserId());
	}

	/**
	 *
	 *	$wiki_attachment->getUrl();
	 *	$wiki_attachment->getUrl(["with_hostname" => true]);
	 *
	 *	// specify image size
	 * 	$wiki_attachment->getUrl("thumbnail"); // "thumbnail", "full", "half", "quarter"
	 * 	$wiki_attachment->getUrl("thumbnail",["with_hostname" => true]);
	 */
	function getUrl($size_or_options = "",$options = []){
		if(is_array($size_or_options)){
			$options = $size_or_options;
			$size_or_options = "";
		}

		$size = $size_or_options;

		$params = [
			"controller" => "wiki_attachments",
			"action" => "detail",
			"id" => $this,
		];

		if($size_or_options){
			$params["size"] = $size_or_options;
		}

		return Atk14Url::BuildLink($params,$options);
	}

	function getRelativeUrl($size = ""){
		$page = $this->getWikiPage();
		$url = sprintf("%s/files/%s",urlencode($page->getName()),urlencode($this->getFilename()));
		if($size){
			$url .= "?size=".urlencode($size);
		}
		return $url;
	}
}
