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

  public function getProductFormElems(&$form, FormStateInterface $form_state) {

    $elems['products_export'] = [
      '#type' => 'container',
      '#prefix' => '<div class="panel"><h3 class="panel__title">' . $this->t('Settings for offer') . '</h3>',
      '#suffix' => '</div>',
    ];

    $elems['products_export']['sales_notes'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Sales notes'),
      '#default_value' => $this->options['sales_notes'],
      '#parents' => ['main', 'sales_notes'],
    ];

    $fields = $this->displayHandler->getOption('fields');
    $options = [];
    foreach ($fields as $field) {
      $options[$field['id']] = !empty($field['label']) ?: $field['id'];
    }

    $elems['products_export']['offer'] = [
      '#type' => 'table',
      '#header' => [
        $this->t('Value/Fields'),
        $this->t('Element in Yandex Market')
      ],
      '#prefix' => '<div id="offer-wrapper">',
      '#suffix' => '</div>',
    ];

    foreach ($options as $key => $option) {
      $elems['products_export']['offer'][$key]['value'] = [
        '#markup' => $option,
      ];

      $elems['products_export']['offer'][$key]['elem'] = [
        '#type' => 'select',
        '#options' => $this->getOfferElems(),
        '#parents' => ['main', 'offer', $key],
        '#default_value' => $this->options["offer"][$key],
      ];
    }

    return $elems;
  }


  public function getOfferElems() {
    $elems = [
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

}