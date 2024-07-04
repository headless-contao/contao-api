<?php

namespace Janmarkuslanger\ApiBundle\Controller;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\System;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api', name: ApiController::class)]
class ApiController
{
    public function __construct(
        private readonly Connection $db,
        private readonly ContaoFramework $framework
    )
    {
        $this->framework->initialize();
    }

    #[Route('/pages', name: 'pages')]
    public function pages(): JsonResponse
    {
        $sql = 'SELECT * FROM tl_page';
        $pages = $this->db->fetchAllAssociative($sql);

        return new JsonResponse([
            'items' => $pages,
            'total' => count($pages)
        ]);
    }

    #[Route('/page/{id}', name: 'page')]
    public function page(int $id): JsonResponse
    {
        $sql = 'SELECT * FROM tl_page WHERE id = ?';
        $page = $this->db->fetchAssociative($sql, [$id]);

        return new JsonResponse($page);
    }

    #[Route('/content/{pageId}', name: 'content')]
    public function content(int $pageId): JsonResponse
    {
        $articleSql = 'SELECT * FROM tl_article WHERE pid = ?';
        $content = $this->db->fetchAllAssociative($articleSql, [$pageId]);

        foreach ($content as &$article) {
            $elementSql = 'SELECT * FROM tl_content WHERE pid = ?';
            $article['elements'] = $this->db->fetchAllAssociative($elementSql, [$article['id']]);
        }

        if (isset($GLOBALS['TL_HOOKS']['apiGetPageContent']) && \is_array($GLOBALS['TL_HOOKS']['apiGetPageContent']))
        {
            $this->framework->initialize();
            foreach ($GLOBALS['TL_HOOKS']['apiGetPageContent'] as $callback)
            {
                $content = System::importStatic($callback[0])->{$callback[1]}($content, $pageId);
            }
        }

        return new JsonResponse([
            'items' => $content,
            'total' => count($content)
        ]);
    }
}