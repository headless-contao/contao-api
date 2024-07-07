<?php

namespace Janmarkuslanger\ApiBundle\Utils;

use Contao\StringUtil;

class ElementHelper {
    private static function isCoreType(string $type): bool
    {
        return in_array($type, ['text', 'headline', 'image', 'gallery', 'list', 'table', 'hyperlink', 'form', 'article']);
    }

    public static function processElement(array $element): array
    {

        if (ElementHelper::isCoreType($element['type'])) {
            $element['headline'] = StringUtil::deserialize($element['headline']);
            return $element;
        }

        return $element;
    }
}