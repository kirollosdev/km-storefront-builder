# KM Storefront Builder

A WordPress plugin that adds the storefront features a WooCommerce shop on the XStore theme is missing:
variation swatches, smarter product card buttons, a horizontal filter bar, a category slider, sale and
stock badges, a mobile bottom bar, full Arabic storefront texts, correct shop URLs under free Polylang,
and an icon library for Elementor.

Built for live bilingual (English / Arabic) fashion stores, then made brand-neutral so any shop can use
it. Every feature is a switch, so a store can take one piece and leave the rest. Formerly
*KM Storefront Tools*.

## What it does

| Feature | What you get |
| --- | --- |
| **Variation swatches** | Colour circles and label boxes instead of dropdowns, on the product page and in the quick view. Colours are read from the term name, with a colour picker for the ones that cannot be guessed. |
| **Product card buttons** | "Select options" becomes *Quick view* for products with colours, or *Add to cart* with an in-place size picker for products with only sizes. |
| **Card colour swatches** | Colour dots on product cards; picking one switches the card photo to that colour. |
| **Horizontal filter bar** | Category, price, attribute and sort filters in a row above the shop, as an Elementor widget or the `[kmst_filter_bar]` shortcode. |
| **Category slider** | An Elementor slider of product categories, with arrows that move a card, a page, or to the end. |
| **Badges** | Sale percentage, "new" and low-stock badges on cards and product pages. |
| **Mobile bottom bar** | Labels and links for XStore's mobile panel, per language. |
| **KM Icons** | An icon library inside Elementor's icon picker, plus `[km_icon name="bag"]`. |
| **Color Swatches widget** | An Elementor widget that shows the product's swatches anywhere on a product template, with the chosen color's name above them. |
| **Rounded storefront** | One setting rounds the checkout, cart, wishlist, search box, quantity control, quick view and product buttons. |
| **Arabic texts** | Fills in the Arabic for XStore strings that ship with no translation, on Arabic pages only, without touching theme files. |
| **Polylang store URLs** | With free Polylang: shop, category, product, cart, checkout, account and wishlist URLs stay in the language being browsed, and the language switcher goes to the same page rather than the home page. |

Everything is under **WooCommerce → Settings → Products → Store tools**.

## Requirements

- WordPress 5.9+, PHP 7.4+
- WooCommerce 6.0+
- XStore theme for the theme-specific parts (swatches, cards, filter bar and slider work on their own)
- Free Polylang, only if you want the multi-language URL handling

## Install

Download this repository as a zip (Code → Download ZIP), rename the folder inside to `km-storefront-builder`, zip it again and
upload it under *Plugins → Add New → Upload*, or copy the folder to `wp-content/plugins/`.

## Versions

Version numbers carry on from the store-specific plugin this grew out of, so 2.11.6 here is the same
code as 2.11.6 there. The changelog in `readme.txt` keeps that whole history.

Until 2.12.0 this plugin was called **KM Storefront Tools** (folder `km-storefront-tools`). WordPress sees
the renamed folder as a new plugin, so on a site running the old one: upload this one and activate it. That
switches KM Storefront Tools off automatically; then delete it. Settings, shortcodes and Elementor widgets
keep their names, so nothing on the pages changes.

## Notes for other stores

- **Arabic font**: some heading fonts have no Arabic letters. Fill in a font under Store tools and it is
  used for the few places that need it (quick view title, mini cart names, empty cart heading, live
  search results). Left empty, the theme's own font stays everywhere.
- **Arabic texts**: the built-in dictionary covers XStore's storefront strings. Add or change any line
  under Store tools → Arabic texts, one `English = Arabic` per line.
- **Coming from the store-specific version**: settings whose names start with `mst_` are copied to the
  new names once, on activation.

## Author

Kirollos Magdy — <https://kirollosmagdy.com>

## Licence

Copyright (c) 2026 Kirollos Magdy. All rights reserved. No part of this plugin may be copied, changed or
redistributed without written permission. See [LICENSE](LICENSE).

