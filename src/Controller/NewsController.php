<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

use App\Entity\News;
use App\Entity\Tags;

class NewsController extends Controller
{
    /**
     * @Route("/news", name="news")
     */
    public function index(Request $request)
    {
        // Do we have to show Welcome Popup ?
        $showWelcome = $request->cookies->get('show-welcome') == 'false' ? false : true;

        $filter = [];

        if ($request->query->get('tags'))
            $filter['tags'] = explode(',', $request->query->get('tags'));

        $newsRepo = $this->getDoctrine()->getRepository(News::class);

        // Для запроса новостей и тегов проходит несколько SQL-вызовов. Первый дергает все новости из таблицы, последующие дергают теги для КАЖДОЙ из новостей
        // Parameters: [   ] SELECT t0.id AS id_1, t0.title AS title_2, t0.lead AS lead_3, t0.text AS text_4, t0.source AS source_5, t0.image AS image_6, t0.date AS date_7, t0.active AS active_8, t0.category_id AS category_id_9 FROM news t0
        // Parameters: [ 1 ] SELECT t0.id AS id_1, t0.tag AS tag_2 FROM tags t0 INNER JOIN news_tags ON t0.id = news_tags.tags_id WHERE news_tags.news_id = ?

        //$news = $newsRepo->findAll();
        $news = $newsRepo->getNews($filter);

        $tagsRepo = $this->getDoctrine()->getRepository(Tags::class);
        $tags = $tagsRepo->findAll();

        return $this->render('news/index.html.twig', [
            'menu' => 'news',
            'show_welcome' => $showWelcome,
            'news' => $news,
            'tags' => $tags,
        ]);
    }

    /**
     * @Route("/news/{id}", name="news_show")
     */
    public function show($id, Request $request)
    {
        // Do we have to show Welcome Popup ?
        $showWelcome = $request->cookies->get('show-welcome') == 'false' ? false : true;
        
//die("FULL");
//        $filter = [];

//        if ($request->query->get('tags'))
//            $filter['tags'] = explode(',', $request->query->get('tags'));

        $newsRepo = $this->getDoctrine()->getRepository(News::class);

        // Для запроса новостей и тегов проходит несколько SQL-вызовов. Первый дергает все новости из таблицы, последующие дергают теги для КАЖДОЙ из новостей
        // Parameters: [] SELECT t0.id AS id_1, t0.title AS title_2, t0.lead AS lead_3, t0.text AS text_4, t0.source AS source_5, t0.image AS image_6, t0.date AS date_7, t0.active AS active_8, t0.category_id AS category_id_9 FROM news t0
        // Parameters: [ 1 ] SELECT t0.id AS id_1, t0.tag AS tag_2 FROM tags t0 INNER JOIN news_tags ON t0.id = news_tags.tags_id WHERE news_tags.news_id = ?

        //$news = $newsRepo->findAll();
        // $news = $newsRepo->getNews($filter);

        $news = $newsRepo->findOneBy([ 'id' => $id ]);

        // FIXME! По быстрому дергаем теги - потом архитектура изменится, переделать

        $conn = $this->getDoctrine()->getEntityManager()->getConnection();
        $sql = 'SELECT tag FROM tags t JOIN news_tags nt ON nt.tags_id = t.id WHERE nt.news_id = :id';
        $query = $conn->prepare($sql);
        $query->execute([ 'id' => $id ]);
        $tags = $query->fetchAll();

//        $tagsRepo = $this->getDoctrine()->getRepository(Tags::class);
        //$tags = $tagsRepo->findAll();

//var_dump($tags);die();

        return $this->render('news/show.html.twig', [
            'menu' => 'news',
            'show_welcome' => $showWelcome,
            'news' => $news,
            'tags' => $tags,
        ]);
    }

}
