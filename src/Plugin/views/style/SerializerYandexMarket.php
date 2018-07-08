<?php

namespace Drupal\yandex_yml_ui\Plugin\views\style;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\rest\Plugin\views\style\Serializer;
use Drupal\taxonomy\Entity\Vocabulary;
use Drupal\views\Plugin\views\style\StylePluginBase;
use Drupal\yandex_yml_ui\Traits\yandexYmlTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * The style plugin for serialized output formats with pager.
 *
 * @ingroup views_style_plugins
 *
 * @ViewsStyle(
 *   id = "serializer_yandex_market",
 *   title = @Translation("Serializer Yandex Market"),
 *   help = @Translation("Create xml for Yandex Market."),
 *   display_types = {"data"}
 * )
 */
class SerializerYandexMarket extends StylePluginBase implements CacheableDependencyInterface {

  use yandexYmlTrait;

  protected $usesRowPlugin = TRUE;

  /** @var \Drupal\yandex_yml\YandexYmlGenerator $generator */
  protected $generator;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('serializer'),
      $container->getParameter('serializer.formats'),
      $container->getParameter('serializer.format_providers')
    );
  }

  /**
   * Constructs a Plugin object.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, SerializerInterface $serializer, array $serializer_formats, array $serializer_format_providers) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->definition = $plugin_definition + $configuration;
    $this->serializer = $serializer;
    $this->formats = $serializer_formats;
    $this->formatProviders = $serializer_format_providers;
  }

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['uses_fields']['default'] = TRUE;
    $options += [
      'store_name' => ['default' => ''],
      'store_fullname' => ['default' => ''],
      'store_currency_id' => ['default' => ''],
      'store_currency_rate' => ['default' => '1'],
      'sales_notes' => ['default' => ''],
    ];

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return Cache::PERMANENT;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return ['request_format'];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    return [];
  }

  public function getFormats() {
    return ['xml'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    $form['main'] = [
      '#type' => 'container',
      '#prefix' => '<div class="clearfix">',
      '#suffix' => '</div>',
      '#tree' => FALSE,
    ];

    $form['main']['left'] = [
      '#type' => 'container',
      '#prefix' => '<div class="layout-column layout-column--half">',
      '#suffix' => '</div>',
    ];

    $form['main']['left'] += $this->getStoreDetailElems();
    $form['main']['left'] += $this->getOfferFormElems();
    $form['main']['left'] += $this->getCategoryFormElems();

    $form['main']['right'] = [
      '#type' => 'container',
      '#prefix' => '<div class="layout-column layout-column--half">',
      '#suffix' => '</div>',
    ];

    $form['main']['right'] += $this->getCurrencyFormElems();
    $form['main']['right'] += $this->getOfferCustomFormElems();


  }

  public function submitOptionsForm(&$form, FormStateInterface $form_state) {
    // Transform the formats back into an array.
    $form_state->setValue('style_options', $form_state->getValues()['main']);
    parent::submitOptionsForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    $rows = [];

    foreach ($this->view->result as $row_index => $row) {
      $this->view->row_index = $row_index;
      $rows[] = $this->view->rowPlugin->render($row);
    }

    $this->generator = \Drupal::service('yandex_yml.generator');
    $this->setShopInfo();
    $this->setCurrencyInfo();
    $this->setCategoryInfo();
    $this->setOffers($rows);
    $output = $this->generator->getResponceData();

    return $output;
  }

  private function setShopInfo() {
    /** @var \Drupal\yandex_yml\YandexYml\Shop\YandexYmlShop $shop_info */
    $shop_info = \Drupal::service('yandex_yml.shop')
      ->setName($this->options['store_name'])
      ->setCompany($this->options['store_fullname']);
    $this->generator->setShopInfo($shop_info);
  }

  private function setCategoryInfo() {
    if ($this->options["category"] === 'none') {
      return;
    }
    $vocabulary = Vocabulary::load($this->options["category"]);
    /** @var \Drupal\yandex_yml\YandexYml\Category\YandexYmlCategory $category */
    $category = \Drupal::service('yandex_yml.category')
      ->setId('0')
      ->setName($vocabulary->label());
    $this->generator->addCategory($category);

    $categories = \Drupal::entityTypeManager()
      ->getStorage('taxonomy_term')
      ->loadTree($this->options["category"]);

    /** @var Object $term */
    foreach ($categories as $term) {
      $category = \Drupal::service('yandex_yml.category')
        ->setId($term->tid)
        ->setName($term->name);

      if (!empty($term->parents)) {
        $category->setParentId(reset($term->parents));
      }
      $this->generator->addCategory($category);
    }
  }

  private function setCurrencyInfo() {
    /** @var \Drupal\yandex_yml\YandexYml\Currency\YandexYmlCurrency $currency */
    $currency = \Drupal::service('yandex_yml.currency')
      ->setId($this->options['store_currency_id'])
      ->setRate($this->options['store_currency_rate']);
    $this->generator->addCurrency($currency);
  }

  private function setOffers($rows) {
    $fields = $this->options['offer'][$this->options["offer_id"]]['elems'];
    foreach ($rows as $row) {
      /** @var \Drupal\yandex_yml\YandexYml\Offer\YandexYmlOfferSimple $offer_simple */
      $offer_simple = \Drupal::service('yandex_yml.offer.simple');
      foreach ($fields as $field => $datum) {
        if (isset($row[$field]) && $datum['elem'] !== 'none') {
          $value = $row[$field]->__toString();
          if ($datum['elem'] === 'parametr' && !empty($datum['param'])) {
            $offer_simple->setParam($datum['param'], $value);
          }
          $func = "set{$datum['elem']}";
          if (method_exists($offer_simple, $func)) {
            $offer_simple->$func($value);
          }
        }
      }

      if (array_search('SalesNotes', $fields) === FALSE) {
        $offer_simple->setSalesNotes($this->options['offer'][$this->options["offer_id"]]['sales_notes']);
      }

      $this->generator->addOffer($offer_simple);
    }

  }
}