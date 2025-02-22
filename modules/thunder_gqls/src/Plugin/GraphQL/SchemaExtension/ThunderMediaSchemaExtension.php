<?php

namespace Drupal\thunder_gqls\Plugin\GraphQL\SchemaExtension;

use Drupal\graphql\GraphQL\ResolverRegistryInterface;
use Drupal\thunder_gqls\GraphQL\MediaTypeResolver;

/**
 * The media schema extension.
 *
 * @SchemaExtension(
 *   id = "thunder_media",
 *   name = "Media extension",
 *   description = "Adds media entities and their fields (required).",
 *   schema = "thunder"
 * )
 */
class ThunderMediaSchemaExtension extends ThunderSchemaExtensionPluginBase {

  /**
   * {@inheritdoc}
   */
  public function registerResolvers(ResolverRegistryInterface $registry): void {
    parent::registerResolvers($registry);

    $this->registry->addTypeResolver(
      'Media',
      new MediaTypeResolver($registry->getTypeResolver('Media'))
    );

    $this->registry->addTypeResolver(
      'Video',
      new MediaTypeResolver($registry->getTypeResolver('Video'))
    );

    $this->resolveFields();
  }

  /**
   * Add image media field resolvers.
   */
  protected function resolveFields(): void {
    // Image.
    $this->resolveMediaInterfaceFields('MediaImage');
    $this->addFieldResolverIfNotExists('MediaImage', 'copyright',
      $this->builder->fromPath('entity', 'field_copyright.value')
    );

    $this->addFieldResolverIfNotExists('MediaImage', 'description',
      $this->builder->fromPath('entity', 'field_description.processed')
    );

    $this->addFieldResolverIfNotExists('MediaImage', 'src',
      $this->builder->compose(
        $this->builder->fromPath('entity', 'field_image.entity'),
        $this->builder->produce('image_url')
          ->map('entity', $this->builder->fromParent())
      )
    );

    $this->addFieldResolverIfNotExists('MediaImage', 'derivative',
      $this->builder->compose(
        $this->builder->fromPath('entity', 'field_image.entity'),
        $this->builder->produce('image_derivative')
          ->map('entity', $this->builder->fromParent())
          ->map('style', $this->builder->fromArgument('style')),
        $this->builder->callback(function ($values) {
          if (!empty($values['url'])) {
            return $values + ['src' => $values['url']];
          }
          return;
        })
      )
    );

    $this->addFieldResolverIfNotExists('MediaImage', 'focalPoint',
      $this->builder->compose(
        $this->builder->fromPath('entity', 'field_image.entity'),
        $this->builder->produce('focal_point')
          ->map('file', $this->builder->fromParent())
      )
    );

    $this->addFieldResolverIfNotExists('MediaImage', 'width',
      $this->builder->fromPath('entity', 'field_image.width')
    );

    $this->addFieldResolverIfNotExists('MediaImage', 'height',
      $this->builder->fromPath('entity', 'field_image.height')
    );

    $this->addFieldResolverIfNotExists('MediaImage', 'title',
      $this->builder->fromPath('entity', 'field_image.title')
    );

    $this->addFieldResolverIfNotExists('MediaImage', 'alt',
      $this->builder->fromPath('entity', 'field_image.alt')
    );

    $this->addFieldResolverIfNotExists('MediaImage', 'tags',
      $this->fromEntityReference('field_tags')
    );

    $this->addFieldResolverIfNotExists('MediaImage', 'source',
      $this->builder->fromPath('entity', 'field_source.value')
    );

    // Video.
    $this->resolveMediaInterfaceFields('MediaVideo');

    $this->addFieldResolverIfNotExists('MediaVideo', 'src',
      $this->builder->produce('media_source_field')->map('media', $this->builder->fromParent())
    );

    $this->addFieldResolverIfNotExists('MediaVideo', 'username',
      $this->builder->fromPath('entity', 'field_author.value')
    );

    $this->addFieldResolverIfNotExists('MediaVideo', 'caption',
      $this->builder->fromPath('entity', 'field_caption.processed')
    );

    $this->addFieldResolverIfNotExists('MediaVideo', 'copyright',
      $this->builder->fromPath('entity', 'field_copyright.value')
    );

    $this->addFieldResolverIfNotExists('MediaVideo', 'description',
      $this->builder->fromPath('entity', 'field_description.processed')
    );

    $this->addFieldResolverIfNotExists('MediaVideo', 'source',
      $this->builder->fromPath('entity', 'field_source.value')
    );

  }

}
