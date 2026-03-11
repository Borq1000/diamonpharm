<?php
/**
 * Diamond Categories Module - Frontend Controller
 * Displays categories in a grid layout
 * Version: 1.0.0
 */

class ControllerExtensionModuleDiamondCategories extends Controller {
    
    public function index($setting) {
        $this->load->language('extension/module/diamond_categories');

        $this->load->model('catalog/category');
        $this->load->model('catalog/product');
        $this->load->model('tool/image');

        $data['heading_title'] = isset($setting['title']) && !empty($setting['title']) ? $setting['title'] : $this->language->get('heading_title');

        $data['categories'] = array();

        if (!empty($setting['categories'])) {
            $categories = array_slice($setting['categories'], 0, (int)$setting['limit']);

            foreach ($categories as $category_id) {
                $category_info = $this->model_catalog_category->getCategory($category_id);

                if ($category_info) {
                    if ($category_info['image']) {
                        $image = $this->model_tool_image->resize($category_info['image'], 600, 420);
                    } else {
                        $image = $this->model_tool_image->resize('placeholder.png', 600, 420);
                    }

                    // Получаем количество товаров в категории
                    $filter_data = array(
                        'filter_category_id'  => $category_id,
                        'filter_sub_category' => true
                    );

                    $product_total = $this->model_catalog_product->getTotalProducts($filter_data);

                    $data['categories'][] = array(
                        'category_id' => $category_info['category_id'],
                        'name'        => $category_info['name'],
                        'description' => utf8_substr(trim(strip_tags(html_entity_decode($category_info['description'], ENT_QUOTES, 'UTF-8'))), 0, 100) . '..',
                        'image'       => $image,
                        'href'        => $this->url->link('product/category', 'path=' . $category_info['category_id']),
                        'product_total' => $product_total
                    );
                }
            }
        }

        $data['columns'] = isset($setting['columns']) ? (int)$setting['columns'] : 3;

        if ($data['categories']) {
            return $this->load->view('extension/module/diamond_categories', $data);
        }
    }
}