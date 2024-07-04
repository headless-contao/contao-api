<?php

declare(strict_types=1);

/*
 * This file is part of janmarkuslanger/ApiBundle.
 *
 * (c) Jan-Markus Langer
 *
 * @license LGPL-3.0
 */

namespace Janmarkuslanger\ApiBundle\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Contao\ManagerPlugin\Routing\RoutingPluginInterface;
use Symfony\Component\Config\Loader\LoaderResolverInterface;
use Janmarkuslanger\ApiBundle\JanmarkuslangerApiBundle;
use Symfony\Component\HttpKernel\KernelInterface;


class Plugin implements BundlePluginInterface, RoutingPluginInterface
{
    public function getBundles(ParserInterface $parser): array
    {
        return [
            BundleConfig::create(JanmarkuslangerApiBundle::class)
                ->setLoadAfter([ContaoCoreBundle::class]),
        ];
    }

    public function getRouteCollection(LoaderResolverInterface $resolver, KernelInterface $kernel)
    {
        $file = __DIR__.'/../Resources/config/routes.yml';
        return $resolver->resolve($file)->load($file);
    }
}
