<?php

class WidgetContent{

    public static function returnRightTriggerHtml(){
        $text = _mt("点击展开右侧边栏");
        return <<<EOF
<div class="resize-pane">
        <div id="trigger_right_content" class="trigger_content"><div class="trigger_drag_content"></div></div>
        <div id="trigger_right_button" data-placement="left" data-toggle="tooltip" data-original-title="{$text}" class="normal-widget resize-pane-trigger box-shadow-wrap-lg"><i data-feather="sidebar"></i></div>
    </div>
EOF;

    }

}
