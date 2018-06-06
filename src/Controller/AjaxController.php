<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
// use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

use App\Entity\Likes;
use App\Entity\Rating;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

use Symfony\Component\Security\Core\User\UserInterface;

class AjaxController extends Controller
{

    /**
     * @Route("/ajax/like", name="ajax_like")
     * @Method({"POST"})
     */
    public function like(UserInterface $user = null, Request $request)
    {
        if (!$request->isXMLHttpRequest())
            throw new BadRequestHttpException("[ERR] Only AJAX requests are allowed!");

        if (!$user)
            throw new AccessDeniedHttpException("[ERR] You are not logged in!");

        $user = $this->getUser();
        $user_id = $user->getId();

        $allowedTypes = [ 'course', 'lecture', 'news' ];

        $content = $request->getContent();
        $params = json_decode($content, true);

        $type = $params['type'] ;
        if (!in_array($type, $allowedTypes))
            throw new AccessDeniedHttpException("[ERR] Wrong content type!");

        $id = intval($params['id']);
        $status = intval($params['status']);

        $likesRepo = $this->getDoctrine()->getRepository(Likes::class);
        $like = $likesRepo->findOneBy(['content_type' => $type, 'content_id' => $id, 'user_id' => $user_id]);

        if (!$like) {
            $like = new Likes();
            $like->setType($type);
            $like->setContentId($id);
            $like->setUserId($user_id);
        }

        $like->setDate(new \DateTime());
        $like->setStatus($status);

        try {
            $em = $this->getDoctrine()->getManager();
            $em->persist($like);
            $em->flush();

            $code = 200;
            $error = '';
        }
        catch (\Exception $e) {
//            if (strpos($e->getMessage(), 'SQLSTATE[23000]')) {
//                $code = 200;
//                $error = '';
//            } else {
                $code = 500;
                $error = $e->getMessage();
//            }
        }

        $response = new JsonResponse([ 'code' => $code, 'error' => $error, 'type' => $type, 'id' => $id, 'status' => $status ]);
        return $response;

/*

        $filter = [];
        $filter['page'] = $page;
        $filter['tags'] = $tags;
        $filter['limit'] = 6;

        $newsRepo = $this->getDoctrine()->getRepository(News::class);
        $news = $newsRepo->getNews($filter);

        if (count($news))
            $template = $this->render('news/more.html.twig', [ 'news' => $news, 'page' => $page ])->getContent();
        else
            $template = '';

        $response = new JsonResponse([ 'page' => $page, 'tags' => $tags, 'html' => $template ]);

        return $response;
*/
    }

    /**
     * @Route("/ajax/rating", name="ajax_rating")
     * @Method({"POST"})
     */
    public function rating(UserInterface $user = null, Request $request)
    {
        if (!$request->isXMLHttpRequest())
            throw new BadRequestHttpException("[ERR] Only AJAX requests are allowed!");

        if (!$user)
            throw new AccessDeniedHttpException("[ERR] You are not logged in!");

        $user = $this->getUser();
        $user_id = $user->getId();

        $allowedTypes = [ 'course', 'lecture' ];

        $content = $request->getContent();
        $params = json_decode($content, true);

        $type = $params['type'] ;
        if (!in_array($type, $allowedTypes))
            throw new AccessDeniedHttpException("[ERR] Wrong content type!");

        $id = intval($params['id']);
        $rating = intval($params['rating']);

        if (!$type || !$id)
            throw new AccessDeniedHttpException("[ERR] Choose content for rating!");

        $ratingRepo = $this->getDoctrine()->getRepository(Rating::class);
        $obj = $ratingRepo->findOneBy(['content_type' => $type, 'content_id' => $id, 'user_id' => $user_id]);

        if (!$obj) {
            $obj = new Rating();
            $obj->setType($type);
            $obj->setContentId($id);
            $obj->setUserId($user_id);
        }

        $obj->setDate(new \DateTime());
        $obj->setRating($rating);

        try {
            $em = $this->getDoctrine()->getManager();
            $em->persist($obj);
            $em->flush();

            $code = 200;
            $error = '';
        }
        catch (\Exception $e) {
//            if (strpos($e->getMessage(), 'SQLSTATE[23000]')) {
//                $code = 200;
//                $error = '';
//            } else {
                $code = 500;
                $error = $e->getMessage();
//            }
        }

        $response = new JsonResponse([ 'code' => $code, 'error' => $error, 'type' => $type, 'id' => $id, 'rating' => $rating ]);
        return $response;

    }

    /**
     * @Route("/ajax/subscribe-to-source", name="ajax_subscribe_to_source")
     * @Method({"POST"})
     */
    public function subscribeToSource(UserInterface $user = null, Request $request)
    {
        if (!$request->isXMLHttpRequest())
            throw new BadRequestHttpException("[ERR] Only AJAX requests are allowed!");

        if (!$user)
            throw new AccessDeniedHttpException("[ERR] You are not logged in!");

        $user = $this->getUser();
        $user_id = $user->getId();

        $allowedTypes = [ 'source' ];

        $content = $request->getContent();
        $params = json_decode($content, true);

        $type = $params['type'] ;
        if (!in_array($type, $allowedTypes))
            throw new AccessDeniedHttpException("[ERR] Wrong content type!");

        $id = intval($params['id']);
        $status = intval($params['status']);

        $likesRepo = $this->getDoctrine()->getRepository(Likes::class);
        $like = $likesRepo->findOneBy(['content_type' => $type, 'content_id' => $id, 'user_id' => $user_id]);

        if (!$like) {
            $like = new Likes();
            $like->setType($type);
            $like->setContentId($id);
            $like->setUserId($user_id);
        }

        $like->setDate(new \DateTime());
        $like->setStatus($status);

        try {
            $em = $this->getDoctrine()->getManager();
            $em->persist($like);
            $em->flush();

            $code = 200;
            $error = '';
        }
        catch (\Exception $e) {
//            if (strpos($e->getMessage(), 'SQLSTATE[23000]')) {
//                $code = 200;
//                $error = '';
//            } else {
                $code = 500;
                $error = $e->getMessage();
//            }
        }

        $response = new JsonResponse([ 'code' => $code, 'error' => $error, 'type' => $type, 'id' => $id, 'status' => $status ]);
        return $response;

/*

        $filter = [];
        $filter['page'] = $page;
        $filter['tags'] = $tags;
        $filter['limit'] = 6;

        $newsRepo = $this->getDoctrine()->getRepository(News::class);
        $news = $newsRepo->getNews($filter);

        if (count($news))
            $template = $this->render('news/more.html.twig', [ 'news' => $news, 'page' => $page ])->getContent();
        else
            $template = '';

        $response = new JsonResponse([ 'page' => $page, 'tags' => $tags, 'html' => $template ]);

        return $response;
*/
    }


}
