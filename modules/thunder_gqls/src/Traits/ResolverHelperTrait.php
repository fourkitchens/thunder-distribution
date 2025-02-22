<?php

namespace Drupal\thunder_gqls\Traits;

use Drupal\graphql\GraphQL\Resolver\ResolverInterface;
use Drupal\graphql\GraphQL\ResolverBuilder;

/**
 * Helper functions for field resolvers.
 */
trait ResolverHelperTrait {

  /**
   * ResolverBuilder.
   *
   * @var \Drupal\graphql\GraphQL\ResolverBuilder
   */
  protected $builder;

  /**
   * ResolverRegistryInterface.
   *
   * @var \Drupal\graphql\GraphQL\ResolverRegistryInterface
   */
  protected $registry;

  /**
   * Add field resolver to registry, if it does not already exist.
   *
   * @param string $type
   *   The type name.
   * @param string $field
   *   The field name.
   * @param \Drupal\graphql\GraphQL\Resolver\ResolverInterface $resolver
   *   The field resolver.
   */
  protected function addFieldResolverIfNotExists(string $type, string $field, ResolverInterface $resolver): void {
    if (!$this->registry->getFieldResolver($type, $field)) {
      $this->registry->addFieldResolver($type, $field, $resolver);
    }
  }

  /**
   * Create the ResolverBuilder.
   */
  protected function createResolverBuilder(): void {
    $this->builder = new ResolverBuilder();
  }

  /**
   * Produces an entity_reference field.
   *
   * @param string $field
   *   Name of the filed.
   * @param \Drupal\graphql\GraphQL\Resolver\ResolverInterface|null $entity
   *   Entity to get the field property.
   *
   * @return \Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerProxy
   *   The field data producer.
   */
  public function fromEntityReference(string $field, ResolverInterface $entity = NULL) {
    return $this->builder->produce('entity_reference')
      ->map('field', $this->builder->fromValue($field))
      ->map('entity', $entity ?: $this->builder->fromParent());
  }

  /**
   * Produces an entity_reference_revisions field.
   *
   * @param string $field
   *   Name of the filed.
   * @param \Drupal\graphql\GraphQL\Resolver\ResolverInterface|null $entity
   *   Entity to get the field property.
   *
   * @return \Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerProxy
   *   The field data producer.
   */
  public function fromEntityReferenceRevisions(string $field, $entity = NULL) {
    return $this->builder->produce('entity_reference_revisions')
      ->map('field', $this->builder->fromValue($field))
      ->map('entity', $entity ?: $this->builder->fromParent())
      ->map('language', $this->builder->fromPath('entity', 'langcode.value', $this->builder->fromParent()));
  }

  /**
   * Define callback field resolver for a type.
   *
   * @param string $type
   *   Type to add fields.
   * @param array $fields
   *   The fields.
   */
  public function addSimpleCallbackFields(string $type, array $fields): void {
    foreach ($fields as $field) {
      $this->addFieldResolverIfNotExists($type, $field,
        $this->builder->callback(fn($arr) => $arr[$field])
      );
    }
  }

  /**
   * Produces an entity from a given path.
   *
   * @param \Drupal\graphql\GraphQL\Resolver\ResolverInterface $path
   *   The path resolver.
   *
   * @return \Drupal\graphql\GraphQL\Resolver\ResolverInterface
   *   The resolved entity.
   */
  public function fromRoute(ResolverInterface $path) {
    return $this->builder->compose(
      $this->builder->produce('route_load')
        ->map('path', $path),
      $this->builder->produce('route_entity')
        ->map('url', $this->builder->fromParent())
        ->map('language', $this->builder->produce('thunder_language')
          ->map('path', $path)
        )
    );
  }

  /**
   * Takes the bundle name and returns the schema name.
   *
   * @param string $bundleName
   *   The bundle name.
   *
   * @return string
   *   Returns the mapped bundle name.
   */
  protected function mapBundleToSchemaName(string $bundleName) {
    return str_replace('_', '', ucwords($bundleName, '_'));
  }

}
