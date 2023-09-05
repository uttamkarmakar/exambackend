<?php

namespace Drupal\custom_about_us\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\user\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a block to display details of selected anchor users.
 *
 * @Block(
 *   id = "anchor_user_details_block",
 *   admin_label = @Translation("Anchor User Details Block"),
 * )
 */
class AnchorUserDetailsBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The configuration factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructs a new AnchorUserDetailsBlock instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The configuration factory.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigFactoryInterface $config_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->configFactory->get('custom_about_us.settings');
    $selectedUsers = $config->get('anchor_user') ?: [];

    $userDetails = [];

    foreach ($selectedUsers as $uid) {
      $user = User::load($uid['target_id']);
      if ($user) {
        $userDetails[] = [
          'name' => $user->getDisplayName(),
        ];
        $query = \Drupal::entityTypeManager()->getStorage('node')->getQuery()
          ->condition('type', 'news')
          ->condition('uid', $user->id())
          ->accessCheck(TRUE);
        $query->range(0, 3);

        $nids = $query->execute();

        $nodes = \Drupal::entityTypeManager()->getStorage('node')->loadMultiple($nids);
        $userId = $user->id();
        $newsNodes[$user->id()] = $nodes;
      }
    }
    $content = [
      '#theme' => 'anchor_user_details',
      '#user_details' => $userDetails,
      '#news_nodes' => $newsNodes,
      '#uid' => $userId,
    ];

    return $content;
  }

}
