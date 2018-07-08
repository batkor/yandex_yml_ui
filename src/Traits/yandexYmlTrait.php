<?php

namespace Drupal\yandex_yml_ui\Traits;


use Drupal\Core\Form\FormStateInterface;

trait yandexYmlTrait {

  public function getStoreDetailElems() {

    $elems['store_details'] = [
      '#type' => 'container',
      '#prefix' => '<div class="panel"><h3 class="panel__title">' . $this->t('Store settings') . '</h3>',
      '#suffix' => '</div>',
    ];

    $elems['store_details']['store_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Name'),
      '#default_value' => $this->options['store_name'],
      '#required' => TRUE,
      '#parents' => ['main', 'store_name'],
    ];

    $elems['store_details']['store_fullname'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Fullname'),
      '#default_value' => $this->options['store_fullname'],
      '#required' => TRUE,
      '#parents' => ['main', 'store_fullname'],
    ];

    return $elems;
  }

  public function getCurrencyFormElems() {

    $elems['currency_details'] = [
      '#type' => 'container',
      '#prefix' => '<div class="panel"><h3 class="panel__title">' . $this->t('Currency settings') . '</h3>',
      '#suffix' => '</div>',
    ];

    $elems['currency_details']['store_currency_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Currency'),
      '#default_value' => $this->options['store_currency_id'],
      '#parents' => ['main', 'store_currency_id'],
    ];

    $elems['currency_details']['store_currency_rate'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Currency rate'),
      '#default_value' => $this->options['store_currency_rate'],
      '#parents' => ['main', 'store_currency_rate'],
    ];

    return $elems;
  }

  public function getOfferFormElems() {

    $elems['offer'] = [
      '#type' => 'container',
      '#prefix' => '<div class="panel"><h3 class="panel__title">' . $this->t('Settings for offer') . '</h3>',
      '#suffix' => '</div>',
    ];

    $elems['offer']['offer_id'] = [
      '#type' => 'select',
      '#title' => $this->t('Selected offer'),
      '#default_value' => $this->options['offer_id'] ?: 'custom',
      '#parents' => ['main', 'offer_id'],
      '#options' => $this->getOffers(),
    ];

    return $elems;
  }

  public function getOfferCustomFormElems(){
    $elems['offer']['custom'] = [
      '#type' => 'details',
      '#title' => $this->t('Arbitrary type'),
      '#open' => TRUE,
      '#states' => [
        'visible' => [':input[name="main[offer_id]"]' => ['value' => 'custom']],
      ],
    ];
    $elems['offer']['custom']['sales_notes'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Sales notes'),
      '#default_value' => $this->options["offer"]['custom']['sales_notes'],
      '#parents' => ['main', 'offer', 'custom', 'sales_notes'],
    ];

    $fields = $this->displayHandler->getOption('fields');
    $options = [];
    foreach ($fields as $field) {
      $options[$field['id']] = !empty($field['label']) ?: $field['id'];
    }

    $elems['offer']['custom']['elems'] = [
      '#type' => 'table',
      '#caption' => $this->t('Set elements'),
      '#header' => [
        $this->t('Value/Fields'),
        $this->t('Element in Yandex Market'),
        $this->t('Parametr'),
      ],
      '#prefix' => '<div id="offer-wrapper">',
      '#suffix' => '</div>',
    ];

    foreach ($options as $key => $option) {
      $elems['offer']['custom']['elems'][$key]['value'] = [
        '#markup' => $option,
      ];

      $elems['offer']['custom']['elems'][$key]['elem'] = [
        '#type' => 'select',
        '#options' => $this->getOfferElems(),
        '#parents' => ['main', 'offer', 'custom', 'elems', $key, 'elem'],
        '#default_value' => $this->options["offer"]['custom']['elems'][$key]['elem'] ?: 'none',
      ];
      $elems['offer']['custom']['elems'][$key]['param'] = [
        '#type' => 'textfield',
        '#parents' => ['main', 'offer', 'custom', 'elems', $key, 'param'],
        '#default_value' => $this->options["offer"]['custom']['elems'][$key]['param'] ?: '',
        '#size' => 10,
        '#states' => [
          'visible' => [':input[name="main[offer][custom][elems][' . $key . '][elem]"]' => ['value' => 'parametr']],
        ],
      ];
    }

    return $elems;
  }

  public function getCategoryFormElems() {
    $elems['category_details'] = [
      '#type' => 'container',
      '#prefix' => '<div class="panel"><h3 class="panel__title">' . $this->t('Category settings') . '</h3>',
      '#suffix' => '</div>',
    ];

    $elems['category_details']['category'] = [
      '#type' => 'select',
      '#title' => $this->t('Select category'),
      '#default_value' => $this->options['category'] ?: 'none',
      '#parents' => ['main', 'category'],
      '#options' => $this->getVocabulars(),
    ];

    return $elems;
  }

  public function getOffers(){
    return [
      'custom' => $this->t('Arbitrary type'),
      'medicine' => $this->t('Medicine type'),
    ];
  }

  public function getOfferElems() {
    $elems = [
      'none',
      'parametr',
      'Id',
      'Name',
      'Vendor',
      'VendorCode',
      'Available',
      'Bid',
      'Cbid',
      'Url',
      'Price',
      'OldPrice',
      'CurrencyId',
      'CategoryId',
      'Picture',
      'Store',
      'Pickup',
      'Delivery',
      'Description',
      'ManufacturerWarranty',
      'CountryOfOrigin',
      'Barcode',
      'SalesNotes'
    ];
    return array_combine($elems, $elems);
  }

  public function getVocabulars() {
    $vocabularies = \Drupal\taxonomy\Entity\Vocabulary::loadMultiple();
    $output = ['none' => 'none'];
    /** @var \Drupal\taxonomy\Entity\Vocabulary $vocabulary */
    foreach ($vocabularies as $key => $vocabulary) {
      $output[$key] = $vocabulary->label();
    }

    return $output;
  }
}