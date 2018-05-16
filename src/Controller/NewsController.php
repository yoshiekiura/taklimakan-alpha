<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

use App\Entity\News;
use App\Entity\Tags;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class NewsController extends Controller
{
    /**
     * @Route("/news", name="news")
     * @Route("/news/", name="news_trail")
     */
    public function index(Request $request)
    {

        // Do we have to show Welcome Popup ?
        $showWelcome = $request->cookies->get('show-welcome') == 'false' ? false : true;

        $filter = [];

        $tags = $request->query->get('tags') ? explode(',', $request->query->get('tags')) : [];
        if ($tags) $filter['tags'] = $tags;

        $page = $request->query->get('page') ? intval($request->query->get('page')) : 1;
        if ($page) $filter['page'] = $page - 1;

        $limit = $request->query->get('limit') ? intval($request->query->get('limit')) : 6;
        if ($limit) $filter['limit'] = $limit;

        // NB! And we have to know total number of news somehow

        // Для запроса новостей и тегов проходит несколько SQL-вызовов. Первый дергает все новости из таблицы, последующие дергают теги для КАЖДОЙ из новостей
        // Parameters: [   ] SELECT t0.id AS id_1, t0.title AS title_2, t0.lead AS lead_3, t0.text AS text_4, t0.source AS source_5, t0.image AS image_6, t0.date AS date_7, t0.active AS active_8, t0.category_id AS category_id_9 FROM news t0
        // Parameters: [ 1 ] SELECT t0.id AS id_1, t0.tag AS tag_2 FROM tags t0 INNER JOIN news_tags ON t0.id = news_tags.tags_id WHERE news_tags.news_id = ?

        $newsRepo = $this->getDoctrine()->getRepository(News::class);
        $news = $newsRepo->getNews($filter);

        $tagsRepo = $this->getDoctrine()->getRepository(Tags::class);
        $allTags = $tagsRepo->findAll();

        return $this->render('news/index.html.twig', [
            'menu' => 'news',
            'show_welcome' => $showWelcome,
            'news' => $news,
            'tags' => $allTags, // Selected tags to sort
            'filter' => [
                'sort' => null, // NB! Define sort orders later (new / older / trending / popular / etc)
                'tags' => implode($tags, ','),
            ],
            'paginator' => [
                'total' => null,   // Total news for paginator / NB! Do not count for now
                'page'  => $page,  // Current Page
                'limit' => $limit, // Max news on the page
            ],
            'page'  => $page,  // Current Page
        ]);
    }

    /**
     * @Route("/news/{id}", name="news_id", requirements={"id"="\d+"})
     * @Route("/news/{id}/", name="news_id_trail", requirements={"id"="\d+"})
     * @Route("/news/{id}/{translit}", name="news_id_translit", requirements={"id"="\d+"})
     */
    public function show($id, $translit = '', Request $request)
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

        if (!$news)
            throw $this->createNotFoundException('Sorry, this news does not exist!');

/*
        // FIXME! По быстрому дергаем теги - потом архитектура изменится, переделать
        // NB! Уже переделали :)

        $conn = $this->getDoctrine()->getEntityManager()->getConnection();
        $sql = 'SELECT tag FROM tags t JOIN news_tags nt ON nt.tags_id = t.id WHERE nt.news_id = :id';
        $query = $conn->prepare($sql);
        $query->execute([ 'id' => $id ]);
        $tags = $query->fetchAll();
*/

        $tags = array_map('trim', explode(',', $news->getTags()));
//var_dump($tags); die();
//        $tagsRepo = $this->getDoctrine()->getRepository(Tags::class);
        //$tags = $tagsRepo->findAll();

//var_dump($tags);die();

        if (strpos($news->getImage(), 'http://') !== false || strpos($news->getImage(), 'https://') !== false)
            $img = $news->getImage();
        else
            $img = 'https://' . $request->getHost() . "/images/news/" . $news->getImage();

        return $this->render('news/show.html.twig', [
            'menu' => 'news',
            'show_welcome' => $showWelcome,
            'news' => $news,
            'tags' => $tags,
            'meta' => [
                'title' => $news->getTitle(),
                'description' => $news->getLead(),
                'image' => $img,
                'date' => $news->getDate()->format('Y-m-dTH:i:sZ'),
                'url' => $request->getUri(),
                'tags' => $tags,
            ],

        ]);
    }

    // Callback Helper for AJAX News <Load More> Button

    /**
     * @Route("/news/more", name="news_more")
     */
    public function more(Request $request)
    {
        if (!$request->isXMLHttpRequest())
            throw new BadRequestHttpException("[ERR] Only AJAX requests are allowed!");

        $content = $request->getContent();
        $params = json_decode($content, true);
        $page = $params['page'] > 0 ? intval($params['page']) : 0;
        $tags = count($params['tags']) > 0 ? $params['tags'] : [];

        $filter = [];
        $filter['page'] = $page;
        $filter['tags'] = $tags;
        $filter['limit'] = 6;

        $newsRepo = $this->getDoctrine()->getRepository(News::class);
        $news = $newsRepo->getNews($filter);

        if (count($news))
            $template = $this->render('news/more.html.twig', [ 'news' => $news ])->getContent();
        else
            $template = '';

        $response = new JsonResponse([ 'page' => $page, 'tags' => $tags, 'html' => $template ]);

        return $response;
    }

}
