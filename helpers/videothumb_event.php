<?php defined("SYSPATH") or die("No direct script access.");/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2011 Bharat Mediratta
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or (at
 * your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street - Fifth Floor, Boston, MA  02110-1301, USA.
 */
class videothumb_event {

  static function context_menu($menu, $context_menu, $item, $thumbnail_css_selector) {
      if ($item->is_movie() && access::can("edit", $item)) {
        $menu->get("options_menu")
          ->append(Menu::factory("dialog")
                   ->id("videothumb")
                   ->label(t("Choose videothumb"))
                   ->css_class("ui-icon-video")
                   ->url(url::site("videothumb/dialog/{$item->id}")));       
      }
  }


  static function site_menu($menu, $theme) {
    $item = $theme->item();
    if ($item && $item->is_movie() && access::can("edit", $item)) {
        $menu->get("options_menu")
          ->append(Menu::factory("dialog")
                   ->id("videothumb")
                   ->label(t("Choose videothumb"))
                   ->css_class("ui-icon-video")
                   ->url(url::site("videothumb/dialog/{$item->id}")));       
    }
  }

}
