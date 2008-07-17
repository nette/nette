<?php

/**
 * My Application
 */



/**
 * Default presenter.
 */
class DefaultPresenter extends BasePresenter
{

    public function renderDefault()
    {
        $this->template->title = 'It works!';
    }

}
