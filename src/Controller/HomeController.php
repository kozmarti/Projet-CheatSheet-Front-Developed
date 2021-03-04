<?php
/**
 * Created by PhpStorm.
 * User: aurelwcs
 * Date: 08/04/19
 * Time: 18:40
 */

namespace App\Controller;

use App\Model\CommentManager;
use App\Model\LanguageManager;
use App\Model\PostManager;
use App\Model\FavoriteManager;

class HomeController extends AbstractController
{

    /**
     * Display home page
     *
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function index()
    {
        $languageManager = new LanguageManager();
        $categories = $languageManager->selectAll();
        $allPostManager = new PostManager();

        $allPostsWithLanguages = $allPostManager->selectAllWithLanguage();
        $allPostsOrderedByDate = $allPostManager->selectPostsOrderedBy('creation_at');
        $allPostsOrderedByPopularity = $allPostManager->selectPostsOrderedBy('popularity');

        $wordToSearch = '';
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            if (isset($_POST['cheat-search'])) {
                $wordToSearch = $_POST['cheat-search'];
            }
        }
        if ($wordToSearch === '') {
            $allPostByKeyword = [];
        } else {
            $allPostByKeyword = $allPostManager->postByKeyword($wordToSearch);
        }
        $hasResult = false;
        if ($wordToSearch !== '' && empty($allPostByKeyword)) {
            $hasResult = true;
        }

        if (isset($_SESSION['userid'])) {
            $likesAndDislikes = $allPostManager->selectAllLikesAndDislikesPerUser($_SESSION['userid']);

            $favoriteManager = new FavoriteManager();
            $favorites = $favoriteManager->selectAllFavoritePostId($_SESSION['userid']);
        } else {
            $likesAndDislikes = [];
            $favorites = [];
        }

        if ($_SERVER["REQUEST_METHOD"] === "POST" && !empty($_POST['comment'])) {
                    $newComment = new CommentManager();
                    $content = $_POST ['comment'];
                    $userid = $_SESSION['userid'];
                    $postid = $_POST['postid'];
                    $newComment->addComment($userid, $content, $postid);
        }
        $allComments = '';
        $newComment = new CommentManager();
        $allComments = $newComment->showComments();

        $colors = ['#FFADAD','#FFD6A5', '#FDFFB6', '#CAFFBF', '#9BF6FF', '#A0C4FF', '#BDB2FF', '#FFC6FF' ];
        $colors2 = ['#FB5607','#FF006E', '#8338EC', '#3A86FF',  '#00F5D4', '#00BBF9', '#F15BB5' ];

        $allPopularities = $allPostManager->popularityPerId();
   
        return $this->twig->render('Home/index.html.twig', [
            'favorite' => $favorites,
            'languages' => $categories,
            'all_posts_by_date' => $allPostsOrderedByDate,
            'all_posts_by_pop' => $allPostsOrderedByPopularity,
            'search' => $allPostByKeyword,
            'keyword' => $wordToSearch,
            'search_without_result' => $hasResult,
            'likesAndDislikes' => $likesAndDislikes,
            'all_comments' => $allComments,
            'all_popularities' => $allPopularities,
            'colors' => $colors,
            'colors2' => $colors2
        ]);
    }

    public function deletepost($id)
    {
        if ($_SESSION['user']['admin'] === '1') {
            $postManager = new PostManager();
            $postManager->delete($id);
            header('Location: /');
        }
        header('Location: /');
    }

    public function deleteUserPost($id)
    {
        $postManager = new PostManager();
        $postManager->deleteUserPost($id);
        header('Location: ' . $_SERVER['HTTP_REFERER']);
    }
}
