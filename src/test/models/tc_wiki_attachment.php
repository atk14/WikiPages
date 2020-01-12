<?php
class TcWikiAttachment extends TcBase {

	function test(){
		$wp = WikiPage::CreateNewRecord([
			"name" => "Testing",
			"content" => "# Hello There!"
		]);

		$content = 'Hello World!';
		$att_plain = WikiAttachment::CreateNewRecord([
			"wiki_page_id" => $wp,
			"filename" => "test.txt",
			"content" => $content,
		]);
		$this->assertEquals($content,$att_plain->getContent());
		$this->assertEquals('text/plain',$att_plain->getMimeType());
		$this->assertEquals(12,$att_plain->getFilesize());
		$this->assertEquals(false,$att_plain->isImage());
		$this->assertEquals(null,$att_plain->getImageWidth());
		$this->assertEquals(null,$att_plain->getImageHeight());

		$content = "<html><head><title>Testing</title></head><body>Hello World!</body></html>";
		$att_html = WikiAttachment::CreateNewRecord([
			"wiki_page_id" => $wp,
			"filename" => "test.html",
			"content" => $content,
		]);
		$this->assertEquals($content,$att_html->getContent());
		$this->assertEquals('text/html',$att_html->getMimeType());
		$this->assertEquals(73,$att_html->getFilesize());
		$this->assertEquals(false,$att_html->isImage());
		$this->assertEquals(null,$att_html->getImageWidth());
		$this->assertEquals(null,$att_html->getImageHeight());

		$this->assertNotEquals($att_plain->getChecksum(),$att_html->getChecksum());

		$content = Files::GetFileContent(__DIR__ . "/../../atk14/src/files/test/sample_files/sample.jpg");
		$att_image = WikiAttachment::CreateNewRecord([
			"wiki_page_id" => $wp,
			"filename" => "sample.jpg",
			"content" => $content,
		]);
		$this->assertEquals($content,$att_image->getContent());
		$this->assertEquals('image/jpeg',$att_image->getMimeType());
		$this->assertEquals(973,$att_image->getFilesize());
		$this->assertEquals(true,$att_image->isImage());
		$this->assertEquals(50,$att_image->getImageWidth());
		$this->assertEquals(38,$att_image->getImageHeight());

		$tmp_filename = $att_image->getTmpFilename();
		$this->assertEquals(true,file_exists($tmp_filename));
		$this->assertEquals($content,Files::GetFileContent($tmp_filename));

		Files::Unlink($tmp_filename);
		$this->assertEquals(false,file_exists($tmp_filename));
		$tmp_filename = $att_image->getTmpFilename();
		$this->assertEquals(true,file_exists($tmp_filename));
		$this->assertEquals($content,Files::GetFileContent($tmp_filename));

		Files::Unlink($tmp_filename);
	}

	function test_setContent(){
		$wp = WikiPage::CreateNewRecord([
			"name" => "Testing",
			"content" => "# Hello There!"
		]);

		$content = Files::GetFileContent(__DIR__ . "/../../atk14/src/files/test/sample_files/sample.jpg");
		$att = WikiAttachment::CreateNewRecord([
			"wiki_page_id" => $wp,
			"filename" => "sample.jpg",
			"content" => $content,
		]);
		$this->assertEquals('image/jpeg',$att->getMimeType());
		$this->assertEquals(973,$att->getFilesize());
		$this->assertEquals(true,$att->isImage());
		$checksum = $att->getChecksum();

		$att->setContent("Hello World!");
		$this->assertEquals('text/plain',$att->getMimeType());
		$this->assertEquals(12,$att->getFilesize());
		$this->assertEquals(false,$att->isImage());
		$this->assertNotEquals($checksum,$att->getChecksum());
	}
}
