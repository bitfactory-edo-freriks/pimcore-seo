<?php

namespace SeoBundle\DependencyInjection\Compiler\ThirdParty;

use SeoBundle\Tool\Bundle;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class RemoveCoreShopExtractorListenerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (Bundle::hasBundle('CoreShopSEOBundle', $container->getParameter('kernel.bundles')) === false) {
            return;
        }

        $definitions = [
            'coreshop.seo.extractor.description' => 'CoreShop\Component\SEO\Extractor\DescriptionExtractor',
            'coreshop.seo.extractor.title'       => 'CoreShop\Component\SEO\Extractor\TitleExtractor',
            'coreshop.seo.extractor.og'          => 'CoreShop\Component\SEO\Extractor\OGExtractor',
            'coreshop.seo.extractor.image'       => 'CoreShop\Component\SEO\Extractor\ImageExtractor',
            'coreshop.seo.extractor.document'    => 'CoreShop\Component\SEO\Extractor\DocumentExtractor'
        ];

        foreach ($definitions as $aliasDefinition => $definition) {
            if ($container->hasAlias($aliasDefinition)) {
                $container->removeAlias($aliasDefinition);
            }

            if ($container->hasDefinition($definition)) {
                $container->removeDefinition($definition);
            }
        }
    }
}
