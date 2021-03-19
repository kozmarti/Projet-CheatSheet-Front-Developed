<?php

namespace App\Controller;

use App\Model\CommentManager;
use App\Model\FavoriteManager;
use App\Model\LanguageManager;
use App\Model\PostManager;
use App\Model\UserManager;
use App\Service\FormValidator;

class MySpaceController extends AbstractController
{


    /**
     * Display My space page
     *
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function main($user)
    {
            $theUser = new UserManager();
            $theUser = $theUser->userInfos($user);
            $languageManager = new LanguageManager();
            $languageManager = $languageManager->selectAll();
            $allMyFavorites = new PostManager();
            $allMyFavorites = $allMyFavorites->selectAllMyFavorites($user);
        if (($_SERVER["REQUEST_METHOD"] === "POST")) {
            if (isset($_POST['newPostTitle']) && isset($_POST['newPostContent']) && isset($_POST['newPostLanguage'])) {
                $_SESSION['emptymessage'] = '';
                $thePost = new PostManager();
                $user = $theUser['id'];
                $title = $_POST['newPostTitle'];
                $content = $_POST['newPostContent'];
                $language = $_POST['newPostLanguage'];
                $thePost->createPost($user, $title, $content, $language);
            }
            $_SESSION['emptymessage'] = 'Remplissez toutes les données pour créer un nouveau message!';
            header('Location: /MySpace/main/' . $_SESSION['userid']);
        }
        $_SESSION['userid'] = $theUser['id'];
        if (isset($_SESSION['userid'])) {
            $postManager = new PostManager();
            $likesAndDislikes = $postManager->selectAllLikesAndDislikesPerUser($_SESSION['userid']);
            $favoriteManager = new FavoriteManager();
            $favorites = $favoriteManager->selectAllFavoritePostId($_SESSION['userid']);
            $allMyPosts = $postManager->selectAllMyPosts($user);
        } else {
            $likesAndDislikes = [];
            $favorites = [];
            $allMyPosts = [];
        }

        $_SESSION['userid'] = $theUser['id'];

        $allComments = '';
        $newComment = new CommentManager();
        $allComments = $newComment->showComments();
        $this->twig->addGlobal('session', $_SESSION);

        $colors = ['#FFADAD','#FFD6A5', '#FDFFB6', '#CAFFBF', '#9BF6FF', '#A0C4FF', '#BDB2FF', '#FFC6FF' ];
        $allPopularities = $postManager->popularityPerId();

        $colors2 = ['#FB5607','#FF006E', '#8338EC', '#3A86FF',  '#00F5D4', '#00BBF9', '#F15BB5' ];
        return $this->twig->render('MySpace/myspacepage.html.twig', [
            'favorite' => $favorites,
            'languages' => $languageManager,
            'favorites' => $allMyFavorites,
            'myposts' => $allMyPosts,
            'user' => $theUser,
            'likesAndDislikes' => $likesAndDislikes,
            'all_popularities' => $allPopularities,
            'all_comments' => $allComments,
            'colors' => $colors,
            'colors2' => $colors2
        ]);
    }

    public function add()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $_SESSION['nameErrorAlreadyExists'] ='';
            $_SESSION['emailErrorAlreadyExists'] ='';
            $userManager = new UserManager();
            if ($newUserData = $userManager->selectOneByName($_POST['name'])) {
                $_SESSION['nameErrorAlreadyExists'] = 'Ce nom est déjà utilisé, choisis en un autre';
                $errorsQueryString = http_build_query($_SESSION);
                header('Location: /#registration?' . $errorsQueryString);
            } if ($newUserData = $userManager->selectOneByEmail($_POST['email'])) {
                $_SESSION['emailErrorAlreadyExists'] = 'Cet e-mail est déjà utilisé, choisis en un autre';
                $errorsQueryString = http_build_query($_SESSION);
                header('Location: /#registration?' . $errorsQueryString);
            }
            else {
                $newUserData = [];
                $newUserData['name'] = trim($_POST['name']);
                $newUserData['email'] = trim($_POST['email']);
                $newUserData['email'] = filter_var($newUserData['email'], FILTER_VALIDATE_EMAIL);
                $newUserData['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
                $userManager->createUser($newUserData);
                $userData = $userManager->selectOneByName($_POST['name']);
                $_SESSION['user'] = $userData;
                header('Location: /MySpace/main/' . $userData['id']);
            }
        } else {
            header('Location: /#registration');
        }
    }
    public function check()
    {
        $userManager = new UserManager();
        $_POST['name'] = trim($_POST['name']);
        $_POST['password'] = trim($_POST['password']);
        if (empty($_POST['name'])) {
            $_SESSION['nameError'] = 'Renseignes ton nom';
            $errorsQueryString = http_build_query($_SESSION);
            header('Location: /#login?' . $errorsQueryString);
        }
        if (empty($_POST['password'])) {
            $_SESSION['passwordError'] = 'Renseignes ton mot de passe';
            $errorsQueryString = http_build_query($_SESSION);
            header('Location: /#login?' . $errorsQueryString);
        } else {
            if ($userData = $userManager->selectOneByName($_POST['name'])) {
                if (password_verify($_POST['password'], $userData['password'])) {
                    $_SESSION['user'] = $userData;
                    header('Location: /MySpace/main/' . $userData['id']);
                } else {
                    $_SESSION['passwordWrongError'] = 'Tu as dû te tromper sur de mot de passe';
                    $errorsQueryString = http_build_query($_SESSION);
                    header('Location: /#login?' . $errorsQueryString);
                }
            } else {
                $_SESSION['nameWrongError'] = 'Tu as dû te tromper de nom';
                $errorsQueryString = http_build_query($_SESSION);
                header('Location: /#login?' . $errorsQueryString);
            }
        }
    }

    public function adminAccess()
    {
        $userManager = new UserManager();
        $admins = $userManager->selectAdmins();
        if (isset($_SESSION['user'])) {
            foreach ($admins as $admin => $adminData) {
                if (($_SESSION['user']['id']) === ($adminData['id'])) {
                    return $this->twig->render('MySpace/admin.html.twig');
                }
            }
        } else {
            header('Location: /');
        }
    }


    public function logout()
    {
        session_destroy();
        session_unset();
        header('Location: /');
    }

    public function addLanguage()
    {
        if (($_SERVER["REQUEST_METHOD"] === "POST")) {
            $languageManager = new LanguageManager();
            $name = trim($_POST['newLanguageName']);
            $identifier = trim($_POST['newLanguageIdentifier']);
            $icon = trim($_POST['newLanguageIcon']);
            $languageManager->createLanguage($name, $identifier, $icon);
            $successMessage = 'La nouvelle catégorie a bien été ajoutée';
            return $this->twig->render('MySpace/admin.html.twig', [
                'successMessage' => $successMessage
            ]);
        }
    }
}
