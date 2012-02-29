<?php
/**
 * This file contains the BlogController Class.
 *
 * @author     Satoshi Payne <satoshi.payne@gmail.com>
 * @copyright  Copyright (c) 2011, Satoshi Payne
 * @version    $Id: BlogController.php 105 2012-02-29 01:00:12Z Satoshi $
 */
/**
 * The BlogController Controller Class defines a set of routes/actions to manage the website's blog section.
 *
 * @author    Satoshi Payne <satoshi.payne@gmail.com>
 * @category  Controllers
 * @package   Blog
 */
class BlogController extends Controller
{
	/**
	 * Blog listing.
	 * Will show a list of blog articles.
	 */
	public function actionIndex()
	{
		// Params.
		
		$request = $this->request;
		$categoryUrl = $request->getQuery('ct');
		$year        = $request->getQuery('y');
		$month       = $request->getQuery('m');
		$pageNumber  = $request->getQuery('p');
		
		// Validation.
		
		$categoryUrl = Sanitise::validate_urltitle($categoryUrl, '');
		$year        = Sanitise::validate_int($year, true, '');
		$year        = Sanitise::validate_length($year, 4, '');
		$month       = Sanitise::validate_int($month, true, '');
		$month       = Sanitise::validate_lengthlte($month, 2, '');
		$pageNumber  = Sanitise::validate_int($pageNumber, true, 1);
		
		// Derived values.
		
		$pageSize = 10; // @todo: Don't use magic numbers. This should come out of a config file.
		$offset   = $pageSize * ($pageNumber - 1);
		
		// Data.
		
		$em = DoctrineComponent::getEntityManager();
		
		// Get current user for access.
		$user = User::getCurrentUser();
		
		// Get blog category.
		$category = $em->getRepository('BlogCategory')->getCategory('', $categoryUrl);
		$categoryId = null;
		if($category) {
			$categoryId = $category->getId();
		}
		
		// Get blog articles.
		$q = $em->getRepository('Blog')->getBlogsQuery($categoryId, $year, $month, $pageSize, $offset);
		$blogs      = Queryer::fetchAll($q);
		$blogsCount = Queryer::fetchCount($q);
		
		// Get tags for each blog. This is required since Doctrine doesn't handle associations well while trying to limit
		// them.
		foreach($blogs as $i => $blog) {
			$blogObj = $blog['obj'];
			$blogObj->fetchTags('name', 5, 0); // @todo: Don't use magic numbers. This should come out of a config file.
		}
		
		// Advertisements.
		$advertisements = $em->getRepository('Advertisement')->getRandom(3);
		
		// Assign to view.
		$this->view->blogs      = $blogs;
		$this->view->category   = $category;
		$this->view->pagination = ViewHelper::pagination($this, 'blogs', $blogsCount, $pageSize, $pageNumber, 4);
		$this->view->ads        = $advertisements;
		
		// Add advertisements to impressionables list.
		$staglog = StatLog::getInstance();
		$staglog->addImpressionables($advertisements);
		
		$this->render('listing', array('view' => $this->view));
	}
	
	/**
	 * Blog article page.
	 * Will show a single blog article and its details.
	 */
	public function actionArticle()
	{
		// Post actions:
		// - actionPostComment: The action that posts a new comment to the article.
		
		// Params.
		
		$request = $this->request;
		$blogId         = $request->getQuery('id');
		$urlTitle       = $request->getQuery('t');
		$categoryUrl    = $request->getQuery('ct');
		$year           = $request->getQuery('y');
		$month          = $request->getQuery('m');
		$blogPageNumber = $request->getQuery('p');
		$pageNumber     = $request->getQuery('cp');
		
		// Validation.
		
		$blogId      = Sanitise::validate_int($blogId, '');
		$urlTitle    = Sanitise::validate_urltitle($urlTitle, '');
		$categoryUrl = Sanitise::validate_urltitle($categoryUrl, '');
		$pageNumber  = Sanitise::validate_int($pageNumber, true, 1);
		
		// Handle post back.
		$actionsList = array(
			'PostComment',
			);
		$this->handlePostback($actionsList);
		
		// Derived values.
		
		$pageSize = 10; // @todo: Don't use magic numbers. This should come out of a config file.
		$offset   = $pageSize * ($pageNumber - 1);
		
		// Data.
		
		$em = DoctrineComponent::getEntityManager();
		
		// Get current user for access.
		$user = User::getCurrentUser();
		
		// Get blog article.
		$blog = $em->getRepository('Blog')->getBlog($blogId, $urlTitle, $user->getId());
		// Keep a reference to the actual blog object, the rest of the array values are either aggregated values or custom
		// select queries.
		$blogObj = $blog['obj'];
		
		// Get blog article comments.
		$q = $em->getRepository('BlogComment')->getBlogCommentsQuery($blogObj->getId(), $pageSize, $offset);
		$comments      = Queryer::fetchAll($q);
		$commentsCount = Queryer::fetchCount($q);
		
		// Assign to view.
		$this->view->canReply   = $user->isActive() && !$user->isGuest();
		$this->view->blog       = $blog;
		$this->view->blogObj    = $blog['obj'];
		$this->view->comments   = $comments;
		$this->view->pagination = ViewHelper::pagination($this, 'comments', $commentsCount, $pageSize, $pageNumber, 4);
		
		$this->render('detail', array('view' => $this->view));
	}
	public function actionPostComment()
	{
		// Params.
		
		$request = $this->request;
		$blogId  = $request->getPost('id');
		$title   = $request->getPost('Title');
		$content = $request->getPost('Comment');
		
		// Validation.
		
		$blogId = Sanitise::validate_int($blogId, '');
		
		// Processing.
		
		// Check if valid status passed through.
		$em = DoctrineComponent::getEntityManager();
		
		// Get current user for access.
		$user = User::getCurrentUser();
		
		// Get blog article.
		$blog = $em->getRepository('Blog')->getBlog($blogId, '', $user->getId());
		
		// Check if post creation is possible.
		if($blog && $user->isActive() && !$user->isGuest()) {
			$comment = new BlogComment;
			$comment
				->setTitle($title)
				->setAuthor($user)
				->setBlog($blog)
				->setContent($content)
				->setStatus(Comment::STATUS_ACTIVE);
			$em->persist($comment);
			$em->flush();
		}
		
		// Erase post submission.
		$this->refresh();
	}
	
	/**
	 * Edit blog article.
	 * Will show an edit form of the current blog article.
	 * 
	 * Must be an administrator to access.
	 */
	public function actionEdit() // @incomplete
	{
		// Post actions:
		// - actionUpdateContent: Update the blog article's content.
		
		// Handle post back.
		$actionsList = array(
			'UpdateContent',
			);
		$this->handlePostback($actionsList);
	}
	public function actionUpdateContent() // @incomplete
	{
		
	}
}