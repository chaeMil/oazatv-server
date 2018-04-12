<?php
/**
 * Created by PhpStorm.
 * User: macbook
 * Date: 12/04/2018
 * Time: 16:16
 */

namespace App\FrontModule;


class TvPresenter extends BasePresenter {
    public function renderDefault($station = null) {
        $this->template->station = $station;
    }
}