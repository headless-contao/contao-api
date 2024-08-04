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

        $finalPages = [];

        $sql = 'SELECT * FROM tl_page';
        $pages = $this->db->fetchAllAssociative($sql);

        foreach ($pages as $page) {
            $finalPages[] = [
                'id' => $page['id'],
                'title' => $page['title'],
                'alias' => $page['alias']
            ];
        }

        if (isset($GLOBALS['TL_HOOKS']['JanmarkuslangerApiCollectPages']) && \is_array($GLOBALS['TL_HOOKS']['JanmarkuslangerApiCollectPages'])) {
            foreach ($GLOBALS['TL_HOOKS']['JanmarkuslangerApiCollectPages'] as $callback) {
                $processedPages = System::importStatic($callback[0])->{$callback[1]}($finalPages);

                if (\is_array($processedPages)) {
                    $finalPages = array_merge($finalPages, $processedPages);
                }
            }
        }

        return new JsonResponse([
            'items' => $finalPages,
            'total' => \count($finalPages),
        ]);
    }

    #[Route('/page/{alias}', name: 'page')]
    public function page(string $alias): JsonResponse
    {
        $sql = 'SELECT * FROM tl_page WHERE alias = ?';
        $page = $this->db->fetchAssociative($sql, [$alias]);

        return new JsonResponse($page);
    }

    #[Route('/content/{alias}', name: 'content')]
    public function content(string $alias): JsonResponse
    {
        $articleSql = 'SELECT * FROM tl_article a LEFT JOIN tl_page p ON a.pid = p.id WHERE p.alias = ?';
        $content = $this->db->fetchAllAssociative($articleSql, [$alias]);

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
