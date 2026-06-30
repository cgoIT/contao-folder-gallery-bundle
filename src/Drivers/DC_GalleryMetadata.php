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

use Cgoit\ContaoFolderGalleryBundle\Metadata\GalleryMetadataManager;
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryMetadata;
use Contao\CoreBundle\Exception\AccessDeniedException;
use Contao\DataContainer;
use Contao\EditableDataContainerInterface;
use Contao\Environment;
use Contao\Input;
use Contao\Message;
use Contao\System;
use Symfony\Component\Security\Csrf\CsrfToken;

final class DC_GalleryMetadata extends DataContainer implements EditableDataContainerInterface
{
    private GalleryMetadataManager $metadataManager;

    private GalleryMetadata $metadata;

    /**
     * Initialize the object.
     *
     * @throws AccessDeniedException
     */
    public function __construct(string $strTable)
    {
        parent::__construct();

        $this->initialize($strTable);
    }

    public function getPalette(): string
    {
        return System::getContainer()
            ->get('contao.data_container.palette_builder')
            ->getPalette($this->strTable, (int) $this->intId, $this)
        ;
    }

    public function edit(): string
    {
        $content = $this->renderBoxes();

        return $this->renderForm($content);
    }

    public function create(): void
    {
        // TODO: Implement create() method.
    }

    public function cut(): void
    {
        // TODO: Implement cut() method.
    }

    public function copy(): void
    {
        // TODO: Implement copy() method.
    }

    protected function save($varValue): void
    {
        // TODO: Implement save() method.
    }

    private function initialize(string $strTable): void
    {
        self::loadDataContainer($strTable);

        $container = System::getContainer();
        $objSession = $container->get('request_stack')->getSession();
        $request = $container->get('request_stack')->getCurrentRequest();

        $metadataManager = $container->get(GalleryMetadataManager::class);

        if (!$metadataManager instanceof GalleryMetadataManager) {
            throw new \RuntimeException(\sprintf('Expected service "%s" to be an instance of "%s", got "%s".', GalleryMetadataManager::class, GalleryMetadataManager::class, get_debug_type($metadataManager)));
        }

        $this->metadataManager = $metadataManager;

        // Check the request token (see #4007)
        if ((!$request || $request->isMethodSafe()) && !\in_array(Input::get('act'), [null, 'edit'], true) && (null === Input::get('rt') || !$container->get('contao.csrf.token_manager')->isTokenValid(new CsrfToken($container->getParameter('contao.csrf_token_name'), Input::get('rt'))))) {
            $objSession->set('INVALID_TOKEN_URL', Environment::get('requestUri'));
            $this->redirect($container->get('router')->generate('contao_backend_confirm'));
        }

        $this->intId = Input::get('id', true);
        $this->strTable = $strTable;

        // Check whether the table is defined
        if (!$strTable || !isset($GLOBALS['TL_DCA'][$strTable])) {
            $container->get('monolog.logger.contao.error')->error('Could not load data container configuration for "'.$strTable.'"');
            trigger_error('Could not load data container configuration', E_USER_ERROR);
        }

        $this->loadMetadata();

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

    private function loadMetadata(): void
    {
        $this->metadata = $this->metadataManager->read($this->intId);
    }

    private function renderBoxes(): string
    {
        $return = '';

        $boxes = System::getContainer()
            ->get('contao.data_container.palette_builder')
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

        $varValue = $this->metadata->$field;

        if ('publishedFrom' === $field || 'publishedUntil' === $field) {
            $varValue = $this->metadata->$field->getTimestamp();
        }

        $this->varValue = $varValue;

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

    private function renderForm(string $content): string
    {
        $container = System::getContainer();

        $buttons = $container
            ->get('contao.data_container.buttons_builder')
            ->generateEditButtons(
                $this->strTable,
                false,
                false,
                false,
                $this,
            )
        ;

        $content .= '
</div>
  '.$buttons.'
</form>';
        $content = $container
            ->get('contao.data_container.global_operations_builder')
            ->initialize($this->strTable)
            ->addBackButton().'
<form id="'.$this->strTable.'" class="tl_form tl_edit_form" method="post"'
            .(!empty($this->onsubmit) ? ' onsubmit="'.implode(' ', $this->onsubmit).'"' : '')
            .'>
<div class="tl_formbody_edit">
<input type="hidden" name="FORM_SUBMIT" value="'.$this->strTable.'">
<input type="hidden" name="REQUEST_TOKEN" value="'
            .htmlspecialchars(
                (string) $container->get('contao.csrf.token_manager')->getDefaultTokenValue(),
                ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5,
            )
            .'">'
            .$content
        ;

        $this->handleSubmit();

        return $content;
    }

    private function handleSubmit(): void
    {
        if ($this->noReload || Input::post('FORM_SUBMIT') !== $this->strTable) {
            return;
        }

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
            $this->redirect($this->getReferer());
        }

        $this->redirect(
            $this->addToUrl('id='.$this->urlEncode($this->intId)),
        );
    }
}
