<?php
/**
 * Controller for getting top-level categories (parent_id = 0)
 * Used for mobile menu to display only first-level categories
 */
class ControllerCommonTopcategories extends Controller {
	public function index() {
		$this->load->model('catalog/category');

		$data['categories'] = array();

		// Get only top-level categories (parent_id = 0)
		$categories = $this->model_catalog_category->getCategories(0);

		foreach ($categories as $category) {
			$data['categories'][] = array(
				'category_id' => $category['category_id'],
				'name'        => $category['name'],
				'href'        => $this->url->link('product/category', 'path=' . $category['category_id'])
			);
		}

		$this->response->setOutput($this->load->view('common/topcategories', $data));
	}
}
