<?php

declare(strict_types=1);

/*
 * This file is part of janmarkuslanger/ApiBundle.
 *
 * (c) Jan-Markus Langer
 *
 * @license LGPL-3.0-or-later
 */

namespace Janmarkuslanger\ApiBundle\Controller;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\System;
use Doctrine\DBAL\Connection;
use Janmarkuslanger\ApiBundle\Utils\ElementHelper;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api', name: ApiController::class)]
class ApiController
{
    public function __construct(
        private readonly Connection $db,
        private readonly ContaoFramework $framework,
    ) {
        $this->framework->initialize();
    }

    #[Route('/pages', name: 'pages')]
    public function pages(): JsonResponse
    {
        $sql = 'SELECT * FROM tl_page';
        $pages = $this->db->fetchAllAssociative($sql);

        return new JsonResponse([
            'items' => $pages,
            'total' => \count($pages),
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
            $elements = $this->db->fetchAllAssociative($elementSql, [$article['id']]);

            foreach ($elements as &$element) {
                $element = ElementHelper::processElement($element);

                if (isset($GLOBALS['TL_HOOKS']['JanmarkuslangerApiProcessElement']) && \is_array($GLOBALS['TL_HOOKS']['JanmarkuslangerApiProcessElement'])) {
                    foreach ($GLOBALS['TL_HOOKS']['JanmarkuslangerApiProcessElement'] as $callback) {
                        $processedElement = System::importStatic($callback[0])->{$callback[1]}($element, $pageId);

                        if (\is_array($processedElement)) {
                            $element = $processedElement;
                        }
                    }
                }
            }

            $article['elements'] = $elements;
        }

        return new JsonResponse([
            'items' => $content,
            'total' => \count($content),
        ]);
    }
}
