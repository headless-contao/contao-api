<?php

declare(strict_types=1);

/*
 * This file is part of janmarkuslanger/ApiBundle.
 *
 * (c) Jan-Markus Langer
 *
 * @license LGPL-3.0-or-later
 */

namespace Janmarkuslanger\ApiBundle\Utils;

use Contao\StringUtil;

class ElementHelper
{
    private static function isCoreType(string $type): bool
    {
        return \in_array($type, ['text', 'headline', 'image', 'gallery', 'list', 'table', 'hyperlink', 'form', 'article'], true);
    }

    public static function processElement(array $element): array
    {
        if (self::isCoreType($element['type'])) {
            $element['headline'] = StringUtil::deserialize($element['headline']);

            return $element;
        }

        return $element;
    }
}
