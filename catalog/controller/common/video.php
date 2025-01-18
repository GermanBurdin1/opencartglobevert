<?php
namespace Opencart\Catalog\Controller\Common;

class Video extends \Opencart\System\Engine\Controller {
    public function index(): string {

        $data['video_width'] = '800';
        $data['video_path'] = 'image/video/my_video.mp4';
        $data['text_video_not_supported'] = $this->language->get('text_video_not_supported'); //
        return $this->load->view('common/video', $data);
    }
}
