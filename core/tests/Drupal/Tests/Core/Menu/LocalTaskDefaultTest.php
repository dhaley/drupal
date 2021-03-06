<?php

/**
 * @file
 * Contains \Drupal\Tests\Core\Menu\LocalTaskDefaultTest.
 */

namespace Drupal\Tests\Core\Menu;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\Menu\LocalTaskDefault;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Route;

/**
 * Tests the local task default class.
 *
 * @see \Drupal\Core\Menu\LocalTaskDefaultTest
 */
class LocalTaskDefaultTest extends UnitTestCase {

  /**
   * The tested local task default plugin.
   *
   * @var \Drupal\Core\Menu\LocalTaskDefault
   */
  protected $localTaskBase;

  /**
   * The used plugin configuration.
   *
   * @var array
   */
  protected $config = array();

  /**
   * The used plugin ID.
   *
   * @var string
   */
  protected $pluginId = 'local_task_default';

  /**
   * The used plugin definition.
   *
   * @var array
   */
  protected $pluginDefinition = array(
    'id' => 'local_task_default',
  );

  /**
   * The mocked translator.
   *
   * @var \Drupal\Core\StringTranslation\TranslationInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $stringTranslation;

  /**
   * The mocked route provider.
   *
   * @var \Drupal\Core\Routing\RouteProviderInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $routeProvider;

  public static function getInfo() {
    return array(
      'name' => 'Local tasks default plugin.',
      'description' => 'Tests the local task default class.',
      'group' => 'Menu',
    );
  }

  protected function setUp() {
    parent::setUp();

    $this->stringTranslation = $this->getMock('Drupal\Core\StringTranslation\TranslationInterface');
    $this->routeProvider = $this->getMock('Drupal\Core\Routing\RouteProviderInterface');
  }

  /**
   * Setups the local task default.
   */
  protected function setupLocalTaskDefault() {
    $container = new ContainerBuilder();
    $container->set('string_translation', $this->stringTranslation);
    $container->set('router.route_provider', $this->routeProvider);
    \Drupal::setContainer($container);
    $this->localTaskBase = new LocalTaskDefault($this->config, $this->pluginId, $this->pluginDefinition);
  }

  /**
   * Tests the getRouteParameters for a static route.
   *
   * @see \Drupal\Core\Menu\LocalTaskDefault::getRouteParameters()
   */
  public function testGetRouteParametersForStaticRoute() {
    $this->pluginDefinition = array(
      'route_name' => 'test_route'
    );

    $this->routeProvider->expects($this->once())
      ->method('getRouteByName')
      ->with('test_route')
      ->will($this->returnValue(new Route('/test-route')));

    $this->setupLocalTaskDefault();

    $request = Request::create('/');
    $this->assertEquals(array(), $this->localTaskBase->getRouteParameters($request));
  }

  /**
   * Tests the getRouteParameters for a route with the parameters in the plugin definition.
   */
  public function testGetRouteParametersInPluginDefinitions() {
    $this->pluginDefinition = array(
      'route_name' => 'test_route',
      'route_parameters' => array('parameter' => 'example')
    );

    $this->routeProvider->expects($this->once())
      ->method('getRouteByName')
      ->with('test_route')
      ->will($this->returnValue(new Route('/test-route/{parameter}')));

    $this->setupLocalTaskDefault();

    $request = new Request();

    $this->assertEquals(array('parameter' => 'example'), $this->localTaskBase->getRouteParameters($request));
  }

  /**
   * Tests the getRouteParameters method for a route with dynamic non upcasted parameters.
   *
   * @see \Drupal\Core\Menu\LocalTaskDefault::getRouteParameters()
   */
  public function testGetRouteParametersForDynamicRoute() {
    $this->pluginDefinition = array(
      'route_name' => 'test_route'
    );

    $this->routeProvider->expects($this->once())
      ->method('getRouteByName')
      ->with('test_route')
      ->will($this->returnValue(new Route('/test-route/{parameter}')));

    $this->setupLocalTaskDefault();

    $request = new Request(array(), array(), array('parameter' => 'example'));

    $this->assertEquals(array('parameter' => 'example'), $this->localTaskBase->getRouteParameters($request));
  }

  /**
   * Tests the getRouteParameters method for a route with upcasted parameters.
   *
   * @see \Drupal\Core\Menu\LocalTaskDefault::getRouteParameters()
   */
  public function testGetRouteParametersForDynamicRouteWithUpcastedParameters() {
    $this->pluginDefinition = array(
      'route_name' => 'test_route'
    );

    $this->routeProvider->expects($this->once())
      ->method('getRouteByName')
      ->with('test_route')
      ->will($this->returnValue(new Route('/test-route/{parameter}')));

    $this->setupLocalTaskDefault();

    $request = new Request();
    $raw_variables = new ParameterBag();
    $raw_variables->set('parameter', 'example');
    $request->attributes->set('parameter', (object) array('example2'));
    $request->attributes->set('_raw_variables', $raw_variables);

    $this->assertEquals(array('parameter' => 'example'), $this->localTaskBase->getRouteParameters($request));
  }

  /**
   * Defines a test provider for getWeight()
   *
   * @see self::getWeight()
   *
   * @return array
   *   A list or test plugin definition and expected weight.
   */
  public function providerTestGetWeight() {
    return array(
      // Manually specify a weight, so this is used.
      array(array('weight' => 314), 'test_id', 314),
      // Ensure that a default tab get a lower weight.
      array(
        array(
          'tab_root_id' => 'local_task_default',
          'id' => 'local_task_default'
        ),
        'local_task_default',
        -10
      ),
      // If the root ID is different to the ID of the tab, ignore it.
      array(
        array(
          'tab_root_id' => 'local_task_example',
          'id' => 'local_task_default'
        ),
        'local_task_default',
        0,
      ),
      // Ensure that a default tab of a derivative gets the default value.
      array(
        array(
          'tab_root_id' => 'local_task_derivative_default:example_id',
          'id' => 'local_task_derivative_default'
        ),
        'local_task_derivative_default:example_id',
        -10,
      ),
    );
  }

  /**
   * Tests the getWeight method.
   *
   * @dataProvider providerTestGetWeight
   *
   * @see \Drupal\Core\Menu\LocalTaskDefault::getWeight()
   */
  public function testGetWeight(array $plugin_definition, $plugin_id, $expected_weight) {
    $this->pluginDefinition = $plugin_definition;
    $this->pluginId = $plugin_id;
    $this->setupLocalTaskDefault();

    $this->assertEquals($expected_weight, $this->localTaskBase->getWeight());
  }

  /**
   * Tests getActive/setActive() method.
   *
   * @see \Drupal\Core\Menu\LocalTaskDefault::getActive()
   * @see \Drupal\Core\Menu\LocalTaskDefault::setActive()
   */
  public function testActive() {
    $this->setupLocalTaskDefault();

    $this->assertFalse($this->localTaskBase->getActive());
    $this->localTaskBase->setActive();
    $this->assertTrue($this->localTaskBase->getActive());
  }

  /**
   * Tests the getTitle method.
   *
   * @see \Drupal\Core\Menu\LocalTaskDefault::getTitle()
   */
  public function testGetTitle() {
    $this->pluginDefinition['title'] = 'Example';
    $this->stringTranslation->expects($this->once())
      ->method('translate', $this->pluginDefinition['title'])
      ->will($this->returnValue('Example translated'));

    $this->setupLocalTaskDefault();
    $this->assertEquals('Example translated', $this->localTaskBase->getTitle());
  }

  /**
   * Tests the getOption method.
   *
   * @see \Drupal\Core\Menu\LocalTaskDefault::getOption()
   */
  public function testGetOptions() {
    $this->pluginDefinition['options'] = array(
      'attributes' => array('class' => array('example')),
    );

    $this->setupLocalTaskDefault();

    $request = Request::create('/');
    $this->assertEquals($this->pluginDefinition['options'], $this->localTaskBase->getOptions($request));

    $this->localTaskBase->setActive(TRUE);

    $this->assertEquals(array(
      'attributes' => array(
        'class' => array(
          'example',
          'active'
        )
      )
    ), $this->localTaskBase->getOptions($request));
  }

}
