<?php
/**
 * This file contains the BlogTest Class.
 *
 * @author     Satoshi Payne <satoshi.payne@gmail.com>
 * @copyright  Copyright (c) 2011, Satoshi Payne
 * @version    $Id: BlogTest.php 107 2012-02-29 01:02:53Z Satoshi $
 */
/**
 * The Blog Test Class contains a set of unit tests for the Blog Entity Class.
 *
 * @author    Satoshi Payne <satoshi.payne@gmail.com>
 * @category  UnitTests
 * @package   Blog
 */
class BlogTest extends CTestCase
{
	// These fixtures can be accessed as class properties.
	public $fixtures = array(
		'blog'     => 'Blog',
		'user'     => 'User',
		'comment'  => 'BlogComment',
		'category' => 'BlogCategory',
		'tag'      => 'BlogTag'
	);

	/**
	 * Setup the unit test state for all unit test methods.
	 */
	public function setUp()
	{
		$this->blog = new Blog;
		parent::setUp();
	}

	/**
	 * Test the construction of the blog object.
	 */
	public function testConstruct()
	{
		// Blog objects are not active by default.
		$this->assertFalse($this->blog->isActive());
		
		// Collections such as tags, comments and categories should be empty.
		$this->assertTrue(empty($this->blog->getTags()));
		$this->assertTrue(empty($this->blog->getComments()));
		$this->assertTrue(empty($this->blog->getCategories()));
	}
	
	/**
	 * Test the setActive method.
	 */
	public function testSetActive()
	{
		$this->blog->setActive(true);
		
		$this->assertTrue($this->blog->isActive());
		
		$this->blog->setActive(false);
		
		$this->assertFalse($this->blog->isActive());
	}
	
	/**
	 * Test the setAuthor method.
	 */
	public function testSetAuthor()
	{
		$this->user = new User;
		
		$this->assertTrue(is_null($this->blog->getAuthor()));
		
		$this->blog->setAuthor($this->user);
		$this->assertFalse(is_null($this->blog->getAuthor()));
	}
	
	/**
	 * Test the addComment method.
	 */
	public function testAddComment()
	{
		$this->comment = new BlogComment;
		
		$this->blog->addComment($this->comment);
		
		// There should now be 1 comment under this blog object.
		$this->assertTrue(count($this->blog->getComments()) == 1);
	}
	
	/**
	 * Test the addTag method.
	 */
	public function testAddTag()
	{
		$this->tag = new BlogTag;
		
		$this->blog->addTag($this->tag);
		
		// There should now be 1 tag attached to this blog object.
		$this->assertTrue(count($this->blog->getTags()) == 1);
	}
	
	/**
	 * Test the addCategory method.
	 */
	public function testAddCategory()
	{
		$this->category = new BlogCategory;
		
		$this->blog->addCategory($this->category);
		
		// There should now be 1 category attached to this blog object.
		$this->assertTrue(count($this->blog->getCategories()) == 1);
	}
}