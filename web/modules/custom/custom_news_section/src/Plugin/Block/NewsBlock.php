<?php

namespace Drupal\custom_news_section\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\user\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a custom news section block.
 *
 * @Block(
 *   id = "news_section",
 *   admin_label = @Translation("Custom News Section"),
 *   category = @Translation("Custom")
 * )
 */
class NewsBlock extends BlockBase implements ContainerFactoryPluginInterface {

  protected $database;
  protected $entityTypeManager;
  protected $currentUser;
  protected $fieldDefinition;

  /**
   *Construct the class
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, Connection $database, EntityTypeManagerInterface $entityTypeManager, AccountProxyInterface $current_user, FieldDefinitionInterface $field_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->database = $database;
    $this->entityTypeManager = $entityTypeManager;
    $this->currentUser = $current_user;
    $this->fieldDefinition = $field_definition;
  }

  /**
   *Create function to inject dependencies
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('database'),
      $container->get('entity_type.manager'),
      $container->get('current_user'),
      $container->get('entity_field.manager')->getFieldDefinitions('user', 'user')['field_genre']
    );
  }

  /**
   * Implementing the build function
   */
  public function build() {
    $build = [];

    $user = User::load($this->currentUser->id());

    if ($user->hasField('field_genre')) {

      $favorite_genre = $user->get('field_genre')->first()->getValue()['target_id'];

      $query = $this->database->select('node_field_data', 'n');
      $query->fields('n', ['nid', 'title']);
      $query->condition('n.status', 1);
      $query->condition('n.type', 'news');
      $query->condition('n.field_genre_target_id', $favorite_genre);
      $query->orderBy('n.created', 'DESC');
      $query->range(0, 5);

      $result = $query->execute();

      foreach ($result as $row) {
        $build[] = [
          '#markup' => '<a href="/node/' . $row->nid . '">' . $row->title . '</a>',
        ];
      }
    }

    return $build;
  }

}
