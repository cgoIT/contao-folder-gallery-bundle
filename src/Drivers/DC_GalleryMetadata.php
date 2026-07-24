<?php

declare(strict_types=1);

/*
 * This file is part of cgoit\contao-folder-gallery-bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) cgoIT
 * @author     cgoIT <https://cgo-it.de>
 * @license    LGPL-3.0-or-later
 */

namespace Cgoit\ContaoFolderGalleryBundle\Drivers;

use Cgoit\ContaoFolderGalleryBundle\Cache\GalleryCacheInvalidator;
use Cgoit\ContaoFolderGalleryBundle\Controller\Backend\GalleryBackendController;
use Cgoit\ContaoFolderGalleryBundle\Factory\GalleryMetadataFactory;
use Cgoit\ContaoFolderGalleryBundle\Metadata\GalleryMetadataManager;
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryMetadata;
use Contao\Config;
use Contao\CoreBundle\Csrf\ContaoCsrfTokenManager;
use Contao\CoreBundle\DataContainer\ButtonsBuilder;
use Contao\CoreBundle\DataContainer\DataContainerGlobalOperationsBuilder;
use Contao\CoreBundle\DataContainer\PaletteBuilder;
use Contao\CoreBundle\Exception\AccessDeniedException;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\DataContainer;
use Contao\EditableDataContainerInterface;
use Contao\FilesModel;
use Contao\Input;
use Contao\Message;
use Contao\StringUtil;
use Contao\System;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class DC_GalleryMetadata extends DataContainer implements EditableDataContainerInterface
{
    private GalleryMetadata $metadata;

    private \DateTimeZone|null $dateTimeZone = null;

    /**
     * Initialize the object.
     *
     * @throws AccessDeniedException
     */
    public function __construct(
        private readonly GalleryMetadataManager $metadataManager,
        private readonly GalleryMetadataFactory $metadataFactory,
        private readonly GalleryCacheInvalidator $cacheInvalidator,
        private readonly ContaoFramework $framework,
        private readonly UrlGeneratorInterface $router,
        private readonly ContaoCsrfTokenManager $csrfTokenManager,
        private readonly PaletteBuilder $paletteBuilder,
        private readonly ButtonsBuilder $buttonsBuilder,
        private readonly DataContainerGlobalOperationsBuilder $globalOperationsBuilder,
        private readonly LoggerInterface|null $logger = null,
    ) {
        parent::__construct();
    }

    public function getPalette(): string
    {
        return $this->paletteBuilder
            ->getPalette($this->strTable, (int) $this->intId, $this)
        ;
    }

    public function edit(): string
    {
        $this->handleSubmit();
        $content = $this->renderForm();

        Message::reset();

        return $content;
    }

    public function create(): void
    {
    }

    public function cut(): void
    {
    }

    public function copy(): void
    {
    }

    public function initialize(string|null $id = null): void
    {
        self::loadDataContainer(GalleryMetadata::DCA_TABLE_NAME);

        $this->framework
            ->getAdapter(System::class)
            ->loadLanguageFile('default')
        ;

        $this->intId = $id;
        $this->strTable = GalleryMetadata::DCA_TABLE_NAME;

        // Check whether the table is defined
        if (!isset($GLOBALS['TL_DCA'][$this->strTable])) {
            $this->logger?->error('Could not load data container configuration for "'.$this->strTable.'"');
            trigger_error('Could not load data container configuration', E_USER_ERROR);
        }

        $config = $this->framework->getAdapter(Config::class);
        $timezone = (string) $config->get('timeZone');
        $this->dateTimeZone = '' !== $timezone ? new \DateTimeZone($timezone) : null;

        if ($this->intId) {
            $this->loadMetadata();
        }

        // Call onload_callback (e.g. to check permissions)
        if (\is_array($GLOBALS['TL_DCA'][$this->strTable]['config']['onload_callback'] ?? null)) {
            foreach ($GLOBALS['TL_DCA'][$this->strTable]['config']['onload_callback'] as $callback) {
                if (\is_array($callback)) {
                    System::importStatic($callback[0])->{$callback[1]}($this);
                } elseif (\is_callable($callback)) {
                    $callback($this);
                }
            }
        }
    }

    protected function save($varValue): void
    {
    }

    private function loadMetadata(): void
    {
        if ($this->intId) {
            $this->metadata = $this->metadataManager->read($this->intId);
        }
    }

    private function renderBoxes(): string
    {
        $return = '';

        $boxes = $this->paletteBuilder
            ->getBoxes(
                $this->getPalette(),
                $this->strTable,
            )
        ;

        if ([] === $boxes) {
            return '';
        }

        $class = 'tl_tbox';

        foreach ($boxes as $box) {
            $return .= \sprintf(
                "\n<div class=\"%s cf\">",
                $class,
            );

            foreach ($box['fields'] as $field) {
                $return .= $this->renderField($field);
            }

            $class = 'tl_box';

            $return .= "\n</div>";
        }

        return $return;
    }

    private function renderField(string $field): string
    {
        $this->strField = $field;
        $this->strInputName = $field;

        $this->varValue = $this->normalizeValue($field);

        if (\is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['load_callback'] ?? null)) {
            foreach ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['load_callback'] as $callback) {
                if (\is_array($callback)) {
                    $this->varValue = System::importStatic($callback[0])->{$callback[1]}(
                        $this->varValue,
                        $this,
                    );
                } elseif (\is_callable($callback)) {
                    $this->varValue = $callback($this->varValue, $this);
                }
            }
        }

        return $this->row();
    }

    private function renderForm(): string
    {
        $buttons = $this->buttonsBuilder
            ->generateEditButtons(
                $this->strTable,
                false,
                false,
                false,
                $this,
            )
        ;

        $content = $this->renderHeader()
                .Message::generate()
                .$this->renderBoxes();

        $content .= '
</div>
  '.$buttons.'
</form>';

        return $this->globalOperationsBuilder
            ->initialize($this->strTable)
            ->addBackButton().'
<form id="'.$this->strTable.'" class="tl_form tl_edit_form" method="post"'
            .(!empty($this->onsubmit) ? ' onsubmit="'.implode(' ', $this->onsubmit).'"' : '')
            .'>
<div class="tl_formbody_edit">
<input type="hidden" name="FORM_SUBMIT" value="'.$this->strTable.'">
<input type="hidden" name="REQUEST_TOKEN" value="'
            .htmlspecialchars(
                $this->csrfTokenManager->getDefaultTokenValue(),
                ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5,
            )
            .'">'
            .$content
        ;
    }

    private function renderHeader(): string
    {
        return \sprintf(
            '<div class="tl_header">
    <table class="tl_header_table">
        <tbody>
            <tr>
                <td><span class="tl_label">%s</span></td>
                <td>%s</td>
            </tr>
            <tr>
                <td><span class="tl_label">%s</span></td>
                <td>%s</td>
            </tr>
        </tbody>
    </table>
</div>',
            $GLOBALS['TL_LANG']['tl_gallery_metadata']['header_label']['gallery'],
            StringUtil::specialchars($this->metadata->title ?: basename((string) $this->intId)),
            $GLOBALS['TL_LANG']['tl_gallery_metadata']['header_label']['filesystemDirectory'],
            StringUtil::specialchars($this->intId),
        );
    }

    private function handleSubmit(): void
    {
        if (Input::post('FORM_SUBMIT') !== $this->strTable) {
            return;
        }

        $arrValues = $this->createArrSubmit();

        if (\is_array($GLOBALS['TL_DCA'][$this->strTable]['config']['onbeforesubmit_callback'] ?? null)) {
            foreach ($GLOBALS['TL_DCA'][$this->strTable]['config']['onbeforesubmit_callback'] as $callback) {
                try {
                    if (\is_array($callback)) {
                        $arrValues = System::importStatic($callback[0])->{$callback[1]}($arrValues, $this);
                    } elseif (\is_callable($callback)) {
                        $arrValues = $callback($arrValues, $this);
                    }
                } catch (\Exception $e) {
                    $this->noReload = true;
                    Message::addError($e->getMessage());
                    System::getContainer()->get('request_stack')?->getMainRequest()->attributes->set('_contao_widget_error', true);

                    break;
                }

                if (!\is_array($arrValues)) {
                    throw new \RuntimeException('The onbeforesubmit_callback must return the values!');
                }
            }
        }

        if ($this->noReload) {
            return;
        }

        $this->metadata = $this->createMetadataFromPost($arrValues);

        $this->metadataManager->write($this->intId, $this->metadata);

        $this->cacheInvalidator->invalidate();

        if (\is_array($GLOBALS['TL_DCA'][$this->strTable]['config']['onsubmit_callback'] ?? null)) {
            foreach ($GLOBALS['TL_DCA'][$this->strTable]['config']['onsubmit_callback'] as $callback) {
                if (\is_array($callback)) {
                    System::importStatic($callback[0])->{$callback[1]}($this);
                } elseif (\is_callable($callback)) {
                    $callback($this);
                }
            }
        }

        if (null !== Input::post('saveNclose')) {
            Message::reset();
            $this->redirect($this->router->generate(GalleryBackendController::ROUTE_NAME));
        }

        $this->redirect(
            $this->addToUrl('id='.$this->urlEncode($this->intId)),
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function createArrSubmit(): array
    {
        $title = Input::post('title');
        $description = Input::post('description');

        $cover = Input::post('cover');

        return [
            'title' => $title,
            'description' => $description,
            'cover' => $cover,
            'publishedFrom' => Input::post('publishedFrom'),
            'publishedUntil' => Input::post('publishedUntil'),
            'sortOrder' => Input::post('sortOrder'),
            'overviewMode' => Input::post('overviewMode'),
        ];
    }

    /**
     * @param array<string, mixed> $arrSubmit
     */
    private function createMetadataFromPost(array $arrSubmit): GalleryMetadata
    {
        if ($arrSubmit['cover']) {
            $arrSubmit['cover'] = FilesModel::findByUuid($arrSubmit['cover'])?->name;
        }

        return $this->metadataFactory->create($arrSubmit, $this->dateTimeZone);
    }

    private function normalizeValue(string $field): mixed
    {
        return match (true) {
            null !== $this->metadata->$field && \in_array($field, ['publishedFrom', 'publishedUntil'], true) => $this->metadata->$field->getTimestamp(),

            null !== $this->metadata->$field && \in_array($field, ['sortOrder', 'overviewMode'], true) => $this->metadata->$field->value,

            null !== $this->metadata->$field && 'cover' === $field => FilesModel::findByPath($this->intId.'/'.$this->metadata->$field)?->uuid,

            default => $this->metadata->$field,
        };
    }
}
