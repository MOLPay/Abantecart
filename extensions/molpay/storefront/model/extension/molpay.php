<?php

if (!defined('DIR_CORE')) {
    header('Location: static_pages/');
}

class ModelExtensionMOLPay extends Model {

    public function getMethod($address) {
        $this->load->language('molpay/molpay');

        if ($this->config->get('molpay_status')) {
            $query = $this->db->query("SELECT *
										FROM " . $this->db->table("zones_to_locations") . "
										WHERE location_id = '" . (int) $this->config->get('molpay_location_id') . "'
												AND country_id = '" . (int) $address['country_id'] . "'
												AND (zone_id = '" . (int) $address['zone_id'] . "' OR zone_id = '0')");

            if (!$this->config->get('molpay_location_id')) {
                $status = TRUE;
            } elseif ($query->num_rows) {
                $status = TRUE;
            } else {
                $status = FALSE;
            }
        } else {
            $status = FALSE;
        }

        $method_data = array();

        if ($status) {
            $method_data = array(
                'id' => 'molpay',
                'title' => $this->language->get('text_title'),
                'sort_order' => $this->config->get('molpay_sort_order')
            );
        }

        return $method_data;
    }

    /**
     * @param string $order_status_name
     * @return int
     */
    public function getOrderStatusIdByName($order_status_name) {
        $language_id = $this->language->getLanguageDetails('en');
        $language_id = $language_id['language_id'];

        $sql = "SELECT *
				FROM " . $this->db->table('order_statuses') . "
				WHERE language_id=" . (int) $language_id . " AND LOWER(name) like '%" . strtolower($order_status_name) . "%'";

        $result = $this->db->query($sql);
        return $result->row['order_status_id'];
    }

}
