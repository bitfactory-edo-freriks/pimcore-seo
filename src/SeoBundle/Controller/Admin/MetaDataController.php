<?php

namespace SeoBundle\Controller\Admin;

use Pimcore\Model\DataObject;
use Pimcore\Model\Document;
use Pimcore\Bundle\AdminBundle\Controller\AdminController;
use SeoBundle\Manager\ElementMetaDataManagerInterface;
use SeoBundle\Tool\LocaleProviderInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MetaDataController extends AdminController
{
    protected ElementMetaDataManagerInterface $elementMetaDataManager;
    protected LocaleProviderInterface $localeProvider;

    public function __construct(
        ElementMetaDataManagerInterface $elementMetaDataManager,
        LocaleProviderInterface $localeProvider
    ) {
        $this->elementMetaDataManager = $elementMetaDataManager;
        $this->localeProvider = $localeProvider;
    }

    public function getMetaDataDefinitionsAction(): JsonResponse
    {
        return $this->json([
            'configuration' => $this->elementMetaDataManager->getMetaDataIntegratorConfiguration()
        ]);
    }

    /**
     * @throws \Exception
     */
    public function getElementMetaDataConfigurationAction(Request $request): JsonResponse
    {
        $element = null;
        $availableLocales = null;

        $elementId = (int) $request->query->get('elementId', 0);
        $elementType = $request->query->get('elementType');

        if ($elementType === 'object') {
            $element = DataObject::getById($elementId);
            $availableLocales = $this->localeProvider->getAllowedLocalesForObject($element);
        } elseif ($elementType === 'document') {
            $element = Document::getById($elementId);
        }

        $configuration = $this->elementMetaDataManager->getMetaDataIntegratorBackendConfiguration($element);
        $data = $this->elementMetaDataManager->getElementDataForBackend($elementType, $elementId);

        return $this->adminJson([
            'success'          => true,
            'data'             => $data,
            'availableLocales' => $availableLocales,
            'configuration'    => $configuration,
        ]);
    }

    /**
     * @throws \Exception
     */
    public function setElementMetaDataConfigurationAction(Request $request): JsonResponse
    {
        $elementId = (int) $request->request->get('elementId', 0);
        $elementType = $request->request->get('elementType');
        $integratorValues = json_decode($request->request->get('integratorValues'), true, 512, JSON_THROW_ON_ERROR);

        if (!is_array($integratorValues)) {
            return $this->adminJson(['success' => true]);
        }

        foreach ($integratorValues as $integratorName => $integratorData) {
            $sanitizedData = is_array($integratorData) ? $integratorData : [];
            $this->elementMetaDataManager->saveElementData($elementType, $elementId, $integratorName, $sanitizedData);
        }

        return $this->adminJson([
            'success' => true
        ]);
    }

    /**
     * @throws \Exception
     */
    public function generateMetaDataPreviewAction(Request $request): Response
    {
        $elementId = (int) $request->query->get('elementId', 0);
        $elementType = $request->query->get('elementType', '');

        $template = $request->query->get('template', 'none');
        $integratorName = $request->query->get('integratorName');
        $data = json_decode($request->query->get('data', ''), true, 512, JSON_THROW_ON_ERROR);

        if (empty($data)) {
            $data = [];
        }

        $previewData = $this->elementMetaDataManager->generatePreviewDataForElement($elementType, $elementId, $integratorName, $template, $data);

        return $this->render($previewData['path'], $previewData['params']);
    }
}
