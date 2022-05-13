<?php

class Title_Plugin extends Typecho_Widget_Helper_Form_Element
{

    public function label(string $value): Typecho\Widget\Helper\Form\Element
    {
        /** 创建标题元素 */
        if (empty($this->label)) {
            $this->label = new Typecho_Widget_Helper_Layout('label', array('class' => 'typecho-label', 'style' => 'font-size: 2em;border-bottom: 1px #ddd solid;padding-top:2em;'));
            $this->container($this->label);
        }

        $this->label->html($value);
        return $this;
    }

    public function input(?string $name = null, ?array $options = null): ?Typecho\Widget\Helper\Layout
    {
        $input = new Typecho_Widget_Helper_Layout('p', array());
        $this->container($input);
        $this->inputs[] = $input;
        return $input;
    }

    protected function inputValue($value)
    {
    }


}
