<?php

namespace LT\Form\Element;

trait Input {

    /**
     * 
     * @param int $length
     * @return static
     */
    public function maxLength($length) {
        return $this->attr('maxlength', $length);
    }

    /**
     * 
     * @return static
     */
    public function autoCompleteOff() {
        return $this->attr('autocomplete', 'off');
    }

    /**
     * 
     * @return static
     */
    public function readonly() {
        return $this->attr('readonly', 'readonly');
    }

    /**
     * 
     * @param string $text
     * @return static
     */
    public function placeholder($text) {
        return $this->attr('placeholder', $text);
    }


    /**
     * @return static
     */
    public function password() {
        return $this->attr('type', 'password');
    }
}
