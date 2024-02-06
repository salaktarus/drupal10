<?php

declare(strict_types = 1);

namespace Drupal\Tests\ui_skins\Unit;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\Tests\UnitTestCase;
use Drupal\ui_skins\Definition\ThemeDefinition;
use Drupal\ui_skins\Theme\ThemePluginManager;
use Drupal\ui_skins_test\DummyThemePluginManager;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Test the CSS variable plugin manager.
 *
 * @group ui_skins
 *
 * @coversDefaultClass \Drupal\ui_skins\Theme\ThemePluginManager
 */
class ThemePluginManagerTest extends UnitTestCase {

  /**
   * The container.
   *
   * @var \Symfony\Component\DependencyInjection\TaggedContainerInterface
   */
  protected $container;

  /**
   * The string translation service.
   *
   * @var \Drupal\Core\StringTranslation\TranslationInterface
   */
  protected TranslationInterface $stringTranslation;

  /**
   * The themes plugin manager.
   *
   * @var \Drupal\ui_skins_test\DummyThemePluginManager
   */
  protected DummyThemePluginManager $themePluginManager;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->container = new ContainerBuilder();
    $this->container->set('string_translation', $this->getStringTranslationStub());

    // Set up for this class.
    /** @var \Drupal\Core\Extension\ModuleHandlerInterface|\PHPUnit\Framework\MockObject\MockObject $moduleHandler */
    $moduleHandler = $this->createMock(ModuleHandlerInterface::class);
    $moduleHandler->expects($this->any())
      ->method('getModuleDirectories')
      ->willReturn([]);

    /** @var \Drupal\Core\Extension\ThemeHandlerInterface|\PHPUnit\Framework\MockObject\MockObject $themeHandler */
    $themeHandler = $this->createMock(ThemeHandlerInterface::class);
    $themeHandler->expects($this->any())
      ->method('getThemeDirectories')
      ->willReturn([]);

    $cache = $this->createMock(CacheBackendInterface::class);

    $this->themePluginManager = new DummyThemePluginManager($cache, $moduleHandler, $themeHandler);
  }

  /**
   * Tests the constructor.
   *
   * @covers ::__construct
   */
  public function testConstructor(): void {
    $this->assertInstanceOf(
      ThemePluginManager::class,
      $this->themePluginManager
    );
  }

  /**
   * Tests the processDefinition().
   *
   * @covers ::processDefinition
   */
  public function testProcessDefinitionWillReturnException(): void {
    $plugin_id = 'test';
    $definition = ['no_id' => $plugin_id];
    try {
      $this->themePluginManager->processDefinition($definition, $plugin_id);
    }
    catch (PluginException $exception) {
      $this->assertTrue(TRUE, 'The expected exception happened.');
    }
  }

  /**
   * Tests the processDefinition().
   *
   * @covers ::processDefinition
   */
  public function testProcessDefinition(): void {
    $plugin_id = 'test';
    $definition = ['id' => $plugin_id];

    $expected = new ThemeDefinition($definition);

    /** @var \Drupal\ui_skins\Definition\ThemeDefinition $definition */
    $this->themePluginManager->processDefinition($definition, $plugin_id);
    $this->assertInstanceOf(ThemeDefinition::class, $definition);
    $this->assertEquals($definition->toArray(), $expected->toArray());
  }

}
