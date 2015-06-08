<?php

if (!defined('DIR_CORE')) {
    header('Location: static_pages/');
}

$controllers = array(
    'storefront' => array(
        'responses/extension/molpay'
        ),
    'admin' => array(),
);

$models = array(
    'storefront' => array('extension/molpay'),
    'admin' => array(),
);

$languages = array(
    'storefront' => array(
        'molpay/molpay'),
    'admin' => array(
        'molpay/molpay'));

$templates = array(
    'storefront' => array('responses/molpay.tpl'),
    'admin' => array());
