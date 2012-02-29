<?php
/**
 * This file contains the Blog Class and the associated BlogRepository Class.
 *
 * @author     Satoshi Payne <satoshi.payne@gmail.com>
 * @copyright  Copyright (c) 2011, Satoshi Payne
 * @version    $Id: Blog.php 105 2012-02-29 01:00:12Z Satoshi $
 */
use \Doctrine\Common\Collections, 
    \Doctrine\ORM\EntityRepository, 
    \Doctrine\ORM\Event as DoctrineEvent;

/**
 * The Blog Entity Class represents a single blog entry.
 *
 * @author    Satoshi Payne <satoshi.payne@gmail.com>
 * @category  Models
 * @package   Blog
 */
/**
 * @Entity(repositoryClass="BlogRepository")
 * @Table(name="Blog")
 * @HasLifecycleCallbacks
 */
class Blog extends DataObject implements IViewable
{
	// Fields.
	
	/**
	 * @Id @Column(type="integer", length=8)
	 * @GeneratedValue
	 */
	protected $id;
	
	/** @Column(type="string", length=140) */
	protected $title;
	
	/** @Column(type="datetime") */
	protected $dateDisplay;
	
	/** @Column(type="string", length=60) */
	protected $image;
	
	/** @Column(type="text") */
	protected $content;
	
	/** @Column(type="boolean") */
	protected $active;
	
	/** @Column(type="string", length=150) */
	protected $urlTitle;
	
	/** @Column(type="string", length=200) */
	protected $metaTitle;
	
	/** @Column(type="string", length=200) */
	protected $metaKeywords;
	
	/** @Column(type="string", length=250) */
	protected $metaDescription;
	
	// Associations.
	
	/** 
	 * @ManyToOne(targetEntity="User")
	 * @JoinColumn(name="authorId", referencedColumnName="id")
	 */
	protected $author;
	
	/**
	 * @OneToMany(targetEntity="BlogTag", mappedBy="blog")
	 */
	protected $tags;
	protected $tagsCount = null;
	
	/** 
	 * @OneToMany(targetEntity="BlogComment", mappedBy="blog", cascade={"persist", "remove"})
	 */
	protected $comments;
	
	/** 
	 * @ManyToMany(targetEntity="BlogCategory", mappedBy="blogs")
	 */
	protected $categories;
	
	// Constructor.
	
	public function __construct()
	{
		$this->tags = new \Doctrine\Common\Collections\ArrayCollection();
		$this->comments = new \Doctrine\Common\Collections\ArrayCollection();
		$this->categories = new \Doctrine\Common\Collections\ArrayCollection();
	}
	
	// Life-cycle Events.
	
	/** @PrePersist */
	public function actionOnPrePersist()
	{
		parent::actionOnPrePersist();
	}
	
	/** @PreUpdate */
	public function actionOnPreUpdate()
	{
		parent::actionOnPreUpdate();
	}
	
	/** @PreRemove */
	public function actionOnPreRemove()
	{
		parent::actionOnPreRemove();
	}
	
	// Field Accessors.
	
	public function getId() { return $this->id; }
	
	public function getTitle() { return $this->title; }
	public function setTitle($value) { $this->title = $value; return $this; }
	
	public function getDateDisplay() { return $this->dateDisplay; }
	public function setDateDisplay($value) { $this->dateDisplay = $value; return $this; }
	
	public function getImage() { return $this->image; }
	public function setImage($value) { $this->image = $value; return $this; }
	
	public function getContent() { return $this->content; }
	public function setContent($value) { $this->content = $value; return $this; }
	
	public function isActive() { return $this->active; }
	public function setActive($value) { $this->active = $value; return $this; }
	
	public function getUrlTitle() { return $this->urlTitle; }
	public function setUrlTitle($value) { $this->urlTitle = $value; return $this; }
	
	public function getMetaTitle() { return $this->metaTitle; }
	public function setMetaTitle($value) { $this->metaTitle = $value; return $this; }
	
	public function getMetaKeywords() { return $this->metaKeywords; }
	public function setMetaKeywords($value) { $this->metaKeywords = $value; return $this; }
	
	public function getMetaDescription() { return $this->metaDescription; }
	public function setMetaDescription($value) { $this->metaDescription = $value; return $this; }
	
	// Association Accessors.
	
	public function getAuthor() { return $this->author; }
	public function setAuthor($value) { $this->author = $value; return $this; }
	
	public function getTags() { return $this->tags; }
	public function getTagsCount() { return $this->tagsCount; }
	public function fetchTags($ordering, $limit, $offset = 0)
	{
		$em = DoctrineComponent::getEntityManager();
		
		if(is_null($this->tagsCount)) {
			
			$this->tags = new \Doctrine\Common\Collections\ArrayCollection();
			
			// Retrieve tags and add to collection one-by-one.
			$q = $em->getRepository('BlogTag')->getTagsQuery($this->id, $ordering, $limit, $offset);
			$tags = Queryer::fetchAll($q);
			//$tags = Queryer::fetchAll($q, \Doctrine\ORM\Query::HYDRATE_ARRAY);
			if($tags) {
				foreach($tags as $i => $tag) {
					$this->tags->add($tag);
				}
			}
			$this->tagsCount = Queryer::fetchCount($q);
		}
		return $this->tags;
	}
	/*public function addTag($value) {
		$this->tags[] = $value;
		return $this;
	}*/
	
	public function getComments() { return $this->comments; }
	public function addComment($value)
	{
		$this->comments[] = $value;
		return $this;
	}
	
	public function getCategories() { return $this->categories; }
	public function setCategories($value) { $this->categories = $value; return $this; }
	public function addCategory($value)
	{
		$this->categories[] = $value;
		return $this;
	}
	
	// Methods.
	
	public function getViewableLink($options = null, $context = IViewable::CONTEXT_DEFAULT)
	{
		$link = '';
		switch($context)
		{
			case IViewable::CONTEXT_DEFAULT:
				$link = '/article/' . $this->getUrlTitle();
				break;
			default:
				throw new Exception('Link failed to be created for blog.');
		}
		return $link;
	}
}

/**
 * The Blog Repository Class for the Blog Entity Class.
 *
 * @author      Satoshi Payne <satoshi.payne@gmail.com>
 * @category    Models
 * @package     Blog
 * @subpackage  Repositories
 */
class BlogRepository extends EntityRepository
{
	/**
	 * Retrieve a list of blog articles.
	 *
	 * @param  integer $categoryId The category ID of the category that the blog articles belong to.
	 * @param  integer $year The year that the blog articles were published.
	 * @param  integer $month The month that the blog articles were published.
	 * @param  integer $limit The maximum number of blog articles to retrieve at a time.
	 * @param  integer $offset The index of the first result of blog articles we want to retrieve.
	 * @return Doctrine\ORM\Query
	 */
	public function getBlogsQuery($categoryId, $year, $month, $limit, $offset = 0)
	{
		$params = array();
		
		// Month.
		$cMonth = '';
		if(strlen($month) > 0) {
			$cMonth = '
			  AND MONTH(b.dateDisplay) = :month
			  AND YEAR(b.dateDisplay) = :year';
			$params['month'] = $month;
			$params['year'] = $year;
		}
		
		// Category.
		$cCategory = '';
		if(strlen($categoryId) > 0) {
			$cCategory = '  AND b.category = :categoryId';
			$params['categoryId'] = $categoryId;
		}
		
		// Main query.
		$sql = '
			SELECT b AS obj, u, COUNT(c.id) AS commentCount 
			FROM Blog b
			LEFT JOIN b.author u
			LEFT JOIN b.comments c
			WHERE b.dateDeleted IS NULL
			  AND u.dateDeleted IS NULL
			  AND c.dateDeleted IS NULL
			  AND b.active = 1
			  AND c.status >= 1' . 
			$cMonth .
			$cCategory . ' 
			ORDER BY b.dateDisplay DESC';
		$query = $this->_em->createQuery($sql);
		$query->setParameters($params);
		if($limit > 0) {
			$query->setMaxResults($limit);
			$query->setFirstResult($offset);
		}
		return $query;
	}
	
	/**
	 * Retrieve a single blog article.
	 *
	 * @param  integer $blogId The blog ID of the blog article that we want to retrieve.
	 * @param  string $urlTitle The URL title of the blog article that we want to retrieve.
	 * @param  integer $userId The user ID of the currently logged in user, we will use this for any sort of
	 * authorisations that may take place.
	 * @return array The Blog object wrapped by an array.
	 */
	public function getBlog($blogId, $urlTitle, $userId)
	{
		$params = array(
			'blogId' => $blogId,
			'urlTitle' => $urlTitle
		);
		
		// Main query.
		$sql = '
			SELECT b AS obj, u, COUNT(c.id) AS commentCount 
			FROM Blog b 
			LEFT JOIN b.author u
			LEFT JOIN b.comments c
			WHERE b.dateDeleted IS NULL
			  AND u.dateDeleted IS NULL
			  AND c.dateDeleted IS NULL
			  AND b.active = 1 
			  AND (b.id = :blogId OR b.urlTitle = :urlTitle)
			  AND c.status >= 1';
		$query = $this->_em->createQuery($sql);
		$query->setParameters($params);
		return Queryer::fetchOne($query);
	}
}