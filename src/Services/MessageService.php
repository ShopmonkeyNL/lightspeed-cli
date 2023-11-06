<?php

namespace Shopmonkeynl\ShopmonkeyCli\Services;
use function Termwind\render;

class MessageService
{

    public function create($title, $message, $color) {

        render(<<<HTML
            <div class="mt-1">
                <span class="px-1 bg-$color-400">$title</span>
                <span class="ml-1">
                $message
                </span>
            </div>
        HTML);
        

    }


}