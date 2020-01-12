<?php
/**
 *
 * @fixture users
 */
class TcWikiPage extends TcBase {

	function test(){
		$rocky = $this->users["rocky"];
		$rambo = $this->users["rambo"];

		$wp = WikiPage::CreateNewRecord([
			"name" => "Testing",
			"content" => "# Hello There!"
		]);

		$this->assertEquals("Testing",$wp->getName());
		$this->assertEquals("# Hello There!",$wp->getContent());

		$wp->updateContent("# Hello There!");
		$this->assertEquals(1,$wp->getRevision());
		$this->assertEquals("# Hello There!",$wp->getContent());

		$wp->updateContent("# Nice to meet you!");
		$this->assertEquals("# Nice to meet you!",$wp->getContent());
		$this->assertEquals(1,$wp->getRevision());

		$wp->updateContent("# Nice to meet you!",$rambo);
		$this->assertEquals("# Nice to meet you!",$wp->getContent());
		$this->assertEquals(1,$wp->getRevision());
		$this->assertEquals(null,$wp->g("updated_by_user_id"));

		$wp->updateContent("# Me, Rambo",$rambo);
		$this->assertEquals("# Me, Rambo",$wp->getContent());
		$this->assertEquals(2,$wp->getRevision());
		$this->assertEquals($rambo->getId(),$wp->g("created_by_user_id"));
		$this->assertEquals(null,$wp->g("updated_by_user_id"));

		$wp->updateContent("# Me, Rambo!!!",$rambo);
		$this->assertEquals("# Me, Rambo!!!",$wp->getContent());
		$this->assertEquals(2,$wp->getRevision());
		$this->assertEquals($rambo->getId(),$wp->g("created_by_user_id"));
		$this->assertEquals($rambo->getId(),$wp->g("updated_by_user_id"));

		$wp->updateContent("# Rocky lives...",$rocky);
		$this->assertEquals("# Rocky lives...",$wp->getContent());
		$this->assertEquals(3,$wp->getRevision());
		$this->assertEquals($rocky->getId(),$wp->g("created_by_user_id"));
		$this->assertEquals(null,$wp->g("updated_by_user_id"));

		$manual = WikiPage::CreateNewRecord([
			"name" => "Testing",
			"wiki_name" => "user_manual"
		]);

		// updateName();

		$wp->updateName("UnitTesting");
		$this->assertEquals("UnitTesting",$wp->getName());

		$this->assertNull(WikiPage::GetInstanceByName("Testing"));

		$this->assertNotNull(WikiPage::GetInstanceByName("Testing","user_manual"));
	}

	function test_getTitle(){
		$wp = WikiPage::CreateNewRecord([
			"name" => "TestPage",
			"content" => "Hello there!",
		]);

		$this->assertEquals("TestPage",$wp->getTitle());

		$wp->updateContent("# Hello There!");
		$this->assertEquals("Hello There!",$wp->getTitle());

		$wp->updateContent("## HelloThere");
		$this->assertEquals("HelloThere",$wp->getTitle());
	}
}
