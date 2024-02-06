<?php

declare(strict_types = 1);

namespace Drupal\ui_skins\HookHandler;

use Drupal\Component\Utility\Html;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Template\AttributeHelper;
use Drupal\ui_skins\Theme\ThemePluginManagerInterface;
use Drupal\ui_skins\UiSkinsInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Inject theme.
 */
class PreprocessHtml implements ContainerInjectionInterface {

  /**
   * The theme plugin manager.
   *
   * @var \Drupal\ui_skins\Theme\ThemePluginManagerInterface
   */
  protected ThemePluginManagerInterface $themePluginManager;

  /**
   * Constructor.
   */
  public function __construct(ThemePluginManagerInterface $themePluginManager) {
    $this->themePluginManager = $themePluginManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new self(
      $container->get('plugin.manager.ui_skins.theme')
    );
  }

  /**
   * Inject attributes.
   */
  public function preprocess(array &$variables): void {
    $ui_skins_theme_setting = \theme_get_setting(UiSkinsInterface::THEME_THEME_SETTING_KEY);
    if (!\is_string($ui_skins_theme_setting) || empty($ui_skins_theme_setting)) {
      return;
    }

    /** @var \Drupal\ui_skins\Definition\ThemeDefinition $plugin_definition */
    $plugin_definition = $this->themePluginManager->getDefinition($ui_skins_theme_setting, FALSE);
    if ($plugin_definition == NULL) {
      return;
    }

    $target = $plugin_definition->getComputedTarget();
    $key = $plugin_definition->getKey();
    $value = $plugin_definition->getValue();
    if ($key == 'class') {
      $value = [
        Html::getClass($value),
      ];
    }

    $variables[$target] = AttributeHelper::mergeCollections(
      $variables[$target],
      [
        $key => $value,
      ]
    );

    $library = $plugin_definition->getLibrary();
    if (!empty($library)) {
      $variables['#attached']['library'][] = $library;
    }
  }

}
