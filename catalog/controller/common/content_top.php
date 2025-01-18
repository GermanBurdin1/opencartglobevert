<?php

namespace Opencart\Catalog\Controller\Common;

/**
 * Class Content Top
 *
 * @package Opencart\Catalog\Controller\Common
 */
class ContentTop extends \Opencart\System\Engine\Controller
{
	/**
	 * @return string
	 */
	public function index(): string
	{

		$this->load->model('design/layout');
		$this->load->model('design/banner'); // Загрузка модели баннера


		if (isset($this->request->get['route'])) {
			$route = (string)$this->request->get['route'];
		} else {
			$route = 'common/home';
		}

		$data['video_width'] = null; // Дефолтное значение для других страниц
		$data['video_path'] = null;
		$data['text_video_not_supported'] = null;

		// Проверяем, является ли текущая страница главной (common/home)
		if ($route == 'common/home') {
			$data['video_width'] = '800'; // Ширина видео
			$data['video_path'] = 'image/video/my_video.mp4'; // Путь к видео
			$data['text_video_not_supported'] = 'Ваш браузер не поддерживает видео.'; // Текст для неподдерживаемых браузеров
		}

		// Получаем первый баннер
		$banner_id = 7; // ID баннера. Проверьте в админке OpenCart
		$banners = $this->model_design_banner->getBanner($banner_id);
		$data['first_banner_link'] = isset($banners[0]['link']) ? $banners[0]['link'] : '#';


		$layout_id = 0;

		if ($route == 'common/home') {
			$this->load->model('catalog/category');
			$this->load->language('common/home');

			$data['breadcrumbs'] = [];

			// Добавляем "Главная" в breadcrumbs
			// $data['breadcrumbs'][] = [
			// 	'text' => $this->language->get('text_home'),
			// 	'href' => $this->url->link('common/home')
			// ];

			// Получаем категории верхнего уровня
			$categories = $this->model_catalog_category->getCategories(0);

			foreach ($categories as $category) {
				$data['breadcrumbs'][] = [
					'text' => $category['name'],
					'href' => $this->url->link('product/category', 'path=' . $category['category_id'])
				];
			}

			$data['video_width'] = '800';
			$data['video_path'] = 'image/video/my_video.mp4';
			$data['text_video_not_supported'] = 'Ваш браузер не поддерживает видео.';
		}


		if ($route == 'product/category' && isset($this->request->get['path'])) {
			$this->load->model('catalog/category');

			$path = explode('_', (string)$this->request->get['path']);

			$layout_id = $this->model_catalog_category->getLayoutId((int)end($path));
		}

		if ($route == 'product/product' && isset($this->request->get['product_id'])) {
			$this->load->model('catalog/product');

			$layout_id = $this->model_catalog_product->getLayoutId((int)$this->request->get['product_id']);
		}



		if ($route == 'product/manufacturer.info' && isset($this->request->get['manufacturer_id'])) {
			$this->load->model('catalog/manufacturer');

			$layout_id = $this->model_catalog_manufacturer->getLayoutId((int)$this->request->get['manufacturer_id']);
		}

		if ($route == 'information/information' && isset($this->request->get['information_id'])) {
			$this->load->model('catalog/information');

			$layout_id = $this->model_catalog_information->getLayoutId((int)$this->request->get['information_id']);
		}

		if ($route == 'cms/blog.info' && isset($this->request->get['blog_id'])) {
			$this->load->model('cms/blog');

			$layout_id = $this->model_cms_blog->getLayoutId((int)$this->request->get['blog_id']);
		}

		if (!$layout_id) {
			$layout_id = $this->model_design_layout->getLayout($route);
		}

		if (!$layout_id) {
			$layout_id = $this->config->get('config_layout_id');
		}

		$this->load->model('setting/module');

		$data['modules'] = [];

		$modules = $this->model_design_layout->getModules($layout_id, 'content_top');

		foreach ($modules as $module) {
			$part = explode('.', $module['code']);

			if (isset($part[1]) && $this->config->get('module_' . $part[1] . '_status')) {
				$module_data = $this->load->controller('extension/' .  $part[0] . '/module/' . $part[1]);

				if ($module_data) {
					$data['modules'][] = $module_data;
				}
			}

			if (isset($part[2])) {
				$setting_info = $this->model_setting_module->getModule($part[2]);

				if ($setting_info && $setting_info['status']) {
					$output = $this->load->controller('extension/' .  $part[0] . '/module/' . $part[1], $setting_info);

					if ($output) {
						$data['modules'][] = $output;
					}
				}
			}
		}
		$data['route'] = $route;
		$data['home_url'] = $this->url->link('common/home', 'language=' . $this->config->get('config_language'));
		$data['header_cart'] = $this->load->controller('common/cart');
		return $this->load->view('common/content_top', $data);
	}
}
