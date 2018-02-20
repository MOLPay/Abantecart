<?php

if (!defined('DIR_CORE')) {
    header('Location: static_pages/');
}

$controllers = array(
    'storefront' => array(
        'responses/extension/molpay_seamless'
        ),
    'admin' => array(),
);

$models = array(
    'storefront' => array('extension/molpay_seamless'),
    'admin' => array(),
);

$languages = array(
    'storefront' => array(
        'molpay_seamless/molpay_seamless'),
    'admin' => array(
        'molpay_seamless/molpay_seamless'));

$templates = array(
    'storefront' => array('responses/molpay_seamless.tpl'),
    'admin' => array());
